<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;


class WalletController extends Controller
{
    public function showFundForm()
    {
        return view('wallet.fund');
    }

    public function manualFund(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:100']);

        // Log manual funding (for admin approval)
        Transaction::create([
            'user_id' => auth()->id(),
            'type' => 'manual',
            'amount' => $request->amount,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Manual funding request submitted. Awaiting approval.');
    }
 public function savePhone(Request $request)
{
    $request->validate([
        'phone' => 'required|string|max:20|unique:users,phone,' . Auth::id(),
    ], [
        'phone.unique' => '❌ This phone number has already been taken.',
    ]);

    $user = Auth::user();
    $user->phone = $request->phone;
    $user->save();

    return redirect()->back()->with('success', '📱 Phone number saved. You can now generate your virtual account.');
}

    /**
     * Generate virtual account using PaymentPoint API.
     */
    public function generateVirtualAccount()
    {
        $user = Auth::user();

        if (empty($user->phone)) {
            return back()->with('error', '❌ Please provide your phone number first.');
        }

        $payload = [
            'email' => $user->email,
            'name' => $user->name,
            'phoneNumber' => $user->phone,
            'bankCode' => [config('services.paymentpoint.bank_code')],
            'businessId' => config('services.paymentpoint.business_id'),
        ];

        $headers = [
            'Authorization' => 'Bearer ' . config('services.paymentpoint.secret'),
            'Content-Type' => 'application/json',
            'api-key' => config('services.paymentpoint.key'),
        ];

        $response = Http::withHeaders($headers)->post(
            'https://api.paymentpoint.co/api/v1/createVirtualAccount',
            $payload
        );

        $data = $response->json();

        if ($response->successful() && isset($data['bankAccounts'][0])) {
            $account = $data['bankAccounts'][0];

            $user->update([
                'virtual_account_number' => $account['accountNumber'] ?? null,
                'virtual_account_bank' => $account['bankName'] ?? null,
                'virtual_account_name' => $account['accountName'] ?? null,
            ]);

            return back()->with('success', '✅ Virtual account generated and saved successfully.');
        }

        Log::error('PaymentPoint Error', ['response' => $data]);

        return back()->with('error', $data['message'] ?? '❌ Failed to generate virtual account.');
    }





    public function paystackRedirect(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:100']);

        // Implement Paystack redirect logic (or use package if already integrated)
        // Redirect to paystack payment page...
    }

    public function flutterwaveRedirect(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:100']);

        // Implement Flutterwave redirect logic...
    }
}
