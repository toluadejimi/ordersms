<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Verification;
use App\Models\Setting;
use App\Services\TelegramService;

class Server2VerificationController extends Controller
{
    protected $baseUrl = 'https://www.tellabot.com/sims/api_command.php';
    protected $user;
    protected $apiKey;

    public function __construct()
    {
        $this->user = config('services.tellabot.user', 'test');
        $this->apiKey = config('services.tellabot.key', '0123456789');
    }

    public function index()
    {
        $user = auth()->user();
      $verifications = Verification::where('server', 'server2')
    ->where('user_id', $user->id)
    ->latest()
    ->paginate(10);


        $rate = Setting::get('tellabot_usd_to_naira_rate', 1600);
        $gain = Setting::get('tellabot_gain_percent', 0);
        $services = [];

        $response = Http::get($this->baseUrl, [
            'cmd' => 'list_services',
            'user' => $this->user,
            'api_key' => $this->apiKey,
        ]);

        if ($response->ok() && data_get($response->json(), 'status') === 'ok') {
            foreach (data_get($response->json(), 'message', []) as $item) {
                if (!isset($item['name'], $item['price'])) continue;

                $name = strtolower($item['name']);
                $priceUsd = floatval($item['price']);
                $finalPrice = 0;

                if ($name === 'whatsapp') {
                    $finalPrice = floatval(Setting::get('whatsapp_price_override', 0));
                } elseif ($name === 'telegram') {
                    $finalPrice = floatval(Setting::get('telegram_price_override', 0));
                } else {
                    $basePrice = $priceUsd * $rate;
                    $finalPrice = $basePrice + $gain;
                }

                $services[] = [
                    'name' => $item['name'],
                    'price_usd' => $priceUsd,
                    'price_ngn' => round($finalPrice),
                ];
            }
        }

        $pollingData = $verifications->whereIn('status', ['Waiting', 'Reserved'])->map(function ($v) {
            return ['id' => $v->id, 'activation_id' => $v->activation_id];
        })->values()->all();

        return view('server2.index', compact('verifications', 'services', 'pollingData', 'gain'));
    }

    public function buyNumber(Request $request)
    {
        $request->validate(['service' => 'required|string']);

        $user = Auth::user();
        $service = strtolower($request->service);
        $rate = Setting::get('tellabot_usd_to_naira_rate', 1600);
        $gain = floatval(Setting::get('tellabot_gain_percent', 0));
        $markup = 0;
        $includeMarkup = false;

        if ($service === 'whatsapp') {
            $markup = floatval(Setting::get('whatsapp_markup_percent', 0));
            $priceNgn = floatval(Setting::get('whatsapp_price_override', 0));
            $includeMarkup = true;
        } elseif ($service === 'telegram') {
            $markup = floatval(Setting::get('telegram_markup_percent', 0));
            $priceNgn = floatval(Setting::get('telegram_price_override', 0));
            $includeMarkup = true;
        } else {
            $priceNgn = null;
        }

        if ($includeMarkup && $priceNgn <= 0) {
            return back()->with('error', ucfirst($service) . ' override price not configured.');
        }

        $params = [
            'cmd' => 'request',
            'user' => $this->user,
            'api_key' => $this->apiKey,
            'service' => $service,
        ];

        if ($includeMarkup) {
            $params['markup'] = $markup;
        }

        $response = Http::get($this->baseUrl, $params);
        $json = $response->json();

        if (!$response->ok() || data_get($json, 'status') !== 'ok') {
            Log::error("Server2 buyNumber failed: " . $response->body());
            return back()->with('error', 'Service unavailable. Try again.');
        }

        $message = data_get($json, 'message.0');
        $id = data_get($message, 'id');
        $number = data_get($message, 'mdn', 'Awaiting Number');
        $priceUsd = floatval(data_get($message, 'price', 0));

        if (!$id || !$priceUsd) {
            return back()->with('error', 'Invalid response.')->withInput();
        }

        if (is_null($priceNgn)) {
            $priceNgn = round($priceUsd * $rate) + $gain;
        }

        if ($user->wallet < $priceNgn) {
            return back()->with('error', 'Insufficient wallet balance.');
        }

        DB::transaction(function () use ($user, $service, $number, $id, $priceNgn, $markup, $includeMarkup) {
            $user->decrement('wallet', $priceNgn);

            Verification::create([
                'user_id' => $user->id,
                'server' => 'server2',
                'service' => $service,
                'name' => $service,
                'number' => $number,
                'activation_id' => $id,
                'price' => $priceNgn,
                'markup' => $includeMarkup ? $markup : null,
                'status' => 'Reserved',
            ]);
        });

        try {
            $msg = "📲 <b>New Number Reserved (Server 2)</b>\n"
                 . "👤 User: <b>{$user->email}</b>\n"
                 . "🧾 Service: <b>{$service}</b>\n"
                 . "📞 Number: <code>{$number}</code>\n"
                 . "💵 Price: ₦" . number_format($priceNgn, 2) . "\n"
                 . "🔢 ID: <code>{$id}</code>\n"
                 . "💼 Wallet: ₦" . number_format($user->fresh()->wallet, 2) . "\n"
                 . "🕐 " . now();

            (new TelegramService())->sendMessage($msg);
        } catch (\Throwable $e) {
            Log::error('Telegram error: ' . $e->getMessage());
        }

        return back()->with('success', 'Number reserved successfully. ID: ' . $id);
    }

