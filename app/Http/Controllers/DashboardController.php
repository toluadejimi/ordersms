<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Setting;
use App\Models\Visitor;

class DashboardController extends Controller
{
    public function index()
{
    $user = auth()->user();

    // ✅ Log visitor (once per day per IP or per user)
    $ip = request()->ip();
    $today = now()->toDateString();

    Visitor::firstOrCreate([
        'ip_address' => $ip,
        'visit_date' => $today,
    ], [
        'user_agent' => request()->userAgent(),
        'user_id' => $user->id,
    ]);

    $totalVerified = $user->verifications()
        ->whereIn('status', ['done', 'completed', 'received'])
        ->count();

    $totalSmsReceived = $user->verifications()
        ->whereNotNull('code')
        ->where('status', 'done')
        ->count();

    $verifications = $user->verifications()
        ->latest()
        ->take(5)
        ->get();

    return view('user.dashboard', [
        'user' => $user,
        'balance' => $user->wallet,
        'transactions' => $user->transactions()->latest()->limit(10)->get(),
        'virtualAccountEnabled' => Setting::get('virtual_account_enabled') === '1',
        'totalVerified' => $totalVerified,
        'totalSmsReceived' => $totalSmsReceived,
        'verifications' => $verifications,
    ]);
}
}
