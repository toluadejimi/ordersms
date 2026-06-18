<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;

use Illuminate\Support\Str;

class ReferralController extends Controller
{
   public function index()
{
    $user = Auth::user();

    // Only users who were referred by me
    $referrals = User::where('referred_by', $user->referral_code)->latest()->get();

    return view('user.referrals.index', compact('user', 'referrals'));
}

   public function withdraw(Request $request)
{
    $user = Auth::user();

    if ($user->referral_earnings < 500) {
        return back()->with('error', '❌ Minimum ₦500 referral earnings required to withdraw.');
    }

    $amount = $user->referral_earnings;

    // ✅ Transfer referral earnings to wallet
    $user->wallet += $amount;
    $user->referral_earnings = 0;
    $user->save();

    // ✅ Log withdrawal
    Transaction::create([
        'user_id' => $user->id,
        'type' => 'referral_withdrawal',
        'method' => 'internal',
        'amount' => $amount,
        'status' => 'completed',
        'reference' => 'REFWD_' . strtoupper(Str::random(8)),
    ]);

    return back()->with('success', '✅ Referral earnings moved to wallet successfully.');
}
}
