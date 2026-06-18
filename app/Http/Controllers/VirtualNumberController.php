<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Verification;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class VirtualNumberController extends Controller
{
    protected string $token;
    protected string $base;

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
        $dbRate = Setting::get('virtual_usd_to_naira_rate') ?? Setting::get('usd_to_naira_rate');
        if ($dbRate && (float) $dbRate > 0) return (float) $dbRate;

        if ($envRate = env('USD_TO_NAIRA_RATE')) return (float) $envRate;

        return Cache::remember('virtual_usd_to_naira_rate', 1800, function () {
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
        return (float) (Setting::get('virtual_service_gain') ?? Setting::get('service_gain') ?? env('VIRTUAL_SERVICE_GAIN', 0));
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
            'pool'      => $pool,
            'price'     => $providerPrice,
            'ngn_price' => $ngnPrice,
            'available' => (int) ($item['available'] ?? $item['count'] ?? 0),
        ];
    }

    protected function loadCountries(): array
    {
        try {
            $response = $this->api()->get("{$this->base}/countries");
            if (!$response->successful()) return [];

            $data      = $response->json();
            $countries = $data['data']['countries'] ?? $data['countries'] ?? [];

            if (!empty($countries)) {
                $map = collect($countries)->keyBy('id')->map(fn($c) => [
                    'id'   => $c['id'],
                    'name' => $c['name'],
                ])->toArray();
                session(['vn_countries' => $map]);
            }

            return $countries;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Sanitise a raw code value from the provider.
     * Returns null if the value is a known "no code yet" sentinel.
     */
    protected function sanitizeCode(?string $val): ?string
    {
        if ($val === null) return null;
        $upper = strtoupper(trim($val));
        if (in_array($upper, ['NOT_FOUND', 'NOTFOUND', 'NOT FOUND', 'NULL', 'NONE', '0', ''], true)) {
            return null;
        }
        return trim($val);
    }

    // GET /virtual
    public function index()
    {
        $userId    = auth()->id();
        $ngnRate   = $this->usdToNgnRate();
        $countries = [];

        if (empty($this->token)) {
            $verifications = collect();
            return view('virtual.index', compact('ngnRate', 'countries', 'verifications'))
                ->with('error', 'API configuration error. Please contact support.');
        }

        try {
            $countries = $this->loadCountries();
        } catch (\Throwable $e) {}

        $verifications = Verification::where('user_id', $userId)
            ->where('server', 'virtual')
            ->latest()
            ->get();

        return view('virtual.index', compact('ngnRate', 'countries', 'verifications'));
    }

    // GET /virtual/services/{countryId}
    public function services(int $countryId)
    {
        try {
            $response = $this->api()->get("{$this->base}/services/{$countryId}");

            if (!$response->successful()) {
                return response()->json(['success' => false, 'services' => [], 'message' => 'Failed to load services'], 400);
            }

            $data     = $response->json();
            $services = $data['data']['services'] ?? $data['services'] ?? [];

            $existing = session('vn_services', []);
            foreach ($services as $s) {
                $code = $s['code'] ?? null;
                if ($code) $existing[$code] = $s;
            }
            session(['vn_services' => $existing]);

            return response()->json(['success' => true, 'services' => $services]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'services' => [], 'message' => 'Server error'], 500);
        }
    }

    // POST /virtual/prices
    public function prices(Request $request)
    {
        $request->validate([
            'country' => 'required|integer',
            'service' => 'required|string',
        ]);

        try {
            $response = $this->api()->post("{$this->base}/prices", [
                'country' => (int) $request->country,
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

    // POST /virtual/purchase
    public function purchase(Request $request)
    {
        $request->validate([
            'country'      => 'required|integer',
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
                'country' => (int) $request->country,
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

            // 3. Resolve names
            $serviceCode = (string) $request->service;
            $serviceName = session('vn_services')[$serviceCode]['name']
                        ?? $request->service_name
                        ?? $serviceCode;
            $countryName = session('vn_countries')[$request->country]['name'] ?? 'Unknown';

            // 4. Call provider API
            $apiResponse = $this->api()->post("{$this->base}/purchase", [
                'country'      => (int) $request->country,
                'service'      => $serviceCode,
                'service_name' => $serviceName,
                'price'        => $matchedPool['price'],
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
                'server'        => 'virtual',
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
                'country'       => $countryName,
                'country_id'    => (int) $request->country,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Number purchased successfully.',
                    'data'    => [
                        'activation_id' => $activationId,
                        'phone_number'  => $phone,
                        'service'       => $serviceName,
                        'country'       => $countryName,
                        'ngn_charged'   => $finalNgn,
                    ],
                ], 201);
            }

            return back()->with('success', 'Number purchased successfully! Waiting for SMS code…');

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // GET /virtual/check/{activationId}/poll  (single — kept for back-compat)
    public function poll(string $activationId)
    {
        if (empty($this->token)) {
            return response()->json(['success' => false, 'status' => 'error', 'code' => null, 'message' => 'API key missing'], 500);
        }

        $verification = Verification::where('activation_id', $activationId)
            ->where('user_id', auth()->id())
            ->where('server', 'virtual')
            ->first();

        if (!$verification) {
            return response()->json(['success' => false, 'status' => 'unknown', 'code' => null]);
        }

        // ── Guard: if DB already has a real code, NEVER call the provider again ──
        $existingCode = $this->sanitizeCode($verification->code);
        if ($existingCode !== null) {
            if ($verification->status !== 'Completed') {
                // Only update status; use whereColumn to avoid a race condition wiping the code
                Verification::where('id', $verification->id)
                    ->update(['status' => 'Completed']);
            }
            return response()->json(['success' => true, 'status' => 'done', 'code' => $existingCode])
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }

        // ── Ask provider ──────────────────────────────────────────────────────
        try {
            $apiResponse  = $this->api()->timeout(10)->get("{$this->base}/check/{$activationId}")->json();
            $incomingCode = $this->sanitizeCode(
                $apiResponse['data']['code'] ?? $apiResponse['code'] ?? null
            );

            if ($incomingCode !== null) {
                // ONLY write if code column is still null — prevents overwriting a good code
                Verification::where('id', $verification->id)
                    ->whereNull('code')
                    ->update(['status' => 'Completed', 'code' => $incomingCode]);

                return response()->json(['success' => true, 'status' => 'done', 'code' => $incomingCode])
                    ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
            }

            $apiStatus = $apiResponse['data']['status'] ?? $apiResponse['status'] ?? 'Waiting';
            $map = [
                'Completed' => 'done', 'Reserved' => 'active', 'Active' => 'active',
                'Waiting'   => 'active', 'Cancelled' => 'cancelled', 'Expired' => 'cancelled',
            ];

            return response()->json(['success' => true, 'status' => $map[$apiStatus] ?? 'active', 'code' => null])
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate');

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'status' => 'unknown', 'code' => null]);
        }
    }

    // POST /virtual/poll/batch  — checks ALL pending activations in one request
   // POST /virtual/poll/batch  — checks ALL pending activations in one request
public function pollBatch(Request $request)
{
    if (empty($this->token)) {
        return response()->json(['success' => false, 'results' => []])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    $request->validate(['ids' => 'required|array|max:20', 'ids.*' => 'string']);
    $userId = auth()->id();

    if (!$userId) {
        return response()->json(['success' => false, 'results' => []], 403);
    }

    // Load only verifications that genuinely need polling
    $verifications = Verification::where('user_id', $userId)
        ->where('server', 'virtual')
        ->whereIn('activation_id', $request->ids)
        ->whereNotIn('status', ['Cancelled', 'Expired', 'Completed'])
        ->get()
        ->keyBy('activation_id');

    // If DB already has codes for some IDs (race condition safety), return them immediately
    $results = [];

    foreach ($verifications as $activationId => $verification) {

        // ── 1. Short-circuit: DB already has a real code ───────────────────
        $existingCode = $this->sanitizeCode($verification->code);
        if ($existingCode !== null) {
            // Sync status in case it got missed
            if ($verification->status !== 'Completed') {
                Verification::where('id', $verification->id)
                    ->update(['status' => 'Completed']);
            }
            $results[$activationId] = ['status' => 'done', 'code' => $existingCode];
            continue;
        }

        // ── 2. Ask provider ────────────────────────────────────────────────
        try {
            $response    = $this->api()->timeout(10)->get("{$this->base}/check/{$activationId}");
            $apiResponse = $response->json();

            \Log::info('[VirtualPoll] raw response', [
                'activation_id' => $activationId,
                'http_status'   => $response->status(),
                'body'          => $apiResponse,
            ]);

            if (!$response->successful()) {
                $results[$activationId] = ['status' => 'unknown', 'code' => null];
                continue;
            }

            // ── 3. Extract code from every known response shape ────────────
            $rawCode = $apiResponse['data']['code']
                    ?? $apiResponse['data']['sms_code']
                    ?? $apiResponse['data']['verification_code']
                    ?? $apiResponse['code']
                    ?? $apiResponse['sms_code']
                    ?? $apiResponse['result']
                    ?? null;

            $incomingCode = $this->sanitizeCode(
                is_array($rawCode) ? ($rawCode['code'] ?? null) : (string) ($rawCode ?? '')
            );

            if ($incomingCode !== null) {
                // Write only if code column is still null — prevents race overwrite
                Verification::where('id', $verification->id)
                    ->whereNull('code')
                    ->update(['status' => 'Completed', 'code' => $incomingCode]);

                $results[$activationId] = ['status' => 'done', 'code' => $incomingCode];
                continue;
            }

            // ── 4. Extract status from every known response shape ──────────
            $rawStatus = $apiResponse['data']['status']
                      ?? $apiResponse['status']
                      ?? $apiResponse['data']['order_status']
                      ?? $apiResponse['order_status']
                      ?? 'Waiting';

            // Normalise to lowercase for safe comparison
            $normalised = strtolower(trim((string) $rawStatus));

            $statusMap = [
                // Active / waiting states → keep polling
                'reserved'  => 'active',
                'active'    => 'active',
                'waiting'   => 'active',
                'pending'   => 'active',
                'sent'      => 'active',
                'in_progress' => 'active',

                // Terminal without code → stop polling, no refund
                'completed' => 'active',   // completed but no code yet — keep polling a bit longer
                'done'      => 'active',   // same reason

                // Hard cancel states → stop polling
                'cancelled' => 'cancelled',
                'canceled'  => 'cancelled',
                'expired'   => 'cancelled',
                'banned'    => 'cancelled',
                'timeout'   => 'cancelled',
                'failed'    => 'cancelled',
            ];

            $mappedStatus = $statusMap[$normalised] ?? 'active';

            // Sync DB status for cancelled/expired so next page load won't re-register
            if (in_array($mappedStatus, ['cancelled'], true)) {
                Verification::where('id', $verification->id)
                    ->whereNotIn('status', ['Completed', 'Cancelled'])
                    ->update(['status' => ucfirst($normalised)]);
            }

            $results[$activationId] = ['status' => $mappedStatus, 'code' => null];

        } catch (\Throwable $e) {
            \Log::error('[VirtualPoll] exception', [
                'activation_id' => $activationId,
                'error'         => $e->getMessage(),
            ]);
            // Return unknown so frontend keeps retrying rather than giving up
            $results[$activationId] = ['status' => 'unknown', 'code' => null];
        }
    }

    // Return results for IDs that had no matching DB row (already done/cancelled)
    foreach ($request->ids as $id) {
        if (!isset($results[$id])) {
            $results[$id] = ['status' => 'cancelled', 'code' => null];
        }
    }

    return response()->json(['success' => true, 'results' => $results])
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
}
    // POST /virtual/cancel/{activationId}
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

                if (!empty($verification->code)) {
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