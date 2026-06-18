<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Verification;
use App\Models\Setting;
use App\Models\User;
use App\Services\SMSActivate;

class Server5VerificationController extends Controller
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.sms_activate.api_key');
    }

    public function index()
    {
        $verifications = Verification::where('user_id', Auth::id())
            ->where('server', 'server5')
            ->whereDate('created_at', now())
            ->latest()
            ->get();

        return view('verifications.server5.index', compact('verifications'));
    }

    public function getCountries()
    {
        // Return only USA (ID: 187)
        $res = Http::get("https://api.sms-activate.ae/stubs/handler_api.php", [
            'api_key' => $this->apiKey,
            'action' => 'getCountries',
        ]);

        $data = $res->json();

        return response()->json([
            '187' => $data['187'] ?? ['eng' => 'United States']
        ]);
    }

    public function getServices()
    {
        $res = Http::get("https://api.sms-activate.ae/stubs/handler_api.php", [
            'api_key' => $this->apiKey,
            'action' => 'getServicesList',
        ]);

        return response()->json($res->json());
    }

    public function getLivePrice(Request $request)
    {
        $country = $request->country;
        $service = $request->service;

        if (!$country || !$service) {
            return response()->json(['error' => 'Missing service or country'], 400);
        }

        try {
            $sdk = new SMSActivate($this->apiKey);
            $prices = $sdk->getPrices($country, $service);

            $countryStr = (string) $country;
            $serviceStr = (string) $service;

            if (isset($prices[$countryStr][$serviceStr])) {
                $usd = $prices[$countryStr][$serviceStr]['cost'];
                $count = $prices[$countryStr][$serviceStr]['count'] ?? 0;
                $rate = Setting::get('usd_to_ngn', 1500);
                $markupPercent = 50;
                $naira = round($usd * (1 + $markupPercent / 100) * $rate, 2);

                return response()->json([
                    'price' => $naira,
                    'usd' => $usd,
                    'count' => $count,
                    'country_id' => $country
                ]);
            }

            return response()->json(['error' => 'Price not available'], 404);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Failed to fetch price',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function purchase(Request $request)
    {
        $user = Auth::user();
        $usdPrice = is_numeric($request->locked_price) ? $request->locked_price : 0;
        $markup = 50;
        $usdWithMarkup = $usdPrice * (1 + $markup / 100);
        $rate = Setting::get('usd_to_ngn', 1500);
        $nairaAmount = round($usdWithMarkup * $rate, 2);

        if ($user->wallet < $nairaAmount) {
            return back()->with('error', 'Insufficient wallet balance.');
        }

        $sdk = new SMSActivate($this->apiKey);
        $numberData = $sdk->getNumber($request->service, $request->resolved_country);

        $activationId = $numberData['id'] ?? null;
        $number = $numberData['number'] ?? null;

        if ($activationId && $number) {
            $user->wallet -= $nairaAmount;
            $user->save();

            Verification::create([
                'user_id' => $user->id,
                'server' => 'server5',
                'activation_id' => $activationId,
                'number' => $number,
                'status' => 'Waiting',
                'service' => $request->service,
                'name' => ucfirst($request->service),
                'country_id' => $request->resolved_country,
                'price' => $usdPrice,
                'naira_amount' => $nairaAmount,
                'markup' => $markup,
            ]);

            return back()
                ->with('success', "Number acquired: $number")
                ->with('charged_usd', $usdPrice)
                ->with('charged_ngn', $nairaAmount);
        }

        return back()->with('error', 'Purchase failed: ' . json_encode($numberData));
    }

    public function pollCode($id)
    {
        $v = Verification::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('server', 'server5')
            ->first();

        if (!$v) return response()->json(['error' => 'Not found'], 404);

        $res = Http::get("https://api.sms-activate.ae/stubs/handler_api.php", [
            'api_key' => $this->apiKey,
            'action' => 'getStatus',
            'id' => $v->activation_id,
        ]);

        $body = $res->body();

        if (str_contains($body, 'STATUS_OK')) {
            $code = explode(':', $body)[1] ?? null;
            $v->status = 'Completed';
            $v->code = $code;
            $v->save();

            return response()->json(['status' => 'Completed', 'code' => $code]);
        }

        return response()->json(['status' => $v->status, 'code' => $v->code]);
    }

    public function cancel($id)
    {
        $v = Verification::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('server', 'server5')
            ->first();

        if (!$v || $v->status === 'Completed') {
            return back()->with('error', 'Cancellation not possible.');
        }

        // Check if code already delivered
        $statusRes = Http::get("https://api.sms-activate.ae/stubs/handler_api.php", [
            'api_key' => $this->apiKey,
            'action' => 'getStatus',
            'id' => $v->activation_id,
        ]);

        $status = $statusRes->body();

        if (str_starts_with($status, 'STATUS_OK')) {
            $code = explode(':', $status)[1] ?? null;
            $v->status = 'Completed';
            $v->code = $code;
            $v->save();

            return back()->with('error', 'Code already delivered. Order completed.');
        }

        // Proceed with cancel
        $cancelRes = Http::get("https://api.sms-activate.ae/stubs/handler_api.php", [
            'api_key' => $this->apiKey,
            'action' => 'setStatus',
            'status' => 8,
            'id' => $v->activation_id,
        ]);

        if (str_contains($cancelRes->body(), 'ACCESS_CANCEL')) {
            $user = Auth::user();
            $user->wallet += $v->naira_amount;
            $user->save();

            $v->status = 'Canceled';
            $v->save();

            return back()->with('success', 'Order canceled and refunded.');
        }

        return back()->with('error', 'Failed to cancel the order from provider.');
    }
}
