<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use App\Services\TelegramService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
        'phone' => ['required', 'numeric', 'digits:11', 'unique:' . User::class],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    // ✅ Generate referral code
    $referralCode = strtoupper(Str::random(8));

    // ✅ Resolve referred_by from ?ref=XYZ
    $referredBy = null;
    if ($request->has('ref')) {
        $referrer = User::where('referral_code', $request->ref)->first();
        if ($referrer) {
            $referredBy = $referrer->referral_code;
        }
    }

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'password' => Hash::make($request->password),
        'referral_code' => $referralCode,
        'referred_by' => $referredBy,
    ]);

    event(new Registered($user));
    Auth::login($user);

    // ✅ Telegram notification
    try {
        $message = "🆕 <b>New User Registered</b>\n"
                 . "👤 Name: <b>" . e($user->name) . "</b>\n"
                 . "📧 Email: <b>" . e($user->email) . "</b>\n"
                 . "📞 Phone: <code>" . e($user->phone) . "</code>\n"
                 . "🕐 " . now()->format('Y-m-d H:i:s');

        app(\App\Services\TelegramService::class)->sendMessage($message);
    } catch (\Throwable $e) {
        \Log::error('Telegram error (new registration): ' . $e->getMessage());
    }

    return redirect()->route('dashboard');
}
}