<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Verification;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Server2ApiController extends Controller
{
    protected $baseUrl = 'https://www.tellabot.com/sims/api_command.php';
    protected $user;
    protected $apiKey;

    public function __construct()
    {
        $this->user = config('services.tellabot.user');
        $this->apiKey = config('services.tellabot.key');
    }

public function getServices(Request $request)
{
    $user = $request->user();
    $rate = Setting::get('tellabot_usd_to_naira_rate', 1600);
    $gain = Setting::get('service_gain', 0);
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

    // 🔁 Include verifications for the authenticated user
    $verifications = Verification::where('user_id', $user->id)
        ->where('server', 'server2')
        ->latest()
        ->take(20)
        ->get()
        ->map(function ($v) {
            return [
                'id' => $v->id,
                'service' => $v->service,
                'name' => $v->name,
                'number' => $v->number,
                'code' => $v->code,
                'status' => $v->status,
                'activation_id' => $v->activation_id,
                'created_at' => $v->created_at->toDateTimeString(),
            ];
        });

    return response()->json([
        'services' => $services,
        'verifications' => $verifications,
    ]);
}



    public function getDashboardData(Request $request)
{
    $user = $request->user();

    $rate = Setting::get('tellabot_usd_to_naira_rate', 1600);
    $gain = Setting::get('service_gain', 0);

    $verifications = Verification::where('user_id', $user->id)
        ->where('server', 'server2')
        ->latest()
        ->take(20)
        ->get()
        ->map(function ($v) {
            return [
                'id' => $v->id,
                'service' => $v->service,
                'name' => $v->name,
                'number' => $v->number,
                'code' => $v->code,
                'status' => $v->status,
                'activation_id' => $v->activation_id,
                'created_at' => $v->created_at->toDateTimeString(),
            ];
        });

    $services = [];

    $response = Http::get('https://www.tellabot.com/sims/api_command.php', [
        'cmd' => 'list_services',
        'user' => config('services.tellabot.user'),
        'api_key' => config('services.tellabot.key'),
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

    return response()->json([
        'services' => $services,
        'verifications' => $verifications,
    ]);
}


public function buy(Request $request)
{
    $request->validate(['service' => 'required|string']);

    $user = Auth::user();
    $service = strtolower($request->service);
    $rate = Setting::get('tellabot_usd_to_naira_rate', 1600);
    $gain = floatval(Setting::get('service_gain', 0));
    $markup = 0;
    $includeMarkup = false;

    // Step 1: Determine override price for fixed services
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

    // Step 2: If override exists, validate wallet before API call
    if ($includeMarkup) {
        if ($priceNgn <= 0) {
            return response()->json([
                'success' => false,
                'message' => ucfirst($service) . ' override price not configured.',
            ], 400);
        }

        if ($user->wallet < $priceNgn) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient wallet balance.',
            ], 400);
        }
    }

    // Step 3: Make API call to reserve number
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
        Log::error("Server2 buy failed: " . $response->body());
        return response()->json([
            'success' => false,
            'message' => 'Service unavailable. Try again.',
        ], 500);
    }

    $message = data_get($json, 'message.0');
    $id = data_get($message, 'id');
    $number = data_get($message, 'mdn', 'Awaiting Number');
    $priceUsd = floatval(data_get($message, 'price', 0));

    if (!$id || !$priceUsd) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid API response.',
        ], 422);
    }

    // Step 4: If not fixed price, calculate NGN price now and validate wallet
    if (is_null($priceNgn)) {
        $priceNgn = round($priceUsd * $rate) + $gain;

        if ($user->wallet < $priceNgn) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient wallet balance.',
            ], 400);
        }
    }

    // Step 5: Deduct wallet and create verification
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

    // Optional Telegram log
    try {
        $msg = "📲 <b>New Number Reserved (Server 2)</b>\n"
             . "👤 User: <b>{$user->email}</b>\n"
             . "🧾 Service: <b>{$service}</b>\n"
             . "📞 Number: <code>{$number}</code>\n"
             . "💵 Price: ₦" . number_format($priceNgn, 2) . "\n"
             . "🔢 ID: <code>{$id}</code>\n"
             . "💼 Wallet: ₦" . number_format($user->fresh()->wallet, 2) . "\n"
             . "🕐 " . now();
        (new \App\Services\TelegramService())->sendMessage($msg);
    } catch (\Throwable $e) {
        Log::error('Telegram error: ' . $e->getMessage());
    }

    return response()->json([
        'success' => true,
        'message' => 'Number reserved successfully.',
        'verification' => [
            'id' => $id,
            'service' => $service,
            'name' => $service,
            'number' => $number,
            'status' => 'Reserved',
            'activation_id' => $id,
            'price' => $priceNgn,
            'created_at' => now()->toDateTimeString(),
        ],
    ]);
}




   public function Status($id)
    {
        $verification = Verification::where('id', $id)
            ->where('server', 'server2')
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $response = Http::get($this->baseUrl, [
            'cmd' => 'request_status',
            'user' => $this->user,
            'api_key' => $this->apiKey,
            'id' => $verification->activation_id,
        ]);

        $json = $response->json();
        $data = data_get($json, 'message', []);

        $updates = [];

        if ($verification->status !== 'Completed') {
            if (!empty($data['mdn']) && !$verification->number) {
                $updates['number'] = $data['mdn'];
            }
            if (isset($data['status']) && in_array($data['status'], ['Reserved', 'Completed'])) {
                $updates['status'] = $data['status'];
            }
            if (isset($data['markup']) && in_array($verification->name, ['whatsapp', 'telegram'])) {
                $updates['markup'] = $data['markup'];
            }

            if (!empty($updates)) {
                $verification->update($updates);
            }
        }


    return response()->json([
        'status' => $verification->status,
        'code' => $verification->code,
        'number' => $verification->number,
    ]);
}



  public function readSms($activationId)
{
    $verification = Verification::where('server', 'server2')
        ->where('activation_id', $activationId)
        ->first();

    if (!$verification) {
        return response()->json([
            'success' => false,
            'message' => 'Verification not found.',
        ], 404);
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
        'code' => $verification->code,
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
        return response()->json([
            'success' => false,
            'message' => 'This verification is already completed.',
        ], 400);
    }

    // Reject from provider
    Http::get($this->baseUrl, [
        'cmd' => 'reject',
        'user' => $this->user,
        'api_key' => $this->apiKey,
        'id' => $verification->activation_id,
    ]);

    // Refund to wallet and delete record
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

        (new \App\Services\TelegramService())->sendMessage($msg);
    } catch (\Throwable $e) {
        \Log::error('Telegram cancel error: ' . $e->getMessage());
    }

    return response()->json([
        'success' => true,
        'message' => 'Verification cancelled and refunded.',
    ]);
}

}