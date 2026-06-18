<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Verification;
use App\Models\Transaction;
use App\Models\Setting;
use App\Models\Visitor;
use App\Models\VisitorSession;
use Carbon\Carbon;

use App\Models\ManualFunding;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminCreditedWalletMail;
use App\Mail\ManualFundingStatusMail;



class AdminController extends Controller
{
 public function index()
{
    $totalUsers = User::count();
    $totalWalletBalance = User::sum('wallet');
    $totalVerifications = Verification::count();
    $totalPayments = Transaction::where('type', 'credit')->sum('amount'); // You may rename if needed

    // Add these:
    $totalMoneyIn = Transaction::whereIn('type', ['funding', 'manual'])->sum('amount');
    $totalMoneyOut = Transaction::where('type', 'debit')->sum('amount');

    // ✅ Fetch only the latest 10 verifications
    $verifications = Verification::with('user')->latest()->take(10)->get();

    return view('admin.dashboard', compact(
        'totalUsers',
        'totalWalletBalance',
        'totalVerifications',
        'totalPayments',
        'verifications',
        'totalMoneyIn',
        'totalMoneyOut'
    ));
}

public function trackVisitors()
{
    $today = Carbon::today();
    $startOfWeek = Carbon::now()->startOfWeek();
    $startOfMonth = Carbon::now()->startOfMonth();
    $startOfYear = Carbon::now()->startOfYear();

    $stats = [
        'today' => VisitorSession::whereDate('visited_at', $today)->distinct('ip_address')->count('ip_address'),
        'week' => VisitorSession::whereBetween('visited_at', [$startOfWeek, $today])->distinct('ip_address')->count('ip_address'),
        'month' => VisitorSession::whereBetween('visited_at', [$startOfMonth, $today])->distinct('ip_address')->count('ip_address'),
        'year' => VisitorSession::whereBetween('visited_at', [$startOfYear, $today])->distinct('ip_address')->count('ip_address'),
    ];

    $sessions = VisitorSession::with('user')
        ->latest('visited_at')
        ->paginate(30);

    return view('admin.visitors.track', compact('stats', 'sessions'));
}


public function manualFundings()
{
    $requests = \App\Models\ManualFunding::with('user') // Ensure 'user' relationship is loaded
        ->where('status', 'pending')
        ->latest()
        ->get();

    return view('admin.manual-fundings.index', compact('requests'));
}


   public function approveManualFunding($id)
{
    $funding = ManualFunding::where('id', $id)->where('status', 'pending')->firstOrFail();
    $user = User::findOrFail($funding->user_id);

    $user->wallet += $funding->amount;
    $user->save();

    $funding->status = 'approved';
    $funding->save();

    Transaction::create([
        'user_id' => $user->id,
        'type' => 'funding',
        'amount' => $funding->amount,
        'method' => 'manual',
        'reference' => $funding->reference,
    ]);

    // Notify user
  Mail::to($user->email)->send(new ManualFundingStatusMail('approved', $funding->amount, $user->wallet, $funding->note));

    return back()->with('success', 'Manual funding approved and wallet credited.');
}

  public function rejectManualFunding(Request $request, $id)
{
    $request->validate([
        'note' => 'required|string|max:500',
    ]);

    $funding = ManualFunding::where('id', $id)
        ->where('status', 'pending')
        ->firstOrFail();

    $funding->status = 'rejected';
    $funding->note = $request->note;
    $funding->save();

    // Notify user
    $user = User::findOrFail($funding->user_id);
    Mail::to($user->email)->send(
        new ManualFundingStatusMail('rejected', $funding->amount, $user->wallet, $request->note)
    );

    return back()->with('success', 'Manual funding request rejected.');
}


public function verificationList(Request $request)
{
    $query = Verification::with('user');

    if ($request->filled('search')) {
        $search = $request->input('search');

        $query->where(function ($q) use ($search) {
            $q->whereHas('user', function ($q2) use ($search) {
                $q2->where('email', 'like', '%' . $search . '%');
            })->orWhere('service', 'like', '%' . $search . '%');
        });
    }

    $verifications = $query->latest()->paginate(20);

    return view('admin.verifications.index', compact('verifications'));
}



    public function allTransactions(Request $request)
    {
        $query = Transaction::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('email', 'like', "%$search%");
                })->orWhere('method', 'like', "%$search%");
            });
        }

        $transactions = $query->latest()->paginate(20);
        return view('admin.transactions.index', compact('transactions'));
    }
