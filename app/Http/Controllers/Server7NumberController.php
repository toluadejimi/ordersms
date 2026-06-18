<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Verification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class Server7NumberController extends Controller
{
    protected string $token;
    protected string $base;
    protected const COUNTRY_ID   = 'USA';
    protected const COUNTRY_NAME = 'USA';

  public function __construct()
{
    // ✅ Use the same key as SmsNumberController — same DaisySIM platform
    $this->token = Setting::get('virtual_api_key') ?? env('VIRTUAL_API_KEY', '');
    $this->base  = 'https://daisysim.com/api/v1/server7';
}

    protected function api()
    {
        return Http::withToken($this->token)->acceptJson()->timeout(20);
    }

    protected function usdToNgnRate(): float
    {
        $dbRate = Setting::get('usd_to_naira_rate');
        if ($dbRate && (float) $dbRate > 0) return (float) $dbRate;

        if ($envRate = env('USD_TO_NAIRA_RATE')) return (float) $envRate;

        return Cache::remember('usd_to_naira_rate', 1800, function () {
            try {
                $res = Http::timeout(8)->get('https://open.er-api.com/v6/latest/USD');
                if ($res->successful()) {
                    $ngn = $res->json('rates.NGN');
                    if ($ngn && (float) $ngn > 0) return (float) $ngn;
                }
            } catch (\Throwable $e) {}
            return 1600.0;
        });
    }

    protected function clientGain(): float
    {
        return (float) (Setting::get('service_gain') ?? env('VIRTUAL_SERVICE_GAIN', 0));
    }

    // Sanitize codes — rejects placeholder strings the provider sometimes returns
    protected function sanitizeCode(?string $val): ?string
    {
        if ($val === null) return null;
        return in_array(strtoupper(trim($val)), [
            'NOT_FOUND', 'NOTFOUND', 'NOT FOUND', 'NULL', 'NONE', '0', ''
        ]) ? null : $val;
    }

    // GET /server7-number
    public function index()
    {
        $userId  = auth()->id();
        $ngnRate = $this->usdToNgnRate();
        $gain    = $this->clientGain();
        $apps    = [];

        if (empty($this->token)) {
            $verifications = collect();
            return view('virtual.server7-number', compact('ngnRate', 'apps', 'verifications'))
                ->with('error', 'API configuration error. Please contact support.');
        }

        try {
            $response = $this->api()->get("{$this->base}/apps/" . self::COUNTRY_ID);
            if ($response->successful()) {
                $data = $response->json();
                $raw  = $data['data'] ?? [];

                // Attach NGN price to each app for the frontend
                $apps = collect($raw)->map(function ($app) use ($ngnRate, $gain) {
                    return [
                        'code'      => $app['code'],
                        'name'      => $app['name'],
                        'usd_price' => (float) $app['price'],
                        'ngn_price' => round(((float) $app['price'] * $ngnRate) + $gain, 2),
                    ];
                })->values()->toArray();
            }
        } catch (\Throwable $e) {}

        $verifications = Verification::where('user_id', $userId)
            ->where('server', 'server7')
            ->latest()
            ->get();

        return view('virtual.server7-number', compact('ngnRate', 'apps', 'verifications'));
    }

    // POST /server7-number/purchase
    public function purchase(Request $request)
    {
        $request->validate([
            'app'      => 'required|string',
            'app_name' => 'nullable|string|max:255',
        ]);

        $user   = auth()->user();
        $userId = $user->id;
        $appCode = (string) $request->app;

        try {
            // 1. Re-fetch live pricing for this service (fresh, server-side)
            $appsResponse = $this->api()->get("{$this->base}/apps/" . self::COUNTRY_ID);

            if (!$appsResponse->successful()) {
                return response()->json(['success' => false, 'error' => 'Could not verify pricing. Please try again.']);
            }

            $appsData = $appsResponse->json();
            $rawApps  = $appsData['data'] ?? [];

            $matchedApp = collect($rawApps)->first(fn($a) => $a['code'] === $appCode);

            if (!$matchedApp) {
                return response()->json(['success' => false, 'error' => 'Service not found or no longer available.']);
            }

            $providerPrice = (float) $matchedApp['price'];
            $ngnRate       = $this->usdToNgnRate();
            $gain          = $this->clientGain();
            $finalNgn      = round(($providerPrice * $ngnRate) + $gain, 2);

            $appName = $request->app_name ?? $matchedApp['name'] ?? $appCode;

            // 2. Wallet pre-check
            $walletBalance = (float) ($user->wallet ?? 0);

            if ($walletBalance < $finalNgn) {
                return response()->json(['success' => false, 'error' =>
                    'Insufficient wallet balance. You need ₦' . number_format($finalNgn, 2) .
                    ' but have ₦' . number_format($walletBalance, 2) . '.'
                ]);
            }

            // 3. Call provider — pass price exactly as returned from /apps
            $apiResponse = $this->api()->post("{$this->base}/purchase", [
                'country'      => self::COUNTRY_ID,
                'app'          => $appCode,
                'price'        => $providerPrice,
                'app_name'     => $appName,
                'country_name' => self::COUNTRY_NAME,
            ]);

            $api = $apiResponse->json();

            if (!$apiResponse->successful()) {
                $providerError = $api['error'] ?? $api['message'] ?? null;
                $machineCode   = $api['code']  ?? '';

                $userMessage = match(true) {
                    $machineCode === 'INSUFFICIENT_BALANCE'       => 'Provider balance too low. Please contact support.',
                    $machineCode === 'INVALID_PRICE'              => 'Pricing has changed. Please refresh and try again.',
                    $machineCode === 'PRICE_VERIFICATION_FAILED'  => 'Pricing verification failed. Please try again shortly.',
                    str_contains(strtolower((string) $providerError), 'no_numbers'),
                    str_contains(strtolower((string) $providerError), 'no numbers')    => 'No numbers available for this service. Please try again later.',
                    str_contains(strtolower((string) $providerError), 'out of stock')  => 'This service is currently out of stock. Please try again later.',
                    $providerError !== null                                             => $providerError,
                    default                                                             => 'Provider request failed. Please try again.',
                };
                return response()->json(['success' => false, 'error' => $userMessage]);
            }

            if (!($api['success'] ?? false)) {
                return response()->json(['success' => false, 'error' => $api['error'] ?? $api['message'] ?? 'Purchase failed.']);
            }

            $activationId = $api['data']['activation_id'] ?? null;
            $phone        = $api['data']['phone_number']  ?? null;

            if (!$activationId || !$phone) {
                return response()->json(['success' => false, 'error' => 'Purchase succeeded but response was incomplete. Please contact support.']);
            }

            // 4. Debit wallet — re-check with lock
            $latestUser = DB::table('users')->where('id', $userId)->first();

            if (!$latestUser || (float) $latestUser->wallet < $finalNgn) {
                return response()->json(['success' => false, 'error' =>
                    'Insufficient wallet balance. You need ₦' . number_format($finalNgn, 2) .
                    ' but have ₦' . number_format((float) ($latestUser->wallet ?? 0), 2) . '.'
                ]);
            }

            DB::table('users')->where('id', $userId)->decrement('wallet', $finalNgn);

            // 5. Create verification record
            Verification::create([
                'user_id'       => $userId,
                'server'        => 'server7',
                'service'       => $appName,
                'service_id'    => $appCode,
                'number'        => (string) $phone,
                'phone'         => (string) $phone,
                'activation_id' => (string) $activationId,
                'api_cost'      => $providerPrice,
                'price'         => $providerPrice,
                'naira_amount'  => $finalNgn,
                'status'        => 'Reserved',
                'code'          => null,
                'country'       => self::COUNTRY_NAME,
                'country_id'    => self::COUNTRY_ID,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Number purchased successfully.',
                'data'    => [
                    'activation_id' => $activationId,
                    'phone_number'  => $phone,
                    'service'       => $appName,
                    'country'       => self::COUNTRY_NAME,
                    'ngn_charged'   => $finalNgn,
                ],
            ], 201);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // GET /server7-number/check/{activationId}/poll
    public function poll(string $activationId)
    {
        if (empty($this->token)) {
            return response()->json(['success' => false, 'status' => 'error', 'code' => null, 'message' => 'API key missing'], 500);
        }

        $verification = Verification::where('activation_id', $activationId)
            ->where('user_id', auth()->id())
            ->where('server', 'server7')
            ->first();

        if (!$verification) {
            return response()->json(['success' => false, 'status' => 'unknown', 'code' => null]);
        }

        $existingCode = $this->sanitizeCode($verification->code);

        // If we already have a real code in DB, never hit the API again
        if ($existingCode !== null) {
            if ($verification->status !== 'Completed') {
                $verification->update(['status' => 'Completed']);
            }
            return response()->json([
                'success' => true,
                'status'  => 'done',
                'code'    => $existingCode,
            ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }

        // No real code yet — ask the provider
        try {
            $apiResponse  = $this->api()->timeout(10)->get("{$this->base}/check/{$activationId}")->json();
            $apiStatus    = $apiResponse['data']['status'] ?? $apiResponse['status'] ?? 'Waiting';
            $incomingCode = $this->sanitizeCode($apiResponse['data']['code'] ?? $apiResponse['code'] ?? null);

            $map = [
                'Completed' => 'done',
                'Waiting'   => 'active',
                'Cancelled' => 'cancelled',
                'Expired'   => 'cancelled',
            ];

            $internalStatus = $map[$apiStatus] ?? 'active';

            if ($incomingCode !== null) {
                $verification->update([
                    'status' => 'Completed',
                    'code'   => $incomingCode,
                ]);
            }

            $finalStatus = $incomingCode !== null ? 'done' : $internalStatus;

            return response()->json([
                'success' => true,
                'status'  => $finalStatus,
                'code'    => $incomingCode,
            ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'status' => 'unknown', 'code' => null]);
        }
    }

    // POST /server7-number/cancel/{activationId}
    public function cancel(string $activationId)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $activationId = trim($activationId);

        try {
            return DB::transaction(function () use ($activationId, $user) {

                $verification = Verification::where('activation_id', $activationId)
                    ->where('user_id', $user->id)
                    ->where('server', 'server7')
                    ->lockForUpdate()
                    ->first();

                if (!$verification) {
                    return response()->json(['success' => false, 'message' => 'Verification not found.'], 404);
                }

                if (strtolower($verification->status) === 'cancelled') {
                    return response()->json(['success' => false, 'message' => 'Already cancelled.'], 400);
                }

                $realCode = $this->sanitizeCode($verification->code);
                if (!empty($realCode)) {
                    return response()->json(['success' => false, 'message' => 'Code already received. Cannot cancel.'], 400);
                }

                // 3-minute lock
                $secondsOld = \Carbon\Carbon::parse($verification->created_at)->diffInSeconds(now());

                if ($secondsOld < 180) {
                    $remaining = 180 - $secondsOld;
                    $mins = floor($remaining / 60);
                    $secs = str_pad($remaining % 60, 2, '0', STR_PAD_LEFT);
                    return response()->json([
                        'success' => false,
                        'message' => "Please wait {$mins}:{$secs} before cancelling.",
                    ], 400);
                }

                // Try provider cancel (non-blocking)
                try {
                    if (!empty($this->token)) {
                        $response = $this->api()->post("{$this->base}/cancel/{$activationId}");
                        \Log::info('Server7 provider cancel attempt', [
                            'activation_id' => $activationId,
                            'response'      => $response->json(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Server7 provider cancel failed but proceeding with refund', [
                        'activation_id' => $activationId,
                        'error'         => $e->getMessage(),
                    ]);
                }

                $refundAmount = (float) ($verification->naira_amount ?? $verification->price ?? 0);

                if ($refundAmount <= 0) {
                    return response()->json(['success' => false, 'message' => 'Invalid refund amount.'], 400);
                }

                $userLocked = User::where('id', $user->id)->lockForUpdate()->first();

                if (!$userLocked) {
                    return response()->json(['success' => false, 'message' => 'User not found.'], 404);
                }

                $userLocked->increment('wallet', $refundAmount);
                $userLocked->refresh();
                $newBalance = (float) $userLocked->wallet;

                $verification->update([
                    'status'       => 'Cancelled',
                    'completed_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Order cancelled and refund processed.',
                    'data'    => [
                        'activation_id'  => $activationId,
                        'refund_amount'  => $refundAmount,
                        'wallet_balance' => $newBalance,
                    ],
                ]);
            });

        } catch (\Throwable $e) {
            \Log::error('Server7 cancel error', [
                'activation_id' => $activationId,
                'error'         => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Error cancelling order. Please try again.'], 500);
        }
    }
}