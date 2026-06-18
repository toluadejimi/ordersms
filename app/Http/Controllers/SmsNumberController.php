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

class SmsNumberController extends Controller
{
    protected string $token;
    protected string $base;
    protected const COUNTRY_ID   = 36;
    protected const COUNTRY_NAME = 'United Kingdom';

    public function __construct()
    {
        $this->token = Setting::get('virtual_api_key') ?? env('VIRTUAL_API_KEY', '');
        $this->base  = 'https://daisysim.com/api/v1/virtual';
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

    protected function extractTiers(array $data): array
    {
        $candidates = [
            $data['data']['tiers']              ?? null,
            $data['tiers']                      ?? null,
            $data['available_pools']            ?? null,
            $data['pools']                      ?? null,
            $data['pricing']['available_pools'] ?? null,
            $data['pricing']['tiers']           ?? null,
            $data['data']['available_pools']    ?? null,
            $data['data']['pools']              ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_array($candidate) && count($candidate) > 0) return $candidate;
        }

        if (isset($data[0]) && is_array($data[0])) return $data;

        return [];
    }

    protected function normaliseTier(array $item, int $index, float $gain): ?array
    {
        $providerPrice = null;
        if (isset($item['price']))          $providerPrice = (float) $item['price'];
        elseif (isset($item['api_price']))  $providerPrice = (float) $item['api_price'];
        elseif (isset($item['amount']))     $providerPrice = (float) $item['amount'];
        elseif (isset($item['cost']))       $providerPrice = (float) $item['cost'];

        if ($providerPrice === null || $providerPrice <= 0) return null;

        $pool = null;
        if (isset($item['pool']))           $pool = (int) $item['pool'];
        elseif (isset($item['tier']))       $pool = (int) $item['tier'];
        elseif (isset($item['id']))         $pool = (int) $item['id'];
        else                                $pool = $index + 1;

        $ngnPrice = round(($providerPrice * $this->usdToNgnRate()) + $gain, 2);

        return [
            'pool'         => $pool,
            'price'        => $providerPrice,   // raw provider price — sent to /purchase
            'ngn_price'    => $ngnPrice,
            'available'    => (int) ($item['available'] ?? $item['count'] ?? 0),
            'discount_pct' => (int) ($item['discount_pct'] ?? 0),
        ];
    }

    // Sanitize codes — rejects placeholder strings the provider sometimes returns
    protected function sanitizeCode(?string $val): ?string
    {
        if ($val === null) return null;
        return in_array(strtoupper(trim($val)), [
            'NOT_FOUND', 'NOTFOUND', 'NOT FOUND', 'NULL', 'NONE', '0', ''
        ]) ? null : $val;
    }

    // GET /sms-number
    public function index()
    {
        $userId   = auth()->id();
        $ngnRate  = $this->usdToNgnRate();
        $services = [];

        if (empty($this->token)) {
            $verifications = collect();
            return view('virtual.sms-number', compact('ngnRate', 'services', 'verifications'))
                ->with('error', 'API configuration error. Please contact support.');
        }

        try {
            $response = $this->api()->get("{$this->base}/services/" . self::COUNTRY_ID);
            if ($response->successful()) {
                $data     = $response->json();
                $services = $data['data']['services'] ?? $data['services'] ?? [];

                $existing = session('vn_services', []);
                foreach ($services as $s) {
                    $code = $s['code'] ?? null;
                    if ($code) $existing[$code] = $s;
                }
                session(['vn_services' => $existing]);
            }
        } catch (\Throwable $e) {}

        $verifications = Verification::where('user_id', $userId)
            ->where('server', 'sms_number')
            ->latest()
            ->get();

        return view('virtual.sms-number', compact('ngnRate', 'services', 'verifications'));
    }

