<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Verification;
use App\Models\Setting;
use App\Services\TelegramService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Server6VerificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $verifications = Verification::where('user_id', $user->id)
            ->where('server', 'server6')
            ->latest()
            ->get();

        $defaultCountry = 187; // United States
        $countries = $this->fetchCountryList();
        $services = $this->fetchServices($defaultCountry);

        return view('user.server6', compact('services', 'verifications', 'defaultCountry', 'countries'));
    }

    public function fetchCountryList()
    {
        return [
            0 => 'Russia', 1 => 'Ukraine', 2 => 'Kazakstan', 3 => 'China', 4 => 'Philippines',
            5 => 'Myanmar', 6 => 'Indonesia', 7 => 'Malaysia', 8 => 'Kenya', 11 => 'Kyrgyzstan',
            13 => 'Israel', 14 => 'Hong Kong', 15 => 'Poland', 16 => 'United Kingdom', 19 => 'Nigeria',
            21 => 'Egypt', 23 => 'Ireland', 24 => 'Cambodia', 25 => 'Lao Peoples', 26 => 'Haiti',
            29 => 'Serbia', 31 => 'South Africa', 32 => 'Romania', 33 => 'Colombia', 34 => 'Estonia',
            37 => 'Morocco', 38 => 'Ghana', 39 => 'Argentina', 40 => 'Uzbekistan', 41 => 'Cameroon',
            43 => 'Germany', 44 => 'Lithuania', 45 => 'Croatia', 46 => 'Sweden', 47 => 'Iraq',
            48 => 'Netherlands', 49 => 'Latvia', 50 => 'Austria', 51 => 'Belarus', 52 => 'Thailand',
            54 => 'Mexico', 56 => 'Spain', 59 => 'Slovenia', 60 => 'Bangladesh', 61 => 'Senegal',
            63 => 'Czech Republic', 64 => 'Sri Lanka', 65 => 'Peru', 66 => 'Pakistan', 67 => 'New Zealand',
            70 => 'Venezuela', 73 => 'Brazil', 76 => 'Angola', 77 => 'Cyprus', 78 => 'France',
            82 => 'Belgium', 83 => 'Bulgaria', 84 => 'Hungary', 85 => 'Moldova', 86 => 'Italy',
            87 => 'Paraguay', 92 => 'Bolivia', 94 => 'Guatemala', 95 => 'UAE', 108 => 'Bosnia And Herzegovina',
            109 => 'Dominican Republic', 117 => 'Portugal', 128 => 'Georgia', 129 => 'Greece',
            141 => 'Slovakia', 143 => 'Tajikistan', 148 => 'Armenia', 151 => 'Chile', 163 => 'Finland',
            171 => 'Montenegro', 172 => 'Denmark', 173 => 'Switzerland', 174 => 'Norway',
            175 => 'Australia', 182 => 'Japan', 183 => 'Macedonia', 187 => 'United States',
            196 => 'Singapore', 199 => 'Malta', 201 => 'Gibraltar',
        ];
    }

    public function fetchServices($country)
    {
        $apiKey = Setting::get('gogetsms_api_key') ?? config('services.gogetsms.api_key');
        $rate = Setting::get('gogetsms_usd_to_naira_rate') ?? 1500;
        $extra = Setting::get('gogetsms_extra_cost') ?? 0;

        $url = "https://www.gogetsms.com/handler_api.php?api_key={$apiKey}&action=getPrices&country={$country}";
        $response = Http::get($url);

        if (!$response->ok()) {
            \Log::error("GoGetSMS fetch failed", ['country' => $country, 'body' => $response->body()]);
            return [];
        }

        $json = $response->json();

        if (!is_array($json) || !isset($json[$country])) {
            \Log::error("Invalid pricing format", ['country' => $country, 'response' => $json]);
            return [];
        }

        $services = [];

        foreach ($json[$country] as $serviceCode => $data) {
            $usd = (float) ($data['cost'] ?? 0);
            $count = (int) ($data['count'] ?? 0);

            $ngn = ($usd * $rate) + $extra;

            $services[$serviceCode] = [
                'name'       => $this->getServiceNames()[$serviceCode] ?? strtoupper($serviceCode),
                'price_usd'  => $usd,
                'price_ngn'  => round($ngn),
                'count'      => $count,
            ];
        }

        return $services;
    }

    private function getServiceNames()
    {
        $path = storage_path('app/service_names.json');
        return json_decode(file_get_contents($path), true);
    }

    public function getServicesJson($country)
    {
        $services = $this->fetchServices($country);
        return response()->json($services);
    }

    public function purchase(Request $request)
    {
        $request->validate([
            'country' => 'required|integer',
            'service' => 'required|string',
        ]);

        $user = auth()->user();
        $apiKey = Setting::get('gogetsms_api_key') ?? config('services.gogetsms.api_key');
        $serviceList = $this->fetchServices($request->country);

        if (!isset($serviceList[$request->service])) {
            return back()->with('error', 'Invalid service or country.');
        }

        $serviceData = $serviceList[$request->service];
        $usdPrice = $serviceData['price_usd'];
        $nairaPrice = $serviceData['price_ngn'];
        $extra = Setting::get('gogetsms_extra_cost') ?? 0;

        if ($user->wallet < $nairaPrice) {
            return back()->with('error', 'Insufficient wallet balance.');
        }

        $response = Http::get("https://www.gogetsms.com/handler_api.php", [
            'api_key' => $apiKey,
            'action' => 'getNumber',
            'service' => $request->service,
            'country' => $request->country,
        ]);

        $body = $response->body();

        if (!str_starts_with($body, 'ACCESS_NUMBER')) {
            \Log::warning("GoGetSMS Error", ['response' => $body, 'user_id' => $user->id]);
            return back()->with('error', $body);
        }

        [$status, $activation_id, $number] = explode(':', $body);

        DB::beginTransaction();

        try {
            $user->wallet -= $nairaPrice;
            $user->save();

            Verification::create([
                'user_id'        => $user->id,
                'name'           => $this->getServiceNames()[$request->service] ?? strtoupper($request->service),
                'server'         => 'server6',
                'number'         => $number,
                'activation_id'  => $activation_id,
                'status'         => 'Waiting',
                'code'           => null,
                'price'          => $usdPrice,
                'markup'         => $extra,
                'naira_amount'   => $nairaPrice,
                'service'        => $request->service,
                'country_id'     => $request->country,
            ]);

            try {
                $message = "📲 <b>Server 6 Purchase (GoGetSMS)</b>\n"
                         . "👤 User: <b>{$user->email}</b>\n"
                         . "🌍 Country ID: <b>{$request->country}</b>\n"
                         . "🧾 Service: <b>{$serviceData['name']}</b>\n"
                         . "📞 Number: <code>{$number}</code>\n"
                         . "💵 Price: ₦" . number_format($nairaPrice, 2) . "\n"
                         . "🔢 Activation ID: <code>{$activation_id}</code>\n"
                         . "💼 Wallet Balance: ₦" . number_format($user->fresh()->wallet, 2) . "\n"
                         . "🕐 " . now()->format('Y-m-d H:i:s');

                (new TelegramService())->sendMessage($message);
            } catch (\Throwable $e) {
                \Log::error('Telegram error (gogetsms purchase): ' . $e->getMessage());
            }

            DB::commit();
            return back()->with('success', "Purchased Successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Verification purchase failed', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return back()->with('error', 'Something went wrong while processing your request.');
        }
    }

   public function checkStatus($id)
{
    $v = Verification::findOrFail($id);
    $apiKey = Setting::get('gogetsms_api_key') ?? config('services.gogetsms.api_key');

    $response = Http::get("https://www.gogetsms.com/handler_api.php", [
        'api_key' => $apiKey,
        'action' => 'getStatus',
        'id' => $v->activation_id
    ]);

    $body = $response->body();

    // Code has been received
    if (str_starts_with($body, 'STATUS_OK')) {
        $code = explode(':', $body)[1];
        $v->status = 'Completed';
        $v->code = $code;
        $v->save();

        try {
            $message = "✅ <b>GoGetSMS Code Received</b>\n"
                     . "👤 User: <b>{$v->user->email}</b>\n"
                     . "🧾 Service: <b>{$v->name}</b>\n"
                     . "📞 Number: <code>{$v->number}</code>\n"
                     . "🔢 Code: <code>{$code}</code>\n"
                     . "💼 Wallet After: ₦" . number_format($v->user->wallet, 2) . "\n"
                     . "🕐 " . now()->format('Y-m-d H:i:s');

            (new TelegramService())->sendMessage($message);
        } catch (\Throwable $e) {
            \Log::error('Telegram error (GoGetSMS CODE): ' . $e->getMessage());
        }

        return response()->json(['status' => 'Completed', 'code' => $code]);
    }

    // Still waiting for code
    if ($body === 'STATUS_WAIT_CODE') {
        return response()->json(['status' => 'Waiting']);
    }

    // API says it's cancelled, but we do NOT mark as cancelled on our end
    if ($body === 'STATUS_CANCEL') {
        return response()->json(['status' => 'Waiting']); // Treat as still waiting
    }

    // Return raw response status
    return response()->json(['status' => $body]);
}

    public function cancel($id)
{
    $v = Verification::where('id', $id)
        ->where('user_id', Auth::id())
        ->where('server', 'server6')
        ->first();

    if (!$v || in_array($v->status, ['Completed', 'Cancelled'])) {
        return back()->with('error', 'Cancellation not possible.');
    }

    // Code has already dropped
    if (!is_null($v->code)) {
        return back()->with('error', 'Code already received. Order marked as completed.');
    }

    $apiKey = Setting::get('gogetsms_api_key') ?? config('services.gogetsms.api_key');

    $response = Http::get("https://www.gogetsms.com/handler_api.php", [
        'api_key' => $apiKey,
        'action' => 'setStatus',
        'status' => 8,
        'id' => $v->activation_id
    ]);

    $body = $response->body();

    // Allow user-side cancellation even if API says BAD_STATUS or already cancelled
    if (
        str_contains($body, 'ACCESS_CANCEL') ||
        str_contains($body, 'NO_ACTIVATION') ||
        str_contains($body, 'BAD_STATUS')
    ) {
        $amount = $v->naira_amount;
        $user = $v->user;

        $user->wallet += $amount;
        $user->save();

        $v->delete();

        try {
            $message = "❌ <b>GoGetSMS Order Force Cancelled</b>\n"
                     . "👤 User: <b>{$user->email}</b>\n"
                     . "🧾 Service: <b>{$v->name}</b>\n"
                     . "📞 Number: <code>{$v->number}</code>\n"
                     . "💵 Refunded: ₦" . number_format($amount, 2) . "\n"
                     . "💼 Wallet After: ₦" . number_format($user->wallet, 2) . "\n"
                     . "🕐 " . now()->format('Y-m-d H:i:s');

            (new TelegramService())->sendMessage($message);
        } catch (\Throwable $e) {
            \Log::error('Telegram error (GoGetSMS cancel): ' . $e->getMessage());
        }

        return back()->with('success', 'Order cancelled, refunded, and removed.');
    }

    return back()->with('error', 'Cancellation failed: ' . $body);
}
}