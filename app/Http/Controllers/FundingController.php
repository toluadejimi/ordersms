<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManualFunding;
use App\Models\Transaction;
use App\Models\Setting;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Mail\WalletFundedUserMail;
use App\Mail\WalletFundedAdminMail;
use App\Mail\ManualFundingSubmittedMail;
use App\Services\TelegramService;
use App\Services\SprintpayLogger;

class FundingController extends Controller
{
    public function show()
    {
        return view('user.fund-wallet');
    }

    public function paystackRedirect(Request $request)
    {
        $amount = $request->input('amount');
        $user = Auth::user();
        $paystackSecret = Setting::get('paystack_secret_key');

        $response = Http::withToken($paystackSecret)->post('https://api.paystack.co/transaction/initialize', [
            'email' => $user->email,
            'amount' => $amount * 100,
            'reference' => Str::uuid(),
            'callback_url' => route('funding.paystack.callback'),
        ]);

        $result = $response->json();

        if (isset($result['data']['authorization_url'])) {
            return redirect($result['data']['authorization_url']);
        }

        return back()->with('error', 'Unable to initiate Paystack payment.');
    }

    public function paystackCallback(Request $request)
    {
        $reference = $request->query('reference');
        $paystackSecret = Setting::get('paystack_secret_key');

        $response = Http::withToken($paystackSecret)->get("https://api.paystack.co/transaction/verify/$reference");
        $result = $response->json();

        if (isset($result['data']) && $result['data']['status'] === 'success') {
            $amount = $result['data']['amount'] / 100;
            $user = Auth::user();
            $method = 'paystack';

            if (Transaction::where('reference', $reference)->exists()) {
                return redirect()->route('dashboard')->with('error', 'Transaction already processed.');
            }

            $user->wallet += $amount;
            $user->save();

            $this->rewardReferrer($user, $amount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'funding',
                'amount' => $amount,
                'method' => $method,
                'reference' => $reference,
            ]);

            Mail::to($user->email)->send(new WalletFundedUserMail($amount, $method));
            foreach (Admin::all() as $admin) {
                Mail::to($admin->email)->send(new WalletFundedAdminMail($amount, $method, $user));
            }

            try {
                $message = "💰 <b>Wallet Funded (Paystack)</b>\n"
                         . "👤 User: <b>{$user->name}</b>\n"
                         . "📧 Email: <b>{$user->email}</b>\n"
                         . "💵 Amount: ₦" . number_format($amount, 2) . "\n"
                         . "🔗 Ref: <code>{$reference}</code>\n"
                         . "🕒 " . now()->format('Y-m-d H:i:s');

                (new TelegramService())->sendMessage($message);
            } catch (\Throwable $e) {
                \Log::error('Telegram error (paystack): ' . $e->getMessage());
            }

            return redirect()->route('dashboard')->with('success', 'Wallet funded successfully.');
        }

