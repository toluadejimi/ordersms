<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use App\Services\TelegramService;

use App\Mail\UserBannedStatusMail; // add this at the top

use App\Mail\AdminCreditedWalletMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminDebitedWalletMail;

class UserManagementController extends Controller
{
    
    protected $telegram;

public function __construct(TelegramService $telegram)
{
    $this->telegram = $telegram;
}

    
    
   public function index(Request $request)
{
    $query = User::query();

    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    $users = $query->orderBy('created_at', 'desc')->paginate(20);
    return view('admin.users.index', compact('users'));
}

public function show($id)
{
    $user = User::with(['verifications', 'transactions'])->findOrFail($id);
    return view('admin.users.show', compact('user'));
}
public function debitWallet(Request $request, User $user)
{
    $request->validate([
        'amount' => 'required|numeric|min:1',
    ]);

    $amount = $request->input('amount');

    if ($user->wallet < $amount) {
        return back()->with('error', 'Insufficient wallet balance to debit.');
    }

    // Debit wallet
    $user->wallet -= $amount;
    $user->save();

    // Log transaction
    Transaction::create([
        'user_id' => $user->id,
        'type' => 'debit',
        'amount' => $amount,
        'method' => 'admin-debit',
        'reference' => 'ADMIN_DEBIT_' . uniqid(),
    ]);

    // Send email to user
    Mail::to($user->email)->send(new AdminDebitedWalletMail($amount, $user->wallet, $user));

    return back()->with('success', 'Wallet debited and user notified.');
}

     public function fundUserWallet(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = User::findOrFail($id);
        $amount = $request->input('amount');
        $method = 'admin-credit';

        $user->wallet += $amount;
        $user->save();

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'funding',
            'amount' => $amount,
            'method' => $method,
            'reference' => 'ADMIN_' . uniqid(),
        ]);

        // ✅ Send specialized email
        Mail::to($user->email)->send(new AdminCreditedWalletMail($amount, $user->wallet, $user));

        return back()->with('success', 'Wallet credited and user notified.');
    }
  public function toggleBan($id)
{
    $user = User::findOrFail($id);
    $user->banned = !$user->banned;
    $user->save();

    $status = $user->banned ? 'banned' : 'unbanned';

    // ✅ Send email to user
    Mail::to($user->email)->send(new \App\Mail\UserBannedStatusMail($user, $status));

    // ✅ Send Telegram notification to admin
    $message = "
<b>🚨 User Ban Status Changed</b>

<b>Name:</b> {$user->name}
<b>Email:</b> {$user->email}
<b>Status:</b> <code>{$status}</code>
<b>Time:</b> " . now()->toDateTimeString();

    $this->telegram->sendMessage($message);

    return back()->with('success', "User has been {$status}.");
}




}
