<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WalletApiController extends Controller
{
    public function generateVirtualAccount(Request $request)
    {
        $user = $request->user();

        if (empty($user->phone)) {
            return response()->json(['message' => 'Please provide your phone number.'], 422);
        }

        // If account already exists, return it
        if ($user->virtual_account_number && $user->virtual_account_bank) {
            return response()->json([
                'message' => '✅ Virtual account already exists.',
                'account' => [
                    'accountNumber' => $user->virtual_account_number,
                    'bankName' => $user->virtual_account_bank,
                    'accountName' => $user->virtual_account_name ?? $user->name,
                ]
            ]);
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
        Log::info('PaymentPoint VA response', $data); // 🪵 Log for debugging

        if ($response->successful() && isset($data['bankAccounts'][0])) {
            $account = $data['bankAccounts'][0];

            $user->update([
                'virtual_account_number' => $account['accountNumber'] ?? null,
                'virtual_account_bank' => $account['bankName'] ?? null,
                'virtual_account_name' => $account['accountName'] ?? null,
            ]);

            return response()->json([
                'message' => '✅ Virtual account generated successfully.',
                'account' => $account,
            ]);
        }

        Log::error('❌ PaymentPoint VA Error', ['response' => $data]);

        return response()->json([
            'message' => $data['message'] ?? '❌ Failed to generate virtual account.',
        ], 500);
    }
}
