<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class Server3VerificationController extends Controller
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = Setting::get('smspool_api_key');
    }

    public function index()
    {
        $countries = $services = $pools = [];

        try {
            $countriesRes = Http::get("https://api.smspool.net/country/retrieve_all", ['key' => $this->apiKey]);
            if ($countriesRes->successful()) {
                $countries = $countriesRes->json();
            }

            $servicesRes = Http::get("https://api.smspool.net/service/retrieve_all", ['key' => $this->apiKey]);
            if ($servicesRes->successful()) {
                $rawServices = $servicesRes->json();
                foreach ($rawServices as $item) {
                    $services[] = [
                        'ID'    => $item['ID'] ?? null,
                        'name'  => $item['name'] ?? 'Unknown',
                        'price' => isset($item['cost']) ? (float) $item['cost'] : 0,
                    ];
                }
            }

            $poolsRes = Http::get("https://api.smspool.net/pool/retrieve_all", ['key' => $this->apiKey]);
            if ($poolsRes->successful()) {
                $pools = $poolsRes->json();
            }
        } catch (\Exception $e) {
            Log::error('Error fetching Server 3 data', ['message' => $e->getMessage()]);
        }

    $rate = Setting::get('smspool_usd_to_naira_rate', 1600);
$gain = Setting::get('smspool_service_gain', 0);

return view('verifications.server3', compact('countries', 'services', 'pools', 'rate', 'gain'));

    }
public function getPrice(Request $request)
{
    $request->validate([
        'country' => 'required',
        'service' => 'required',
    ]);

    $payload = [
        'key'     => $this->apiKey,
        'country' => $request->input('country'),
        'service' => $request->input('service'),
      
    ];

    $response = Http::asForm()->post('https://api.smspool.net/purchase/sms', $payload);
    $json = $response->json();

    \Log::info('getPrice() purchase response', [
        'payload' => $payload,
        'response' => $json
    ]);

    if (!($json['success'] ?? false) || !isset($json['cost']) || !isset($json['orderid'])) {
        return response()->json([
            'success' => false,
            'message' => $json['message'] ?? 'Unable to fetch price from SMSPool.',
        ]);
    }

    $usd = (float) $json['cost'];
    $rate = Setting::get('smspool_usd_to_naira_rate', 1600);
    $gain = Setting::get('smspool_service_gain', 0);

    // Optional delay
    sleep(5);

    Http::asForm()->post('https://api.smspool.net/sms/cancel', [
        'key'     => $this->apiKey,
        'orderid' => $json['orderid'],
    ]);

    return response()->json([
        'success'     => true,
        'price_usd'   => $usd,
        'price_naira' => round(($usd * $rate) + $gain, 2),
        'rate'        => $rate,
        'gain'        => $gain,
    ]);
}



public function check($id)
{
    $verification = Verification::where('id', $id)
        ->where('server', 3)
        ->where('user_id', auth()->id())
        ->first();

  if (!$verification || $verification->status === 'Completed') {
    return response()->json([
        'id' => $verification ? $verification->id : null,
        'code' => $verification ? $verification->code : null,
        'status' => 'Completed',
    ]);
}

    $response = Http::asForm()->post("https://api.smspool.net/sms/check", [
        'key' => $this->apiKey,
        'orderid' => $verification->activation_id,
    ]);

    $json = $response->json();

    if (!($json['sms'] ?? false)) {
        return response()->json([
            'id' => $verification->id,
            'status' => $verification->status,
            'code' => null,
            'message' => 'Still waiting for SMS.',
        ]);
    }

    // Save code
    $verification->code = $json['sms'];
    $verification->status = 'Completed';
    $verification->save();

    try {
        $user = $verification->user;
        $message = "📬 <b>Server 3 Code Received</b>\n"
                 . "👤 User: <b>{$user->email}</b>\n"
                 . "🧾 Service: <b>{$verification->service}</b>\n"
                 . "📞 Number: <code>{$verification->number}</code>\n"
                 . "🔐 Code: <b>{$json['sms']}</b>\n"
                 . "💼 Wallet Balance: ₦" . number_format($user->wallet, 2) . "\n"
                 . "🕐 " . now()->format('Y-m-d H:i:s');

        (new \App\Services\TelegramService())->sendMessage($message);
    } catch (\Throwable $e) {
        \Log::error('Telegram error (smspool check): ' . $e->getMessage());
    }

    return response()->json([
        'id' => $verification->id,
        'status' => $verification->status,
        'code' => $verification->code,
    ]);
}

