<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Verification;
use App\Models\Setting;

class Server1VerificationController extends Controller
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = Setting::get('daisysms_api_key');
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();

        $verifications = Verification::where('user_id', $user->id)
            ->where('server', 1)
            ->latest()
            ->take(20)
            ->get();

        $apiKey = Setting::get('daisysms_api_key');
        $usdToNairaRate = Setting::get('usd_to_naira_rate', 1400);
        $serviceGain = Setting::get('service_gain', 0);

        $response = Http::get("https://daisysms.com/stubs/handler_api.php", [
            'api_key' => $apiKey,
            'action' => 'getPricesVerification',
        ]);

        $raw = json_decode($response->body(), true);
        $services = [];

        if (is_array($raw)) {
            foreach ($raw as $code => $countryData) {
                if (isset($countryData['187'])) {
                    $entry = $countryData['187'];
                    $services[] = [
                        'service' => $code,
                        'name' => $entry['name'],
                        'price' => (floatval($entry['cost']) * $usdToNairaRate) + $serviceGain,
                    ];
                }
            }
        }

        return response()->json([
            'verifications' => $verifications,
            'services' => $services,
        ]);
    }
 public function send_notification($message)
    {
        $chat_id = '6743906881';
        $bot_token = '7287253308:AAFX63ArvBktT7U1CvfAL58mxT6ryT0vT0Y';

        $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";

        $post_fields = [
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_fields,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }
   public function purchaseNumber(Request $request)
{
    $user = auth()->user();
    $serviceCode = $request->input('service');

    $apiKey = Setting::get('daisysms_api_key');
    $usdToNairaRate = Setting::get('usd_to_naira_rate', 1400);
    $serviceGain = Setting::get('service_gain', 0);

    $response = Http::get("https://daisysms.com/stubs/handler_api.php", [
        'api_key' => $apiKey,
        'action' => 'getPricesVerification',
    ]);

    $services = json_decode($response->body(), true);

    if (!isset($services[$serviceCode]['187'])) {
        return response()->json(['success' => false, 'message' => 'Service not available in USA.']);
    }

    $details = $services[$serviceCode]['187'];
    $serviceName = $details['name'];
    $price = (floatval($details['cost']) * $usdToNairaRate) + $serviceGain;

    $recent = Verification::where('user_id', $user->id)
        ->where('service', $serviceCode)
        ->where('status', 'active')
        ->where('created_at', '>=', now()->subMinutes(1))
        ->first();

    if ($recent) {
        return response()->json(['success' => false, 'message' => '⚠️ You recently purchased this service. Please wait.']);
    }

    if ($user->wallet < $price) {
        return response()->json(['success' => false, 'message' => 'Insufficient wallet balance.']);
    }

    $orderResponse = Http::get("https://daisysms.com/stubs/handler_api.php", [
        'api_key' => $apiKey,
        'action' => 'getNumber',
        'service' => $serviceCode,
    ]);

    $body = $orderResponse->body();

    if (!str_starts_with($body, "ACCESS_NUMBER")) {
        Log::error("Failed to purchase number: {$body}");
        return response()->json(['success' => false, 'message' => 'Failed to purchase number.']);
    }

    [$_, $id, $number] = explode(':', $body);

    $verification = null;

    DB::transaction(function () use ($user, $price, $serviceCode, $serviceName, $number, $id, &$verification) {
        $user->decrement('wallet', $price);

        $verification = Verification::create([
            'user_id' => $user->id,
            'server' => 1,
            'service' => $serviceCode,
            'name' => $serviceName,
            'number' => $number,
            'activation_id' => $id,
            'price' => $price,
            'status' => 'active',
        ]);
    });

    $this->send_notification(
        "📲 <b>New Number Purchase</b>\n"
        . "👤 User: <b>{$user->email}</b>\n"
        . "💳 Price: ₦" . number_format($price, 2) . "\n"
        . "💼 Wallet Balance: ₦" . number_format($user->wallet, 2) . "\n"
        . "🧾 Service: <b>{$serviceName}</b>\n"
        . "📞 Number: <code>{$number}</code>\n"
        . "🔢 Activation ID: {$id}\n"
        . "🕐 At: " . now()->format('Y-m-d H:i:s')
    );

    return response()->json([
        'success' => true,
        'message' => 'Number purchased successfully.',
        'verification' => [
            'id' => $verification->id,
            'service' => $verification->service,
            'name' => $verification->name,
            'number' => $verification->number,
            'code' => $verification->code,
            'status' => $verification->status,
            'activation_id' => $verification->activation_id,
            'created_at' => $verification->created_at->toDateTimeString(),
        ],
    ]);
}


public function cancel($id)
{
    $verification = Verification::where('id', $id)
        ->where('user_id', auth()->id())
        ->where('status', 'active')
        ->first();

    // ✅ If not found, assume it was already cancelled
    if (!$verification) {
        return response()->json([
            'success' => false,
            'message' => 'Verification already cancelled or not found.',
            'id' => $id,
        ]);
    }

    $user = auth()->user();
    $user->increment('wallet', $verification->price);

    $verification->delete();

    $this->send_notification(
        "❌ <b>Verification Cancelled</b>\n"
        . "👤 User: <b>{$user->email}</b>\n"
        . "💵 Refunded: ₦" . number_format($verification->price, 2) . "\n"
        . "💼 Wallet Balance: ₦" . number_format($user->wallet, 2) . "\n"
        . "📞 Number: <code>{$verification->number}</code>\n"
        . "🧾 Service: <b>{$verification->name}</b>\n"
        . "🕐 At: " . now()->format('Y-m-d H:i:s')
    );

    return response()->json([
        'success' => true,
        'message' => 'Verification cancelled and refunded.',
        'id' => $id,
    ]);
}



    public function pollCode($id)
    {
        $verification = Verification::where('user_id', auth()->id())->findOrFail($id);

        if ($verification->status === 'done') {
            return response()->json([
                'status' => 'done',
                'code' => $verification->code,
                'stop' => true,
            ]);
        }

        $response = Http::get('https://daisysms.com/stubs/handler_api.php', [
            'api_key' => $this->apiKey,
            'action' => 'getStatus',
            'id' => $verification->activation_id,
        ]);

        if (str_starts_with($response->body(), 'STATUS_OK')) {
            $code = explode(':', $response->body())[1];

            $verification->update([
                'code' => $code,
                'status' => 'done',
            ]);

            $user = auth()->user();
            $message = "📬 <b>SMS Code Received</b>\n"
                     . "👤 User: <b>{$user->email}</b>\n"
                     . "💼 Wallet Balance: ₦" . number_format($user->wallet, 2) . "\n"
                     . "🧾 Service: <b>{$verification->name}</b>\n"
                     . "📞 Number: <code>{$verification->number}</code>\n"
                     . "🔐 Code: <b>{$code}</b>\n"
                     . "🕐 At: " . now()->format('Y-m-d H:i:s');

            $this->send_notification($message);

            return response()->json([
                'status' => 'done',
                'code' => $code,
                'stop' => true,xq
            ]);
        }

        return response()->json([
            'status' => $verification->status,
            'code' => $verification->code,
            'stop' => false,
        ]);
    }


    public function webhook(Request $request)
    {
        // ✅ Verify shared secret (optional but recommended)
        if ($request->header('x-daisy-secret') !== config('services.daisysms.webhook_secret')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $verification = Verification::where('activation_id', $request->activationId)->first();

        if ($verification) {
            $verification->update([
                'code' => $request->code,
                'status' => 'done',
            ]);
        }

        return response()->json(['message' => 'Webhook received']);
    }
}
