<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Verification;
use App\Models\Setting;

class Server4ApiController extends Controller
{
    protected $token;

    public function __construct()
    {
        $this->token = Setting::get('smsman_api_token') ?? config('services.smsman.token');
    }

    public function dashboard()
    {
        $countries = [];
        $services = [];
        $verifications = Verification::where('user_id', auth()->id())
            ->where('server', 4)
            ->latest()->get();

        try {
            $countryRes = Http::get("https://api.sms-man.com/control/countries", ['token' => $this->token]);
            if ($countryRes->successful()) {
                foreach ($countryRes->json() as $c) {
                    if (isset($c['id'], $c['title'])) {
                        $countries[] = ['id' => $c['id'], 'name' => $c['title']];
                    }
                }
            }

            $serviceRes = Http::get("https://api.sms-man.com/control/applications", ['token' => $this->token]);
            if ($serviceRes->successful()) {
                foreach ($serviceRes->json() as $s) {
                    if (isset($s['id'])) {
                        $services[] = ['id' => $s['id'], 'name' => $s['title'] ?? $s['name']];
                    }
                }
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'countries' => $countries,
            'services' => $services,
            'verifications' => $verifications,
        ]);
    }

    public function getPrice(Request $request)
    {
        $request->validate(['country' => 'required', 'service' => 'required']);

        $response = Http::get("https://api.sms-man.com/control/get-prices", [
            'token' => $this->token,
            'country_id' => $request->country
        ]);

        $json = $response->json();
        $usd = null;

        foreach ($json as $item) {
            if ((int)$item['application_id'] === (int)$request->service &&
                (int)$item['country_id'] === (int)$request->country) {
                $base = floatval($item['cost']) / 100;
                $markup = floatval(Setting::get('smsman_markup_percent', 50));
                $usd = $base + ($base * $markup / 100);
                break;
            }
        }

        if (is_null($usd)) {
            return response()->json(['success' => false, 'message' => 'Price not available']);
        }

        $rate = Setting::get('smsman_usd_to_naira_rate', 1600);
        $gain = Setting::get('smsman_service_gain', 0);
        $naira = round(($usd * $rate) + $gain, 2);

        return response()->json(['success' => true, 'price_naira' => $naira]);
    }

