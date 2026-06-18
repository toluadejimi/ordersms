<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Verification;
use App\Models\User;
use App\Models\Setting;

class VerificationController extends Controller
{
    private $apiKey;

    public function __construct()
    {
        $this->middleware('auth');
        $this->apiKey = Setting::get('daisysms_api_key');
    }

    public function smsHistory()
    {
        $user = auth()->user();

        $allVerifications = $user->verifications()
            ->whereIn('status', ['done', 'completed', 'received'])
            ->latest()
            ->paginate(20);

        $totalVerifications = $user->verifications()
            ->whereIn('status', ['done', 'completed', 'received'])
            ->count();

        return view('user.sms-history', compact(
            'allVerifications',
            'totalVerifications'
        ));
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

    public function dashboard()
    {
        $verifications = Verification::where('user_id', auth()->id())
            ->where('server', '1')
            ->latest()
            ->paginate(10);

        $apiKey = Setting::get('daisysms_api_key');
        $usdToNairaRate = Setting::get('usd_to_naira_rate', 1400);
        $serviceGain = Setting::get('service_gain', 0);

        $response = Http::get("https://daisysms.com/stubs/handler_api.php", [
            'api_key' => $apiKey,
            'action' => 'getPricesVerification',
        ]);

        $raw = json_decode($response->body(), true);
        $usaServices = [];

        if (is_array($raw)) {
            foreach ($raw as $serviceCode => $countries) {
                if (isset($countries['187'])) {
                    $serviceDetails = $countries['187'];
                    $priceInNaira = (floatval($serviceDetails['cost']) * $usdToNairaRate) + $serviceGain;

                    $usaServices[] = [
                        'service' => $serviceCode,
                        'name' => $serviceDetails['name'],
                        'price' => $priceInNaira,
                    ];
                }
            }
        }

        return view('user.home', [
            'verifications' => $verifications,
            'services' => $usaServices,
        ]);
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
            return back()->with('error', 'Service not available in USA.');
        }

        $details = $services[$serviceCode]['187'];
        $serviceName = $details['name'];
        $price = (floatval($details['cost']) * $usdToNairaRate) + $serviceGain;

        // Prevent double purchase
        $recent = Verification::where('user_id', $user->id)
            ->where('service', $serviceCode)
            ->where('status', 'active')
            ->where('created_at', '>=', now()->subMinutes(1))
            ->first();

        if ($recent) {
            return back()->with('error', '⚠️ You recently purchased this service. Please wait.');
        }

        if ($user->wallet < $price) {
            return back()->with('error', 'Insufficient wallet balance.');
        }

        $orderResponse = Http::get("https://daisysms.com/stubs/handler_api.php", [
            'api_key' => $apiKey,
            'action' => 'getNumber',
            'service' => $serviceCode,
        ]);

        $body = $orderResponse->body();

        if (!str_starts_with($body, "ACCESS_NUMBER")) {
            Log::error("Failed to purchase number: {$body}");
            return back()->with('error', 'Failed to purchase number.');
        }

        [$_, $id, $number] = explode(':', $body);

        // Use transaction to protect wallet deduction + verification creation
        DB::transaction(function () use ($user, $price, $serviceCode, $serviceName, $number, $id) {
            $user->decrement('wallet', $price);

            Verification::create([
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

        $message = "📲 <b>New Number Purchase</b>\n"
                 . "👤 User: <b>{$user->email}</b>\n"
                 . "💳 Price: ₦" . number_format($price, 2) . "\n"
                 . "💼 Wallet Balance: ₦" . number_format($user->wallet, 2) . "\n"
                 . "🧾 Service: <b>{$serviceName}</b>\n"
                 . "📞 Number: <code>{$number}</code>\n"
                 . "🔢 Activation ID: {$id}\n"
                 . "🕐 At: " . now()->format('Y-m-d H:i:s');

        $this->send_notification($message);

        return back()->with('success', 'Number purchased successfully.');
    }

    public function cancel($id)
    {
        $verification = Verification::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->firstOrFail();

        $user = auth()->user();
        $user->wallet += $verification->price;
        $user->save();

        $verification->delete();

        $message = "❌ <b>Verification Cancelled</b>\n"
                 . "👤 User: <b>{$user->email}</b>\n"
                 . "💵 Refunded: ₦" . number_format($verification->price, 2) . "\n"
                 . "💼 Wallet Balance: ₦" . number_format($user->wallet, 2) . "\n"
                 . "📞 Number: <code>{$verification->number}</code>\n"
                 . "🧾 Service: <b>{$verification->name}</b>\n"
                 . "🕐 At: " . now()->format('Y-m-d H:i:s');

        $this->send_notification($message);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'id' => $id]);
        }

        return redirect()->back()->with('success', 'Verification cancelled and funds refunded.');
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
                'stop' => true,
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