  public function checkStatus($id)
    {
        $verification = Verification::where('id', $id)
            ->where('server', 'server2')
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $response = Http::timeout(10)->get($this->baseUrl, [
            'cmd' => 'request_status',
            'user' => $this->user,
            'api_key' => $this->apiKey,
            'id' => $verification->activation_id,
        ]);

        if (!$response->successful()) {
            return response()->json([
                'status' => $verification->status,
                'number' => $verification->number,
                'code' => $verification->code,
                'has_number' => !empty($verification->number) && $verification->number !== 'Awaiting Number',
            ]);
        }

        $json = $response->json();
        $data = data_get($json, 'message', []);
        $updates = [];

        $apiNumber = data_get($data, 'mdn');
        $apiStatus = data_get($data, 'status');

        if ($apiNumber === 'pending' && $verification->number !== 'Pending') {
            $updates['number'] = 'Pending';
            $updates['status'] = 'Pending';
        }

        if (!empty($apiNumber) && $apiNumber !== 'pending' &&
            (empty($verification->number) || in_array($verification->number, ['Awaiting Number', 'Pending']))) {
            $updates['number'] = $apiNumber;
            $updates['number_received_at'] = now();
        }

        if (isset($apiStatus) &&
            !in_array($verification->status, ['Completed', 'Cancelled', 'Expired']) &&
            in_array($apiStatus, ['Pending', 'Waiting', 'Reserved'])) {
            $updates['status'] = $apiStatus;
        }

        if (!empty($updates)) {
            $verification->update($updates);
        }

        $verification->refresh();
        $hasNumber = !empty($verification->number) && !in_array($verification->number, ['Awaiting Number', 'Pending']);

        return response()->json([
            'status' => $verification->status,
            'number' => $verification->number,
            'code' => $verification->code,
            'has_number' => $hasNumber,
        ]);
    }
    public function readSms($activationId)
    {
        $verification = Verification::where('server', 'server2')
            ->where('activation_id', $activationId)
            ->first();

        if (!$verification || $verification->status === 'Completed') {
            return response()->json([
                'id' => optional($verification)->id,
                'code' => optional($verification)->code,
                'status' => 'Completed',
            ]);
        }

        $response = Http::get($this->baseUrl, [
            'cmd' => 'read_sms',
            'user' => $this->user,
            'api_key' => $this->apiKey,
        ]);

        $messages = data_get($response->json(), 'message', []);
        foreach ($messages as $msg) {
            if (data_get($msg, 'to') === $verification->number && isset($msg['pin'])) {
                $verification->update([
                    'code' => $msg['pin'],
                    'status' => 'Completed',
                ]);

                try {
                    $user = $verification->user;
                    $msgText = "📬 <b>Code Received (Server 2)</b>\n"
                             . "👤 User: <b>{$user->email}</b>\n"
                             . "📞 Number: <code>{$verification->number}</code>\n"
                             . "🔐 Code: <b>{$msg['pin']}</b>\n"
                             . "💼 Wallet: ₦" . number_format($user->wallet, 2) . "\n"
                             . "🕐 " . now();

                    (new TelegramService())->sendMessage($msgText);
                } catch (\Throwable $e) {
                    Log::error('Telegram error (readSms): ' . $e->getMessage());
                }

                return response()->json([
                    'id' => $verification->id,
                    'code' => $msg['pin'],
                    'status' => 'Completed',
                ]);
            }
        }

        return response()->json([
            'id' => $verification->id,
            'code' => null,
            'status' => $verification->status,
        ]);
    }

    public function cancel($id)
    {
        $verification = Verification::where('id', $id)
            ->where('server', 'server2')
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($verification->status === 'Completed') {
            return back()->with('error', 'This verification is already completed.');
        }

        Http::get($this->baseUrl, [
            'cmd' => 'reject',
            'user' => $this->user,
            'api_key' => $this->apiKey,
            'id' => $verification->activation_id,
        ]);

        $user = auth()->user();
        $user->increment('wallet', $verification->price);
        $wallet = $user->fresh()->wallet;
        $verification->delete();

        try {
            $msg = "❌ <b>Cancelled Server 2 Verification</b>\n"
                 . "👤 User: <b>{$user->email}</b>\n"
                 . "🧾 Service: <b>{$verification->name}</b>\n"
                 . "💵 Refunded: ₦" . number_format($verification->price, 2) . "\n"
                 . "💼 Wallet: ₦" . number_format($wallet, 2) . "\n"
                 . "🕐 " . now();

            (new TelegramService())->sendMessage($msg);
        } catch (\Throwable $e) {
            Log::error('Telegram cancel error: ' . $e->getMessage());
        }

        return back()->with('success', 'Verification cancelled and refunded.');
    }
}
