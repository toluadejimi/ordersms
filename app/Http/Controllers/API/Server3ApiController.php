<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Verification;
use App\Models\Setting;
use App\Services\TelegramService;

class Server3ApiController extends Controller
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = Setting::get('smspool_api_key');
    }

    public function dashboard()
    {
        $countries = $services = [];

        try {
            Log::info('🔁 Server3 API dashboard hit by user: ' . auth()->id());

            // ✅ FIX: use query string for GET requests
            $countriesRes = Http::get("https://api.smspool.net/country/retrieve_all?key={$this->apiKey}");
            $servicesRes  = Http::get("https://api.smspool.net/service/retrieve_all?key={$this->apiKey}");

            Log::info('🌍 Countries response: ' . $countriesRes->body());
            Log::info('🛠 Services response: ' . $servicesRes->body());

            if ($countriesRes->successful()) {
                $countries = $countriesRes->json();
            }

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
        } catch (\Exception $e) {
            Log::error('❌ Server3 Dashboard Error: ' . $e->getMessage());
        }

        $verifications = Verification::where('user_id', auth()->id())
            ->where('server', 3)
            ->latest()
            ->get();

        return response()->json([
            'countries'     => $countries,
            'services'      => $services,
            'verifications' => $verifications,
        ]);
    }

    public function getPrice(Request $request)
{
    $request->validate([
        'country' => 'required|string',
        'service' => 'required|string',
    ]);

    $payload = [
        'key'     => $this->apiKey,
        'country' => $request->country,
        'service' => $request->service,
    ];

    $response = Http::asForm()->post('https://api.smspool.net/purchase/sms', $payload);
    $json = $response->json();

    // Handle failure or missing fields gracefully
    if (
        !($json['success'] ?? false) ||
        empty($json['orderid']) ||
        empty($json['cost']) ||
        !is_numeric($json['cost'])
    ) {
        return response()->json([
            'success' => false,
            'message' => 'Number not available',
        ]);
    }

    // Cancel order immediately to avoid charges
    sleep(3);
    Http::asForm()->post('https://api.smspool.net/sms/cancel', [
        'key'     => $this->apiKey,
        'orderid' => $json['orderid'],
    ]);

    // Pricing calculation
    $usd = (float) $json['cost'];
    $rate = Setting::get('smspool_usd_to_naira_rate', 1600);
    $gain = Setting::get('smspool_service_gain', 0);
    $priceNaira = round(($usd * $rate) + $gain, 2);

    return response()->json([
        'success'     => true,
        'price_usd'   => $usd,
        'price_naira' => $priceNaira,
        'rate'        => $rate,
        'gain'        => $gain,
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

    // 🛑 Handle missing or invalid number, cost, or orderid
    if (
        !($json['success'] ?? false) ||
        empty($json['number']) ||
        empty($json['orderid']) ||
        empty($json['cost']) ||
        !is_numeric($json['cost'])
    ) {
        return response()->json([
            'success' => false,
            'message' => '❌ Number not available. Please try again later.',
        ]);
    }

    $usd = (float) $json['cost'];
    $rate = Setting::get('smspool_usd_to_naira_rate', 1600);
    $gain = Setting::get('smspool_service_gain', 0);
    $totalNaira = round(($usd * $rate) + $gain, 2);

    if ($user->wallet < $totalNaira) {
        // Cancel the number immediately to prevent it being held
        Http::asForm()->post('https://api.smspool.net/sms/cancel', [
            'key'     => $this->apiKey,
            'orderid' => $json['orderid'],
        ]);

        return response()->json(['success' => false, 'message' => '❌ Insufficient wallet balance.']);
    }

    // Deduct user wallet
    $user->wallet -= $totalNaira;
    $user->save();

    // Save verification
    $verification = Verification::create([
        'user_id'       => $user->id,
        'name'          => 'Server 3',
        'server'        => 3,
        'number'        => $json['number'],
        'activation_id' => $json['orderid'],
        'status'        => 'Reserved',
        'service'       => $request->service,
        'country_id'    => $request->country,
        'price'         => $usd,
        'naira_amount'  => $totalNaira,
    ]);

    return response()->json(['success' => true, 'verification' => $verification]);
}


    public function check($id)
    {
        $verification = Verification::where('id', $id)
            ->where('server', 3)
            ->where('user_id', auth()->id())
            ->first();

        if (!$verification) {
            return response()->json(['success' => false, 'message' => 'Not found.']);
        }

        if ($verification->status === 'Completed') {
            return response()->json(['id' => $verification->id, 'status' => 'Completed', 'code' => $verification->code]);
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

        $verification->code = $json['sms'];
        $verification->status = 'Completed';
        $verification->save();

        return response()->json([
            'id' => $verification->id,
            'status' => 'Completed',
            'code' => $json['sms']
        ]);
    }

    public function cancel($id)
{
    $verification = Verification::where('id', $id)
        ->where('user_id', auth()->id())
        ->first();

    // 🚫 Check: Does not exist or already completed
    if (!$verification || $verification->code) {
        return response()->json([
            'success' => false,
            'message' => '❌ Already completed or not found.',
        ]);
    }

    // 🛑 Cancel the number on SMSPool
    Http::asForm()->post('https://api.smspool.net/sms/cancel', [
        'key'     => $this->apiKey,
        'orderid' => $verification->activation_id,
    ]);

    // 💰 Refund wallet
    $refund = $verification->naira_amount;
    $user = auth()->user();
    $user->wallet += $refund;
    $user->save();

    // 🗑️ Delete verification record
    $verification->delete();

    return response()->json([
        'success' => true,
        'message' => '✅ Cancelled and refunded ₦' . number_format($refund, 2),
    ]);
}

}