        return redirect()->route('dashboard')->with('error', 'Payment verification failed.');
    }

    public function flutterwaveRedirect(Request $request)
    {
        $amount = $request->input('amount');
        $tx_ref = 'FLW_' . uniqid();

        $flutterwaveSecret = Setting::get('flutterwave_secret_key');
        $redirectUrl = route('funding.flutterwave.callback');

        session([
            'flutterwave_tx_ref' => $tx_ref,
            'flutterwave_amount' => $amount,
        ]);

        $paymentData = [
            'tx_ref' => $tx_ref,
            'amount' => $amount,
            'currency' => 'NGN',
            'redirect_url' => $redirectUrl,
            'customer' => [
                'email' => auth()->user()->email,
                'name' => auth()->user()->name,
            ],
            'customizations' => [
                'title' => 'Fund Wallet',
                'description' => 'Wallet funding via Flutterwave',
            ],
            'meta' => [
                'user_id' => auth()->id(),
            ],
        ];

        $response = Http::withToken($flutterwaveSecret)
            ->post('https://api.flutterwave.com/v3/payments', $paymentData)
            ->json();

        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->away($response['data']['link']);
        }

        return back()->with('error', 'Unable to initiate Flutterwave payment.');
    }

    public function flutterwaveCallback(Request $request)
    {
        $tx_ref = $request->query('tx_ref');
        $status = strtolower($request->query('status'));
        $transaction_id = $request->query('transaction_id');
        $flutterwaveSecret = Setting::get('flutterwave_secret_key');

        if (!in_array($status, ['successful', 'completed'])) {
            return redirect()->route('wallet.fund')->with('error', 'Payment not successful.');
        }

        if (!$transaction_id) {
            return redirect()->route('wallet.fund')->with('error', 'Transaction ID missing.');
        }

        $response = Http::withToken($flutterwaveSecret)
            ->get("https://api.flutterwave.com/v3/transactions/{$transaction_id}/verify")
            ->json();

        if (
            isset($response['status']) &&
            $response['status'] === 'success' &&
            $response['data']['tx_ref'] === $tx_ref
        ) {
            $amount = $response['data']['amount'];
            $method = 'flutterwave';

            $userId = $response['data']['meta']['user_id'] ?? null;
            $user = User::find($userId);

            if (!$user) {
                return redirect()->route('wallet.fund')->with('error', 'User not found.');
            }

            if (Transaction::where('reference', $tx_ref)->exists()) {
                return redirect()->route('wallet.fund')->with('error', 'Transaction already processed.');
            }

            $user->wallet += $amount;
            $user->save();

            $this->rewardReferrer($user, $amount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'funding',
                'amount' => $amount,
                'method' => $method,
                'reference' => $tx_ref,
            ]);

            Mail::to($user->email)->send(new WalletFundedUserMail($amount, $method));
            foreach (Admin::all() as $admin) {
                Mail::to($admin->email)->send(new WalletFundedAdminMail($amount, $method, $user));
            }

            try {
                $message = "💰 <b>Wallet Funded (Flutterwave)</b>\n"
                         . "👤 User: <b>{$user->name}</b>\n"
                         . "📧 Email: <b>{$user->email}</b>\n"
                         . "💵 Amount: ₦" . number_format($amount, 2) . "\n"
                         . "🔗 Ref: <code>{$tx_ref}</code>\n"
                         . "🕒 " . now()->format('Y-m-d H:i:s');

                (new TelegramService())->sendMessage($message);
            } catch (\Throwable $e) {
                \Log::error('Telegram error (flutterwave): ' . $e->getMessage());
            }

            return redirect()->route('wallet.fund')->with('success', 'Wallet funded successfully.');
        }

        return redirect()->route('wallet.fund')->with('error', 'Verification failed.');
    }

    public function sprintpayRedirect(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
        ]);

        $amount = $request->input('amount');
        $user = Auth::user();
        $webkey = $this->getSprintpayWebkey();

        if (!$webkey) {
            SprintpayLogger::warning('redirect', 'Credit blocked: SprintPay webkey not configured.');
            return back()->with('error', 'SprintPay is not configured.');
        }

        $ref = $this->makeSprintpayRef($user);

        session([
            'sprintpay_ref' => $ref,
            'sprintpay_amount' => $amount,
        ]);

        $checkoutUrl = $this->sprintpayPaymentPageUrl($webkey, (int) $amount, $ref, $user->email);

        SprintpayLogger::info('redirect', 'Customer redirected to SprintPay payment page.', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ref' => $ref,
            'amount' => (int) $amount,
            'checkout_url' => $checkoutUrl,
        ]);

        return redirect()->away($checkoutUrl);
    }

    public function sprintpayVirtualAccount(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
        ]);

        $user = Auth::user();
        $webkey = $this->getSprintpayWebkey();

        if (!$webkey) {
            SprintpayLogger::warning('virtual_account', 'Credit blocked: SprintPay webkey not configured.');
            return response()->json(['status' => false, 'message' => 'SprintPay is not configured.'], 422);
        }

        $amount = (int) $request->input('amount');
        $ref = $this->makeSprintpayRef($user);

        $apiUrl = $this->sprintpayUrl('/api/get-account/wvn');

        $response = Http::post($apiUrl, [
            'key' => $webkey,
            'email' => $user->email,
            'amount' => $amount,
            'ref' => $ref,
        ]);

        $result = $response->json();

        if (!($result['status'] ?? false)) {
            SprintpayLogger::warning('virtual_account', 'Failed to generate virtual account — wallet cannot be funded yet.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ref' => $ref,
                'amount' => $amount,
                'api_url' => $apiUrl,
                'http_status' => $response->status(),
                'response' => $result,
            ]);
            return response()->json([
                'status' => false,
                'message' => $result['message'] ?? 'Unable to generate virtual account.',
            ], 422);
        }

        session([
            'sprintpay_ref' => $ref,
            'sprintpay_amount' => $amount,
        ]);

        SprintpayLogger::info('virtual_account', 'Virtual account generated; awaiting bank transfer.', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ref' => $ref,
            'amount' => $amount,
            'amount_to_pay' => $result['amount_to_pay'] ?? null,
            'account_no' => $result['account_no'] ?? null,
            'bank_name' => $result['bank_name'] ?? null,
        ]);

        return response()->json([
            'status' => true,
            'ref' => $ref,
            'account_no' => $result['account_no'],
            'account_name' => $result['account_name'],
            'bank_name' => $result['bank_name'],
            'amount_to_pay' => $result['amount_to_pay'],
        ]);
    }

    public function sprintpayVerify(Request $request)
    {
        $ref = $request->input('ref') ?? session('sprintpay_ref');

        if (!$ref) {
            SprintpayLogger::warning('verify', 'Credit blocked: no payment reference in request or session.', [
                'user_id' => Auth::id(),
            ]);
            return response()->json(['status' => false, 'message' => 'No pending payment found.'], 404);
        }

        if (Transaction::where('reference', $ref)->exists()) {
            SprintpayLogger::info('verify', 'Payment already credited (duplicate verify request).', [
                'user_id' => Auth::id(),
                'ref' => $ref,
            ]);
            return response()->json(['status' => true, 'message' => 'completed', 'credited' => true]);
        }

        [$data, $verifyMeta] = $this->verifySprintpayTransaction($ref);

        if (!$data) {
            SprintpayLogger::warning('verify', 'Credit blocked: SprintPay reports payment not completed.', [
                'user_id' => Auth::id(),
                'ref' => $ref,
                'verify' => $verifyMeta,
            ]);
            return response()->json(['status' => false, 'message' => 'Payment not completed yet.']);
        }

        $user = Auth::user();
        $amount = $data['amount'] ?? session('sprintpay_amount');

        if (!$this->creditSprintpayWallet($user, $amount, $ref, 'verify')) {
            return response()->json(['status' => false, 'message' => 'Unable to credit wallet. Check admin logs.'], 500);
        }

        session()->forget(['sprintpay_ref', 'sprintpay_amount']);

        return response()->json([
            'status' => true,
            'message' => 'completed',
            'credited' => true,
            'amount' => $amount,
        ]);
    }

    public function sprintpayCallback(Request $request)
    {
        $ref = $request->query('ref')
            ?? $request->query('order_id')
            ?? session('sprintpay_ref');

        if (!$ref) {
            SprintpayLogger::warning('callback', 'Credit blocked: payment session expired (no ref in session).', [
                'user_id' => Auth::id(),
            ]);
            return redirect()->route('funding.show')->with('error', 'Payment session expired.');
        }

        if (Transaction::where('reference', $ref)->exists()) {
            session()->forget(['sprintpay_ref', 'sprintpay_amount']);
            return redirect()->route('funding.show')->with('success', 'Wallet funded successfully.');
        }

        [$data, $verifyMeta] = $this->verifySprintpayTransaction($ref);

        if ($data) {
            $amount = $data['amount'] ?? session('sprintpay_amount');
            $user = Auth::user();

            if (!$user && preg_match('/^SPAY_(\d+)_/', $ref, $matches)) {
                $user = User::find($matches[1]);
            }

            if (!$user) {
                SprintpayLogger::warning('callback', 'Credit blocked: user not logged in and ref has no matching user.', [
                    'ref' => $ref,
                ]);
                return redirect()->route('funding.show')->with('error', 'Please log in to complete wallet funding.');
            }

            if ($this->creditSprintpayWallet($user, $amount, $ref, 'callback')) {
                session()->forget(['sprintpay_ref', 'sprintpay_amount']);
                return redirect()->route('funding.show')->with('success', 'Wallet funded successfully.');
            }

            return redirect()->route('funding.show')->with('error', 'Payment verified but wallet credit failed. Contact support.');
        }

        SprintpayLogger::warning('callback', 'Credit blocked: SprintPay reports payment not completed on return.', [
            'user_id' => Auth::id(),
            'ref' => $ref,
            'verify' => $verifyMeta,
        ]);

        return redirect()->route('funding.show')->with('error', 'Payment not completed yet. If you have paid, your wallet will be credited shortly.');
    }

    public function handleSprintpayWebhook(Request $request)
    {
        $webhookSecret = Setting::get('sprintpay_webhook_secret') ?? config('services.sprintpay.webhook_secret');

        if ($webhookSecret && !$this->isValidSprintpayWebhookAuth($request->header('Authorization', ''), $webhookSecret)) {
            SprintpayLogger::warning('webhook', 'Credit blocked: invalid webhook authorization header.', [
                'ip' => $request->ip(),
                'auth_format' => str_starts_with(trim($request->header('Authorization', '')), 'Bearer ') ? 'bearer' : 'raw',
            ]);
            return response()->json(['status' => false], 401);
        }

        $payload = $request->all();
        $ref = $payload['order_id'] ?? $payload['ref'] ?? null;
        $email = $payload['email'] ?? null;
        $amount = (float) ($payload['amount'] ?? $payload['amount_settled'] ?? 0);

        SprintpayLogger::info('webhook', 'Webhook received from SprintPay.', [
            'ref' => $ref,
            'email' => $email,
            'amount' => $amount,
            'event' => $payload['event'] ?? null,
            'session_id' => $payload['session_id'] ?? null,
        ]);

        if (!$ref || !$amount) {
            SprintpayLogger::warning('webhook', 'Credit blocked: missing required webhook fields.', [
                'ref' => $ref,
                'email' => $email,
                'amount' => $amount,
                'payload_keys' => array_keys($payload),
            ]);
            return response()->json(['status' => false], 422);
        }

        if (Transaction::where('reference', $ref)->where('type', 'funding')->exists()) {
            SprintpayLogger::info('webhook', 'Webhook ignored: transaction already credited.', [
                'ref' => $ref,
                'email' => $email,
            ]);
            return response()->json(['status' => true]);
        }

        $user = null;

        if ($email) {
            $user = User::where('email', $email)->first();
        }

        if (!$user && preg_match('/^SPAY_(\d+)_/', $ref, $matches)) {
            $user = User::find($matches[1]);
        }

        if (!$user) {
            SprintpayLogger::warning('webhook', 'Credit blocked: user not found for webhook email/ref.', [
                'ref' => $ref,
                'email' => $email,
                'amount' => $amount,
            ]);
            return response()->json(['status' => false], 404);
        }

        if (!$this->creditSprintpayWallet($user, $amount, $ref, 'webhook')) {
            return response()->json(['status' => false], 500);
        }

        return response()->json(['status' => true]);
    }

    private function isValidSprintpayWebhookAuth(string $header, string $secret): bool
    {
        $header = trim($header);
        $secret = trim($secret);

        if ($header === '') {
            return false;
        }

        if (hash_equals($secret, $header)) {
            return true;
        }

        if (preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return hash_equals($secret, trim($matches[1]));
        }

        return false;
    }

    private function creditSprintpayWallet(User $user, float $amount, string $reference, string $source): bool
    {
        if ($amount <= 0) {
            SprintpayLogger::warning($source, 'Credit blocked: invalid amount.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ref' => $reference,
                'amount' => $amount,
            ]);
            return false;
        }

        if (Transaction::where('reference', $reference)->exists()) {
            SprintpayLogger::info($source, 'Credit skipped: transaction reference already exists.', [
                'user_id' => $user->id,
                'ref' => $reference,
            ]);
            return true;
        }

        try {
            $this->creditWallet($user, $amount, $reference, 'sprintpay');

            SprintpayLogger::info($source, 'Wallet credited successfully.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ref' => $reference,
                'amount' => $amount,
                'new_wallet_balance' => $user->fresh()->wallet,
            ]);

            return true;
        } catch (\Throwable $e) {
            SprintpayLogger::error($source, 'Credit failed: exception while crediting wallet.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ref' => $reference,
                'amount' => $amount,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function creditWallet(User $user, float $amount, string $reference, string $method): void
    {
        $user->wallet += $amount;
        $user->save();

        $this->rewardReferrer($user, $amount);

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'funding',
            'amount' => $amount,
            'method' => $method,
            'reference' => $reference,
        ]);

        Mail::to($user->email)->send(new WalletFundedUserMail($amount, $method));
        foreach (Admin::all() as $admin) {
            Mail::to($admin->email)->send(new WalletFundedAdminMail($amount, $method, $user));
        }

        try {
            $label = ucfirst($method);
            $message = "💰 <b>Wallet Funded ({$label})</b>\n"
                     . "👤 User: <b>{$user->name}</b>\n"
                     . "📧 Email: <b>{$user->email}</b>\n"
                     . "💵 Amount: ₦" . number_format($amount, 2) . "\n"
                     . "🔗 Ref: <code>{$reference}</code>\n"
                     . "🕒 " . now()->format('Y-m-d H:i:s');

            (new TelegramService())->sendMessage($message);
        } catch (\Throwable $e) {
            Log::error("Telegram error ({$method}): " . $e->getMessage());
        }
    }

    private function getSprintpayWebkey(): ?string
    {
        return Setting::get('sprintpay_webkey') ?? config('services.sprintpay.webkey');
    }

    private function sprintpayUrl(string $path): string
    {
        $base = rtrim(
            Setting::get('sprintpay_base_url') ?? config('services.sprintpay.base_url', 'https://web.sprintpay.online'),
            '/'
        );

        return $base . $path;
    }

    private function sprintpayPaymentPageUrl(string $webkey, int $amount, string $ref, string $email): string
    {
        $query = http_build_query([
            'amount' => $amount,
            'key' => $webkey,
            'ref' => $ref,
            'email' => $email,
        ]);

        return $this->sprintpayUrl('/pay') . '?' . $query;
    }

    private function makeSprintpayRef(User $user): string
    {
        return 'SPAY_' . $user->id . '_' . strtoupper(Str::random(10));
    }

    private function verifySprintpayTransaction(string $ref): array
    {
        $apiUrl = $this->sprintpayUrl('/api/verify-transaction');

        try {
            $response = Http::timeout(30)->get($apiUrl, ['ref' => $ref]);
            $result = $response->json() ?? [];
            $meta = [
                'api_url' => $apiUrl,
                'http_status' => $response->status(),
                'status' => $result['status'] ?? null,
                'message' => $result['message'] ?? null,
                'data' => $result['data'] ?? null,
            ];

            if (($result['status'] ?? false) === true && ($result['message'] ?? '') === 'completed') {
                return [$result['data'] ?? [], $meta];
            }

            return [null, $meta];
        } catch (\Throwable $e) {
            $meta = [
                'api_url' => $apiUrl,
                'exception' => $e->getMessage(),
            ];

            SprintpayLogger::error('verify_api', 'SprintPay verify-transaction request failed.', [
                'ref' => $ref,
                'verify' => $meta,
            ]);

            return [null, $meta];
        }
    }

    public function handlePaymentpointWebhook(Request $request)
    {
        $raw = $request->getContent();
        $signature = $request->header('Paymentpoint-Signature');
        $secretKey = config('services.paymentpoint.secret');

        $expectedSignature = hash_hmac('sha256', $raw, $secretKey);

        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('❌ Invalid PaymentPoint Signature');
            return response('Invalid signature.', 400);
        }

        $data = json_decode($raw, true);

        if (($data['notification_status'] ?? '') === 'payment_successful') {
            $email = $data['customer']['email'] ?? null;
            $amount = $data['amount_paid'] ?? 0;
            $reference = $data['transaction_id'] ?? null;

            if (!$email || !$amount || !$reference) {
                return response('Missing required fields', 422);
            }

            $user = User::where('email', $email)->first();

            if (!$user) {
                return response('User not found.', 404);
            }

            if (Transaction::where('reference', $reference)->exists()) {
                return response('Already processed.', 200);
            }

            $user->wallet += $amount;
            $user->save();

            $this->rewardReferrer($user, $amount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'funding',
                'amount' => $amount,
                'method' => 'paymentpoint',
                'reference' => $reference,
            ]);

            Mail::to($user->email)->send(new WalletFundedUserMail($amount, 'paymentpoint'));
            foreach (Admin::all() as $admin) {
                Mail::to($admin->email)->send(new WalletFundedAdminMail($amount, 'paymentpoint', $user));
            }

            try {
                $message = "💰 <b>Wallet Funded (PaymentPoint)</b>\n"
                         . "👤 User: <b>{$user->name}</b>\n"
                         . "📧 Email: <b>{$user->email}</b>\n"
                         . "💵 Amount: ₦" . number_format($amount, 2) . "\n"
                         . "🔗 Ref: <code>{$reference}</code>\n"
                         . "🕒 " . now()->format('Y-m-d H:i:s');

                (new TelegramService())->sendMessage($message);
            } catch (\Throwable $e) {
                \Log::error('Telegram error (paymentpoint): ' . $e->getMessage());
            }

            return response('Webhook processed.', 200);
        }

        return response('Ignored.', 200);
    }

    public function submitManual(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'reference' => 'nullable|string',
            'note' => 'nullable|string',
            'proof' => 'required|image|max:2048',
        ]);

        $filename = time() . '_' . $request->file('proof')->getClientOriginalName();
        $request->file('proof')->move(public_path('storage/manual_proofs'), $filename);
        $path = 'manual_proofs/' . $filename;

        $user = auth()->user();

        ManualFunding::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'reference' => $request->reference,
            'note' => $request->note,
            'proof' => $path,
            'status' => 'pending',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'funding_request',
            'amount' => $request->amount,
            'method' => 'manual',
            'reference' => $request->reference,
        ]);

        Mail::to($user->email)->send(new WalletFundedUserMail($request->amount, 'manual (pending approval)'));
        foreach (Admin::all() as $admin) {
            Mail::to($admin->email)->send(new ManualFundingSubmittedMail($user, $request->amount, $request->note));
        }

        try {
            $message = "📝 <b>Manual Funding Submitted</b>\n"
                     . "👤 User: <b>{$user->name}</b>\n"
                     . "📧 Email: <b>{$user->email}</b>\n"
                     . "💵 Amount: ₦" . number_format($request->amount, 2) . "\n"
                     . "📝 Note: <i>" . ($request->note ?? 'N/A') . "</i>\n"
                     . "🕒 " . now()->format('Y-m-d H:i:s');

            (new TelegramService())->sendMessage($message);
        } catch (\Throwable $e) {
            \Log::error('Telegram error (manual funding): ' . $e->getMessage());
        }

        return back()->with('success', 'Manual funding request submitted.');
    }

   private function rewardReferrer(User $user, float $amount)
{
    if (!$user->first_deposit_made && $user->referred_by) {
        $referrer = User::where('referral_code', $user->referred_by)->first();

        if ($referrer) {
            $bonus = $amount * 0.05;

            // ✅ Credit referral earnings, not wallet
            $referrer->referral_earnings += $bonus;
            $referrer->save();

            // ✅ Log referral bonus (status = pending)
            Transaction::create([
                'user_id' => $referrer->id,
                'type' => 'referral_bonus',
                'method' => 'referral',
                'amount' => $bonus,
                'status' => 'pending',
                'reference' => 'REFBONUS_' . strtoupper(Str::random(8)),
            ]);
        }

        $user->first_deposit_made = true;
        $user->save();
    }
}

}