    // POST /sms-number/prices
    public function prices(Request $request)
    {
        $request->validate([
            'service' => 'required|string',
        ]);

        try {
            $response = $this->api()->post("{$this->base}/prices", [
                'country' => self::COUNTRY_ID,
                'service' => (string) $request->service,
            ]);

            if (!$response->successful()) {
                return response()->json(['success' => false, 'message' => 'Pricing unavailable'], 400);
            }

            $data = $response->json();

            if (!($data['success'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => $data['error'] ?? $data['message'] ?? 'No pools available',
                ]);
            }

            $rawTiers = $this->extractTiers($data);

            if (empty($rawTiers)) {
                return response()->json([
                    'success'         => false,
                    'available_pools' => [],
                    'message'         => 'No numbers available for this selection',
                ]);
            }

            $gain  = $this->clientGain();
            $pools = collect($rawTiers)
                ->map(fn($item, $index) => $this->normaliseTier((array) $item, $index, $gain))
                ->filter()
                ->values();

            if ($pools->isEmpty()) {
                return response()->json([
                    'success'         => false,
                    'available_pools' => [],
                    'message'         => 'No numbers available for this selection',
                ]);
            }

            return response()->json(['success' => true, 'available_pools' => $pools]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error fetching pricing'], 500);
        }
    }

    // POST /sms-number/purchase
    public function purchase(Request $request)
    {
        $request->validate([
            'service'      => 'required|string',
            'service_name' => 'nullable|string|max:255',
            'pool'         => 'required|integer',
        ]);

        $user         = auth()->user();
        $userId       = $user->id;
        $selectedPool = (int) $request->pool;

        try {
            // 1. Re-verify pricing server-side (fresh, no cache)
            $pricingResponse = $this->api()->post("{$this->base}/prices", [
                'country' => self::COUNTRY_ID,
                'service' => (string) $request->service,
            ]);

            if (!$pricingResponse->successful()) {
                return response()->json(['success' => false, 'error' => 'Could not verify pricing. Please try again.']);
            }

            $pricingData = $pricingResponse->json();

            if (!($pricingData['success'] ?? false)) {
                return response()->json(['success' => false, 'error' => 'Pricing verification failed.']);
            }

            $rawTiers = $this->extractTiers($pricingData);
            $gain     = $this->clientGain();

            $normalisedPools = collect($rawTiers)
                ->map(fn($item, $index) => $this->normaliseTier((array) $item, $index, $gain))
                ->filter();

            $matchedPool = $normalisedPools->first(fn($p) => (int) $p['pool'] === $selectedPool);

            if (!$matchedPool) {
                return response()->json(['success' => false, 'error' => 'Selected route is no longer available. Please refresh and try again.']);
            }

            $finalNgn = round((float) $matchedPool['ngn_price'], 2);

            // 2. Wallet pre-check
            $walletBalance = (float) ($user->wallet ?? 0);

            if ($walletBalance < $finalNgn) {
                return response()->json(['success' => false, 'error' =>
                    'Insufficient wallet balance. You need ₦' . number_format($finalNgn, 2) .
                    ' but have ₦' . number_format($walletBalance, 2) . '.'
                ]);
            }

            // 3. Resolve service name
            $serviceCode = (string) $request->service;
            $serviceName = session('vn_services')[$serviceCode]['name']
                        ?? $request->service_name
                        ?? $serviceCode;

            // 4. Call provider — send price (raw provider price)
            $apiResponse = $this->api()->post("{$this->base}/purchase", [
                'country'      => self::COUNTRY_ID,
                'service'      => $serviceCode,
                'service_name' => $serviceName,
                'price'        => $matchedPool['price'],  // ✅ correct key, raw provider price
            ]);

            $api = $apiResponse->json();

            if (!$apiResponse->successful()) {
                $providerError = $api['error'] ?? $api['message'] ?? null;
                $userMessage = match(true) {
                    str_contains(strtolower((string) $providerError), 'no_numbers'),
                    str_contains(strtolower((string) $providerError), 'no numbers')    => 'No numbers available for this selection. Please try a different route or service.',
                    str_contains(strtolower((string) $providerError), 'out of stock')  => 'This number pool is currently out of stock. Please try again later.',
                    str_contains(strtolower((string) $providerError), 'not available') => 'This service is not available right now. Please try another option.',
                    $providerError !== null                                             => $providerError,
                    default                                                             => 'Provider request failed. Please try again.',
                };
                return response()->json(['success' => false, 'error' => $userMessage]);
            }

            if (!($api['success'] ?? false)) {
                return response()->json(['success' => false, 'error' => $api['error'] ?? $api['message'] ?? 'Purchase failed.']);
            }

            $activationId = $api['data']['activation_id'] ?? null;
            $phone        = $api['data']['phone_number'] ?? $api['data']['number'] ?? null;

            if (!$activationId || !$phone) {
                return response()->json(['success' => false, 'error' => 'Purchase succeeded but response was incomplete. Please contact support.']);
            }

            // 5. Debit wallet — re-check balance with lock
            $latestUser = DB::table('users')->where('id', $userId)->first();

            if (!$latestUser || (float) $latestUser->wallet < $finalNgn) {
                return response()->json(['success' => false, 'error' =>
                    'Insufficient wallet balance. You need ₦' . number_format($finalNgn, 2) .
                    ' but have ₦' . number_format((float) ($latestUser->wallet ?? 0), 2) . '.'
                ]);
            }

            DB::table('users')->where('id', $userId)->decrement('wallet', $finalNgn);

            // 6. Create verification record
            Verification::create([
                'user_id'       => $userId,
                'server'        => 'sms_number',
                'service'       => $serviceName,
                'service_id'    => $serviceCode,
                'number'        => (string) $phone,
                'phone'         => (string) $phone,
                'activation_id' => (string) $activationId,
                'api_cost'      => $matchedPool['price'],
                'price'         => $matchedPool['price'],
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
                    'service'       => $serviceName,
                    'country'       => self::COUNTRY_NAME,
                    'ngn_charged'   => $finalNgn,
                ],
            ], 201);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // GET /sms-number/check/{activationId}/poll
    public function poll(string $activationId)
    {
        if (empty($this->token)) {
            return response()->json(['success' => false, 'status' => 'error', 'code' => null, 'message' => 'API key missing'], 500);
        }

        $verification = Verification::where('activation_id', $activationId)
            ->where('user_id', auth()->id())
            ->where('server', 'sms_number')
            ->first();

        if (!$verification) {
            return response()->json(['success' => false, 'status' => 'unknown', 'code' => null]);
        }

        $existingCode = $this->sanitizeCode($verification->code);

        // If we already have a real code in DB, NEVER hit the API again
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
            $apiResponse = $this->api()->timeout(10)->get("{$this->base}/check/{$activationId}")->json();

            $map = [
                'Completed' => 'done',
                'Reserved'  => 'active',
                'Active'    => 'active',
                'Waiting'   => 'active',
                'Cancelled' => 'cancelled',
                'Expired'   => 'cancelled',
            ];

            $apiStatus      = $apiResponse['data']['status'] ?? $apiResponse['status'] ?? 'Waiting';
            $internalStatus = $map[$apiStatus] ?? 'active';
            $incomingCode   = $this->sanitizeCode($apiResponse['data']['code'] ?? $apiResponse['code'] ?? null);

            // Only write to DB if incoming is a real code
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

    // POST /sms-number/cancel/{activationId}
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
                    ->lockForUpdate()
                    ->first();

                if (!$verification) {
                    return response()->json(['success' => false, 'message' => 'Verification not found.'], 404);
                }

                if (strtolower($verification->status) === 'cancelled') {
                    return response()->json(['success' => false, 'message' => 'Already cancelled.'], 400);
                }

                // Sanitize before checking — don't block cancel if DB has NOT_FOUND
                $realCode = $this->sanitizeCode($verification->code);
                if (!empty($realCode)) {
                    return response()->json(['success' => false, 'message' => 'Code already received. Cannot cancel.'], 400);
                }

                // 3-minute time lock
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
                        \Log::info('Provider cancel attempt', [
                            'activation_id' => $activationId,
                            'response'      => $response->json(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Provider cancel failed but proceeding with refund', [
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
            \Log::error('Cancel error', [
                'activation_id' => $activationId,
                'error'         => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Error cancelling order. Please try again.'], 500);
        }
    }
}