public function purchase(Request $request)
{
    $request->validate([
        'country' => 'required|string',
        'service' => 'required|string',
    ]);

    $user = Auth::user();

    $response = Http::asForm()->post('https://api.smspool.net/purchase/sms', [
        'key'     => $this->apiKey,
        'country' => $request->country,
        'service' => $request->service,
    ]);

   $json = $response->json();

    if (!($json['success'] ?? false)) {
        $message = strip_tags($json['message'] ?? 'Purchase failed.');
        $message = preg_replace('/^Pool\s+\w+:\s*/i', '', $message);

        if (str_contains(strtolower($message), 'not available')) {
            return back()->with('error', '📵 Number not available for now. Please try again later.');
        }

        return back()->with('error', $message);
    }

    $usd = (float) ($json['cost'] ?? 0);
    $rate = Setting::get('smspool_usd_to_naira_rate', 1600);
    $gain = Setting::get('smspool_service_gain', 0);
    $totalNaira = round(($usd * $rate) + $gain, 2);

    if ($user->wallet < $totalNaira) {
        return back()->with('error', '❌ Insufficient wallet balance. Required: ₦' . number_format($totalNaira, 2));
    }

    $user->wallet -= $totalNaira;
    $user->save();

    Verification::create([
        'user_id'       => $user->id,
        'name'          => 'Server 3',
        'server'        => 3,
        'number'        => $json['number'] ?? null,
        'activation_id' => $json['orderid'] ?? uniqid(),
        'status'        => isset($json['number']) ? 'Reserved' : 'Waiting',
        'code'          => null,
        'price'         => $usd,
        'service'       => $request->service,
        'country_id'    => $request->country,
        'naira_amount'  => $totalNaira,
    ]);

    $number = $json['number'] ?? 'Awaiting';
$activationId = $json['orderid'] ?? uniqid();

try {
    $message = "📲 <b>Server 3 Purchase</b>\n"
             . "👤 User: <b>{$user->email}</b>\n"
             . "🌍 Country: <b>{$request->country}</b>\n"
             . "🧾 Service: <b>{$request->service}</b>\n"
             . "📞 Number: <code>{$number}</code>\n"
             . "💵 Price: ₦" . number_format($totalNaira, 2) . "\n"
             . "🔢 Activation ID: <code>{$activationId}</code>\n"
             . "💼 Wallet: ₦" . number_format($user->fresh()->wallet, 2) . "\n"
             . "🕐 " . now()->format('Y-m-d H:i:s');

    (new \App\Services\TelegramService())->sendMessage($message);
} catch (\Throwable $e) {
    \Log::error('Telegram error (smspool purchase): ' . $e->getMessage());
}

    return redirect()->back()->with('success', '✅ Number purchased. ₦' . number_format($totalNaira, 2) . ' deducted from your wallet.');
}
 public function cancel($id)
{
    $verification = Verification::where('id', $id)
        ->where('user_id', Auth::id())
        ->firstOrFail();

    // If code already exists, block cancellation
    if (!empty($verification->code)) {
        return back()->with('error', 'Cannot cancel. Code has already been received. ✅');
    }

    // Attempt API cancellation
    $response = Http::asForm()->post("https://api.smspool.net/sms/cancel", [
        'key' => $this->apiKey,
        'orderid' => $verification->activation_id,
    ]);

    $json = $response->json();

    Log::info('SMSPool Cancel Attempt', [
        'orderid' => $verification->activation_id,
        'response_status' => $response->status(),
        'response_body' => $response->body(),
        'json' => $json
    ]);

    // If code has NOT dropped, allow cancellation regardless of success/failure
    if (empty($verification->code)) {
        $user = Auth::user();
        $refundAmount = $verification->naira_amount;

        $user->wallet += $refundAmount;
        $user->save();

        $verification->delete();

        // ✅ Telegram Notification
        try {
            $message = "❌ <b>Server 3 Cancelled</b>\n"
                     . "👤 User: <b>{$user->name}</b>\n"
                     . "🧾 Service: <b>{$verification->service}</b>\n"
                     . "📞 Number: <code>{$verification->number}</code>\n"
                     . "🔢 Activation ID: <code>{$verification->activation_id}</code>\n"
                     . "💰 Refunded: ₦" . number_format($refundAmount, 2) . "\n"
                     . "💼 Wallet: ₦" . number_format($user->wallet, 2) . "\n"
                     . "🕐 " . now()->format('Y-m-d H:i:s');

            (new TelegramService())->sendMessage($message);
        } catch (\Throwable $e) {
            \Log::error('Telegram error (smspool cancel): ' . $e->getMessage());
        }

        return back()->with('success', '❌ Number cancelled. ₦' . number_format($refundAmount, 2) . ' refunded to your wallet.');
    }

    // Block if code exists but cancellation failed
    $message = strip_tags($json['message'] ?? 'Failed to cancel number.');
    $message = preg_replace('/^Pool\s+\w+:\s*/i', '', $message);

    Log::error('SMSPool Cancel Failed', [
        'orderid' => $verification->activation_id,
        'message' => $message
    ]);

    return back()->with('error', $message ?: 'Service not available at this time.');
}
}