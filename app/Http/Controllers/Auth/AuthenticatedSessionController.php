<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Services\TelegramService;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle a web login request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // 🛑 Kill previous web session if it exists
        if ($user->session_id && $user->session_id !== session()->getId()) {
            \Session::getHandler()->destroy($user->session_id);

            // 🔐 Invalidate all API tokens (log out of mobile app)
            $user->tokens()->delete();
        }

        // 💾 Save new session ID (web)
        $user->session_id = session()->getId();
        $user->save();

        // ✅ Send Telegram notification (optional)
        try {
            $message = "🔐 <b>User Logged In</b>\n"
                     . "👤 Name: <b>{$user->name}</b>\n"
                     . "📧 Email: <b>{$user->email}</b>\n"
                     . "🕐 " . now()->format('Y-m-d H:i:s');
            (new TelegramService())->sendMessage($message);
        } catch (\Throwable $e) {
            \Log::error('Telegram login error: ' . $e->getMessage());
        }

       return redirect()->route('dashboard');

    }

    /**
     * Forcefully clear a session by email (admin use).
     */
    public function forceClearSession(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user) {
            $user->update(['session_id' => null]);
            $user->tokens()->delete();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back()->with('status', '✅ Previous session cleared. You can now log in.');
    }

    /**
     * Logout the user (web).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            $user->update(['session_id' => null]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