    public function purchase(Request $request)
    {
        $request->validate([
            'country' => 'required|numeric',
            'service' => 'required|numeric',
        ]);

        $user = Auth::user();
        $countryId = $request->country;
        $applicationId = $request->service;

        $response = Http::get("https://api.sms-man.com/control/get-number", [
            'token' => $this->token,
            'country_id' => $countryId,
            'application_id' => $applicationId
        ]);

        $json = $response->json();

        if (!isset($json['number']) || !isset($json['request_id'])) {
            return response()->json([
                'success' => false,
                'message' => $json['message'] ?? '❌ Number not available, try again later.'
            ], 422);
        }

        $usd = null;

        if (isset($json['price'])) {
            $usd = floatval($json['price']);
        } else {
            $priceResponse = Http::get("https://api.sms-man.com/control/get-prices", [
                'token' => $this->token,
                'country_id' => $countryId
            ]);

            $priceJson = $priceResponse->json();

            foreach ($priceJson as $item) {
                if ((int)$item['application_id'] === (int)$applicationId &&
                    (int)$item['country_id'] === (int)$countryId &&
                    isset($item['cost'])) {
                    $base = floatval($item['cost']) / 100;
                    $markup = floatval(Setting::get('smsman_markup_percent', 50));
                    $usd = $base + ($base * $markup / 100);
                    break;
                }
            }
        }

        if (is_null($usd)) {
            return response()->json([
                'success' => false,
                'message' => '❌ Price not available for this service and country.'
            ], 422);
        }

        $rate = floatval(Setting::get('smsman_usd_to_naira_rate'));
        $gain = floatval(Setting::get('smsman_service_gain'));

        if ($rate <= 0) {
            return response()->json([
                'success' => false,
                'message' => '❌ USD to Naira rate not configured in admin settings.'
            ], 500);
        }

        $nairaAmount = round(($usd * $rate) + $gain, 2);

        if ($user->wallet < $nairaAmount) {
            return response()->json([
                'success' => false,
                'message' => '❌ Insufficient wallet balance. Required: ₦' . number_format($nairaAmount, 2)
            ], 422);
        }

        $user->wallet -= $nairaAmount;
        $user->save();

        $number = $json['number'];
        $activationId = $json['request_id'];

        $verification = Verification::create([
            'user_id'       => $user->id,
            'name'          => 'Server 4',
            'server'        => 4,
            'number'        => $number,
            'activation_id' => $activationId,
            'price'         => $usd,
            'naira_amount'  => $nairaAmount,
            'status'        => 'Reserved',
            'service'       => $applicationId,
            'country_id'    => $countryId,
        ]);

        $serviceName = 'Unknown Service';

        try {
            $serviceList = Http::get("https://api.sms-man.com/control/applications", [
                'token' => $this->token
            ])->json();

            foreach ($serviceList as $s) {
                if ((int)$s['id'] === (int)$applicationId) {
                    $serviceName = $s['title'] ?? $s['name'] ?? 'Unknown Service';
                    break;
                }
            }
        } catch (\Throwable $e) {
            \Log::error("Service name fetch failed: " . $e->getMessage());
        }

        try {
            $message = "📲 <b>Server 4 Purchase (SMS-Man)</b>\n"
                . "👤 User: <b>{$user->email}</b>\n"
                . "🌍 Country ID: <b>{$countryId}</b>\n"
                . "🧾 Service: <b>{$serviceName}</b>\n"
                . "📞 Number: <code>{$number}</code>\n"
                . "💵 Price: ₦" . number_format($nairaAmount, 2) . "\n"
                . "🔢 Activation ID: <code>{$activationId}</code>\n"
                . "💼 Wallet Balance: ₦" . number_format($user->fresh()->wallet, 2) . "\n"
                . "🕐 " . now()->format('Y-m-d H:i:s');

            (new \App\Services\TelegramService())->sendMessage($message);
        } catch (\Throwable $e) {
            \Log::error('Telegram error (smsman purchase): ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => '✅ Number purchased successfully. ₦' . number_format($nairaAmount, 2) . ' deducted.',
            'verification' => array_merge($verification->toArray(), [
                'service_name' => $serviceName
            ])
        ]);
    }

   public function check($id)
{
    $v = Verification::findOrFail($id);

    // Fetch SMS code from SMS-Man
    $res = Http::get("https://api.sms-man.com/control/get-sms", [
        'token' => $this->token,
        'request_id' => $v->activation_id,
    ])->json();

    // Update code if found
    if (isset($res['sms_code'])) {
        $v->update([
            'code' => $res['sms_code'],
            'status' => 'Received',
        ]);
    }

    // Optional: Lookup service name to return to frontend
    $serviceName = 'Unknown Service';
    try {
        $serviceList = Http::get("https://api.sms-man.com/control/applications", [
            'token' => $this->token
        ])->json();

        foreach ($serviceList as $s) {
            if ((int)$s['id'] === (int)$v->service) {
                $serviceName = $s['title'] ?? $s['name'] ?? 'Unknown Service';
                break;
            }
        }
    } catch (\Throwable $e) {
        \Log::warning("Service name lookup failed: " . $e->getMessage());
    }

    return response()->json([
        'status'        => $v->status,
        'code'          => $v->code ?? null,
        'number'        => $v->number,
        'service_name'  => $serviceName,
    ]);
}


    public function cancel($id)
    {
        $v = Verification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($v->code) {
            return response()->json(['success' => false, 'message' => 'Code already received']);
        }

        Http::get("https://api.sms-man.com/control/set-status", [
            'token' => $this->token,
            'request_id' => $v->activation_id,
            'status' => 'cancel',
        ]);

        Auth::user()->increment('wallet', $v->naira_amount);
        $v->delete();

        return response()->json([
    'success' => true,
    'message' => 'Cancelled and refunded ₦' . $v->naira_amount,
    'refund' => $v->naira_amount // ✅ important for Flutter
]);

    }
}
