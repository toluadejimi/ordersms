<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Verification;
use App\Models\Setting;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Server4VerificationController extends Controller
{
    protected $token;

    public function __construct()
    {
        $this->token = Setting::get('smsman_api_token') ?? config('services.smsman.token');
    }

    public function index()
    {
        $countries = [];
        $services = [];

        try {
            // Fetch countries
            $countriesResponse = Http::get("https://api.sms-man.com/control/countries", [
                'token' => $this->token
            ]);
            if ($countriesResponse->successful()) {
                $rawCountries = $countriesResponse->json();
                foreach ($rawCountries as $item) {
                    if (isset($item['id']) && isset($item['title'])) {
                        $countries[] = [
                            'id' => $item['id'],
                            'name' => $item['title'],
                        ];
                    }
                }
            }

            // Fetch services
            $servicesResponse = Http::get("https://api.sms-man.com/control/applications", [
                'token' => $this->token
            ]);
            if ($servicesResponse->successful()) {
                $rawServices = $servicesResponse->json();
                foreach ($rawServices as $item) {
                    if (isset($item['id'])) {
                        $services[] = [
                            'id' => $item['id'],
                            'name' => $item['title'] ?? ($item['name'] ?? 'Unnamed Service'),
                        ];
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('SMS-Man Server4 index fetch error', ['error' => $e->getMessage()]);
        }

        return view('verifications.server4', compact('countries', 'services'));
    }
    
    
    public function getPrice(Request $request)
{
    $request->validate([
        'country' => 'required|numeric',
        'service' => 'required|numeric',
    ]);

    $countryId = $request->country;
    $serviceId = $request->service;

    $response = Http::get("https://api.sms-man.com/control/get-prices", [
        'token' => $this->token,
        'country_id' => $countryId
    ]);

    $json = $response->json();
    $usd = null;

    foreach ($json as $item) {
        if (
            isset($item['application_id'], $item['country_id'], $item['cost']) &&
            (int)$item['application_id'] === (int)$serviceId &&
            (int)$item['country_id'] === (int)$countryId
        ) {
            $baseUsd = floatval($item['cost']) / 100;
$markupPercent = floatval(Setting::get('smsman_markup_percent', 50)); // Default to 50% if not set
$usd = $baseUsd + ($baseUsd * $markupPercent / 100);

        }
    }
    

    if (is_null($usd)) {
        return response()->json([
            'success' => false,
            'message' => '❌ Price not available for this service and country.',
        ]);
    }

    $rate = floatval(Setting::get('smsman_usd_to_naira_rate'));
    $gain = floatval(Setting::get('smsman_service_gain'));

    if ($rate <= 0 || is_null($rate)) {
        return response()->json([
            'success' => false,
            'message' => '❌ USD to Naira rate is not configured. Please set it in admin settings.',
        ]);
    }

    $priceNaira = round(($usd * $rate) + $gain, 2);

    return response()->json([
        'success' => true,
        'price_naira' => $priceNaira,
    ]);
}


public function purchase(Request $request)
{
    $request->validate([
        'country' => 'required|numeric',
        'service' => 'required|numeric',
    ]);

    $user = Auth::user();
    $countryId = $request->country;
    $applicationId = $request->service;

    $response = Http::get("https://api.sms-man.com/control/get-number", [
        'token' => $this->token,
        'country_id' => $countryId,
        'application_id' => $applicationId
    ]);

    $json = $response->json();

    if (!isset($json['number']) || !isset($json['request_id'])) {
        return back()->with('error', $json['message'] ?? '❌  Number not available try again later');
    }

    // Step 2: Try to get real USD price
    $usd = null;

    if (isset($json['price'])) {
        $usd = floatval($json['price']);
    } else {
        $priceResponse = Http::get("https://api.sms-man.com/control/get-prices", [
            'token' => $this->token,
            'country_id' => $countryId
        ]);

        $priceJson = $priceResponse->json();

        foreach ($priceJson as $item) {
            if (
                isset($item['application_id'], $item['country_id'], $item['cost']) &&
                (int)$item['application_id'] === (int)$applicationId &&
                (int)$item['country_id'] === (int)$countryId
            ) {
                $baseUsd = floatval($item['cost']) / 100;
                $markupPercent = floatval(Setting::get('smsman_markup_percent', 50));
                $usd = $baseUsd + ($baseUsd * $markupPercent / 100);
            }
        }

        if (is_null($usd)) {
            return back()->with('error', '❌ Price not available for this service and country.');
        }
    }

    $rate = floatval(Setting::get('smsman_usd_to_naira_rate'));
    $gain = floatval(Setting::get('smsman_service_gain'));

    if ($rate <= 0 || is_null($rate)) {
        return back()->with('error', '❌ USD to Naira rate is not configured. Please set it in admin settings.');
    }

    $nairaAmount = round(($usd * $rate) + $gain, 2);

    if ($user->wallet < $nairaAmount) {
        return back()->with('error', '❌ Insufficient wallet balance. Required: ₦' . number_format($nairaAmount, 2));
    }

    $user->wallet -= $nairaAmount;
    $user->save();

    $number = $json['number'];
    $activationId = $json['request_id'];

    Verification::create([
        'user_id'       => $user->id,
        'name'          => 'Server 4',
        'server'        => 4,
        'number'        => $number,
        'activation_id' => $activationId,
        'price'         => $usd,
        'naira_amount'  => $nairaAmount,
        'status'        => 'Reserved',
        'service'       => $applicationId,
        'country_id'    => $countryId,
    ]);

    // ✅ Telegram Notification
    try {
        $message = "📲 <b>Server 4 Purchase (SMS-Man)</b>\n"
                 . "👤 User: <b>{$user->email}</b>\n"
                 . "🌍 Country ID: <b>{$countryId}</b>\n"
                 . "🧾 Service ID: <b>{$applicationId}</b>\n"
                 . "📞 Number: <code>{$number}</code>\n"
                 . "💵 Price: ₦" . number_format($nairaAmount, 2) . "\n"
                 . "🔢 Activation ID: <code>{$activationId}</code>\n"
                 . "💼 Wallet Balance: ₦" . number_format($user->fresh()->wallet, 2) . "\n"
                 . "🕐 " . now()->format('Y-m-d H:i:s');

        (new TelegramService())->sendMessage($message);
    } catch (\Throwable $e) {
        \Log::error('Telegram error (smsman purchase): ' . $e->getMessage());
    }

    return back()->with('success', '✅ Number purchased successfully. ₦' . number_format($nairaAmount, 2) . ' deducted.');
}

  public function check($id)
{
    $verification = Verification::findOrFail($id);

    $response = Http::get("https://api.sms-man.com/control/get-sms", [
        'token' => $this->token,
        'request_id' => $verification->activation_id,
    ]);

    $json = $response->json();

    if (isset($json['sms_code'])) {
        $verification->update([
            'code' => $json['sms_code'],
            'status' => 'Received',
        ]);

        // ✅ Telegram Notification
        try {
            $user = $verification->user;
            $message = "📬 <b>Server 4 Code Received</b>\n"
                     . "👤 User: <b>{$user->email}</b>\n"
                     . "🧾 Service ID: <b>{$verification->service}</b>\n"
                     . "🌍 Country ID: <b>{$verification->country_id}</b>\n"
                     . "📞 Number: <code>{$verification->number}</code>\n"
                     . "💼 Wallet Balance: ₦" . number_format($user->wallet, 2) . "\n"
                     . "🔐 Code: <b>{$json['sms_code']}</b>\n"
                     . "🔢 Activation ID: <code>{$verification->activation_id}</code>\n"
                     . "🕐 " . now()->format('Y-m-d H:i:s');

            (new TelegramService())->sendMessage($message);
        } catch (\Throwable $e) {
            \Log::error('Telegram error (Server 4 check): ' . $e->getMessage());
        }
    }

    return response()->json([
        'status' => $verification->status,
        'code' => $verification->code ?? null,
    ]);
}
public function cancel($id)
{
    $verification = Verification::where('id', $id)
        ->where('user_id', Auth::id())
        ->firstOrFail();

    // 🛑 Prevent cancel if code already received
    if (!empty($verification->code)) {
        return back()->with('error', '✅ Code already received. This order is completed and cannot be cancelled.');
    }

    // Cancel via SMS-Man API
    Http::get("https://api.sms-man.com/control/set-status", [
        'token' => $this->token,
        'request_id' => $verification->activation_id,
        'status' => 'cancel',
    ]);

    $user = Auth::user();
    $refundAmount = $verification->naira_amount;

    $user->wallet += $refundAmount;
    $user->save();

    $walletBalance = $user->wallet;

    $verification->delete();

    // ✅ Telegram Notification
    try {
        $message = "❌ <b>Server 4 Cancelled</b>\n"
                 . "👤 User: <b>{$user->email}</b>\n"
                 . "🧾 Service ID: <b>{$verification->service}</b>\n"
                 . "🌍 Country ID: <b>{$verification->country_id}</b>\n"
                 . "📞 Number: <code>{$verification->number}</code>\n"
                 . "🔢 Activation ID: <code>{$verification->activation_id}</code>\n"
                 . "💰 Refunded: ₦" . number_format($refundAmount, 2) . "\n"
                 . "💼 Wallet Balance: ₦" . number_format($walletBalance, 2) . "\n"
                 . "🕐 " . now()->format('Y-m-d H:i:s');

        (new TelegramService())->sendMessage($message);
    } catch (\Throwable $e) {
        \Log::error('Telegram error (Server 4 cancel): ' . $e->getMessage());
    }

    return back()->with('success', '❌ Cancelled and refunded ₦' . number_format($refundAmount, 2));
}
}