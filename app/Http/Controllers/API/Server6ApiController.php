<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Server6VerificationController;

class Server6ApiController extends Controller
{
    protected $core;

    public function __construct()
    {
        $this->core = new Server6VerificationController();
    }

    /**
     * 🔍 Get services (with prices and count) by country
     */
    public function getServices($country)
{
    try {
        $services = $this->core->fetchServices($country);

        if (empty($services)) {
            \Log::warning("No services found for country $country");
        }

        return response()->json([
            'success' => true,
            'services' => $services,
        ]);
    } catch (\Throwable $e) {
        \Log::error("Server6 fetch error: " . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch services',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    /**
     * 🛒 Purchase number
     */
public function purchase(Request $request)
{
    $request->validate([
        'country' => 'required|integer',
        'service' => 'required|string',
    ]);

    $user = auth()->user();
    $apiKey = Setting::get('gogetsms_api_key') ?? config('services.gogetsms.api_key');
    $serviceList = $this->core->fetchServices($request->country);

    if (!isset($serviceList[$request->service])) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid service or country.'
        ], 422);
    }

    $serviceData = $serviceList[$request->service];
    $usdPrice = $serviceData['price_usd'];
    $nairaPrice = $serviceData['price_ngn'];
    $extra = Setting::get('gogetsms_extra_cost') ?? 0;

    if ($user->wallet < $nairaPrice) {
        return response()->json([
            'success' => false,
            'message' => 'Insufficient wallet balance.'
        ], 400);
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

        return response()->json([
            'success' => false,
            'message' => $body
        ], 400);
    }

    [$status, $activation_id, $number] = explode(':', $body);

    DB::beginTransaction();

    try {
        $user->wallet -= $nairaPrice;
        $user->save();

        $verification = Verification::create([
            'user_id'        => $user->id,
            'name'           => $this->core->getServiceNames()[$request->service] ?? strtoupper($request->service), // ✅ FIXED
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

        // 🔔 Telegram notification (optional)
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

            (new \App\Services\TelegramService())->sendMessage($message);
        } catch (\Throwable $e) {
            \Log::error('Telegram error (GoGetSMS purchase): ' . $e->getMessage());
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Number purchased successfully',
            'verification' => [
                'id' => $verification->id,
                'number' => $verification->number,
                'service' => $verification->name,
                'status' => $verification->status,
                'price' => $verification->naira_amount,
            ]
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('GoGetSMS purchase failed', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong during purchase.',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
        ], 500);
    }
}




    /**
     * ❌ Cancel verification and refund
     */
    public function cancel($id)
    {
        try {
            return $this->core->cancel($id);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cancellation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔁 Poll verification status (get code if available)
     */
    public function checkStatus($id)
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
    'number' => $verification->number, // ✅ Include number here
]);
}
}