public function settings()
{
    return view('admin.settings.index', [
        // DaisySMS (Server 1 / SMS History)
        'daisysmsApiKey'         => Setting::get('daisysms_api_key'),

        // DaisySIM Virtual Numbers
        'virtualApiKey'          => Setting::get('virtual_api_key'),
        'virtualUsdToNairaRate'  => Setting::get('virtual_usd_to_naira_rate', 1600),
        'virtualServiceGain'     => Setting::get('virtual_service_gain', 0),

        // Global Daisy Pricing (shared fallback)
        'usdToNairaRate'         => Setting::get('usd_to_naira_rate', 1400),
        'serviceGain'            => Setting::get('service_gain', 0),

        // Paystack
        'paystackPublic'         => Setting::get('paystack_public_key'),
        'paystackSecret'         => Setting::get('paystack_secret_key'),
        'paystackEnabled'        => Setting::get('paystack_enabled') === '1',

        // Flutterwave
        'flutterwavePublic'          => Setting::get('flutterwave_public_key'),
        'flutterwaveSecret'          => Setting::get('flutterwave_secret_key'),
        'flutterwaveEncryptionKey'   => Setting::get('flutterwave_encryption_key'),
        'flutterwaveRedirectUrl'     => Setting::get('flutterwave_redirect_url'),
        'flutterwaveEnabled'         => Setting::get('flutterwave_enabled') === '1',

        // Manual Payment
        'manualPaymentEnabled'   => Setting::get('manual_payment_enabled') === '1',

        // Virtual Account
        'virtualAccountEnabled'  => Setting::get('virtual_account_enabled') === '1',

        // PaymentPoint
        'paymentpointApiKey'     => Setting::get('paymentpoint_api_key'),
        'paymentpointSecret'     => Setting::get('paymentpoint_secret'),
        'paymentpointBusinessId' => Setting::get('paymentpoint_business_id'),
        'paymentpointBankCode'   => Setting::get('paymentpoint_bank_code'),

        // SprintPay
        'sprintpayWebkey'        => Setting::get('sprintpay_webkey'),
        'sprintpayBaseUrl'       => Setting::get('sprintpay_base_url') ?? config('services.sprintpay.base_url', 'https://web.sprintpay.online'),
        'sprintpayWebhookSecret' => Setting::get('sprintpay_webhook_secret'),
        'sprintpayEnabled'       => Setting::get('sprintpay_enabled') === '1',
        'sprintpayWebhookUrl'    => url('/api/webhook/sprintpay'),
        'sprintpayCallbackUrl'   => route('funding.sprintpay.callback'),

        // SMSMan (Server 4)
        'smsmanApiToken'         => Setting::get('smsman_api_token') ?? env('SMSMAN_API_TOKEN'),
        'smsmanRate'             => Setting::get('smsman_usd_to_naira_rate', 1600),
        'smsmanGain'             => Setting::get('smsman_service_gain', 0),
        'smsmanMarkupPercent'    => Setting::get('smsman_markup_percent', 50),

        // SMSPool (Server 3/5)
        'smspoolApiKey'          => Setting::get('smspool_api_key'),
        'smspoolRate'            => Setting::get('smspool_usd_to_naira_rate', 1600),
        'smspoolGain'            => Setting::get('smspool_service_gain', 0),

        // OprimeNumbers / Tellabot (Server 2)
        'tellabotRate'           => Setting::get('tellabot_usd_to_naira_rate', 0),
        'tellabotGain'           => Setting::get('tellabot_gain_percent', 0),
        'whatsapp_markup_percent'=> Setting::get('whatsapp_markup_percent', 0),
        'telegram_markup_percent'=> Setting::get('telegram_markup_percent', 0),
        'whatsapp_price_override'=> Setting::get('whatsapp_price_override'),
        'telegram_price_override'=> Setting::get('telegram_price_override'),

        // GoGetSMS (Server 6)
        'gogetsmsApiKey'         => Setting::get('gogetsms_api_key'),
        'gogetsmsRate'           => Setting::get('gogetsms_usd_to_naira_rate', 1500),
        'gogetsmsExtraCost'      => Setting::get('gogetsms_extra_cost', 0),

        // Settings array for misc keys
        'settings'               => [
            'smsman_markup_percent' => Setting::get('smsman_markup_percent', 50),
        ],
    ]);
}

public function updateSettings(Request $request)
{
    $fields = [
        // DaisySMS
        'daisysms_api_key',

        // DaisySIM Virtual Numbers
        'virtual_api_key',
        'virtual_usd_to_naira_rate',
        'virtual_service_gain',

        // Global Daisy Pricing
        'usd_to_naira_rate',
        'service_gain',

        // Paystack
        'paystack_public_key',
        'paystack_secret_key',

        // Flutterwave
        'flutterwave_public_key',
        'flutterwave_secret_key',
        'flutterwave_encryption_key',
        'flutterwave_redirect_url',

        // PaymentPoint
        'paymentpoint_api_key',
        'paymentpoint_secret',
        'paymentpoint_business_id',
        'paymentpoint_bank_code',

        // SprintPay
        'sprintpay_webkey',
        'sprintpay_base_url',
        'sprintpay_webhook_secret',

        // SMSMan
        'smsman_api_token',
        'smsman_usd_to_naira_rate',
        'smsman_service_gain',
        'smsman_markup_percent',

        // SMSPool
        'smspool_api_key',
        'smspool_usd_to_naira_rate',
        'smspool_service_gain',

        // OprimeNumbers / Tellabot
        'tellabot_usd_to_naira_rate',
        'tellabot_gain_percent',
        'whatsapp_markup_percent',
        'telegram_markup_percent',
        'whatsapp_price_override',
        'telegram_price_override',

        // GoGetSMS
        'gogetsms_api_key',
        'gogetsms_usd_to_naira_rate',
        'gogetsms_extra_cost',
    ];

    foreach ($fields as $field) {
        Setting::updateOrCreate(
            ['key' => $field],
            ['value' => $request->input($field)]
        );
    }

    // Boolean Toggles
    Setting::updateOrCreate(['key' => 'manual_payment_enabled'],  ['value' => $request->has('manual_payment_enabled')  ? '1' : '0']);
    Setting::updateOrCreate(['key' => 'virtual_account_enabled'], ['value' => $request->has('virtual_account_enabled') ? '1' : '0']);
    Setting::updateOrCreate(['key' => 'paystack_enabled'],        ['value' => $request->has('paystack_enabled')        ? '1' : '0']);
    Setting::updateOrCreate(['key' => 'flutterwave_enabled'],     ['value' => $request->has('flutterwave_enabled')     ? '1' : '0']);
    Setting::updateOrCreate(['key' => 'sprintpay_enabled'],      ['value' => $request->has('sprintpay_enabled')      ? '1' : '0']);

    return back()->with('success', 'Settings updated successfully.');
}














}