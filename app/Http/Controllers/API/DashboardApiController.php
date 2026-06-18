<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Visitor;

class DashboardApiController extends Controller
{
    
    public function index(Request $request)
{
    if ($request->user()->session_id !== $request->session()->getId()) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->withErrors([
            'email' => '⛔ You have been logged out due to another login.',
        ]);
    }

    return view('dashboard');
}

    
    public function data(Request $request)
    {
        $user = $request->user();

        // Log visitor
        $ip = $request->ip();
        $today = now()->toDateString();

        Visitor::firstOrCreate([
            'ip_address' => $ip,
            'visit_date' => $today,
        ], [
            'user_agent' => $request->userAgent(),
            'user_id' => $user->id,
        ]);

        $verifications = $user->verifications()
            ->latest()
            ->take(5)
            ->get(['id', 'name', 'service', 'number', 'code', 'status', 'created_at']);

        $transactions = $user->transactions()
            ->latest()
            ->take(10)
            ->get(['id', 'type', 'method', 'amount', 'created_at']);

        return response()->json([
    'wallet' => $user->wallet, // ✅ ADD THIS LINE
    'user' => [
        'name' => $user->name,
        'wallet' => $user->wallet, // still fine to keep here too
        'referral_code' => $user->referral_code,
        'phone' => $user->phone,
    ],


            'virtual_account' => [
                'enabled' => Setting::get('virtual_account_enabled') === '1',
                'account_number' => $user->virtual_account_number,
                'bank_name' => $user->virtual_account_bank,
                'account_name' => $user->virtual_account_name ?? $user->name,
            ],
            'totalVerified' => $user->verifications()
                ->whereIn('status', ['done', 'completed', 'received'])
                ->count(),

            'totalSpent' => $user->verifications()->sum('price'),
            'verifications' => $verifications,
            'transactions' => $transactions,
        ]);
    }
}
