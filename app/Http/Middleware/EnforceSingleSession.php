<?php

// app/Http/Middleware/EnforceSingleSession.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnforceSingleSession
{
    public function handle(Request $request, Closure $next)
    {
        // Allow impersonation sessions to bypass this check
        if (session()->has('impersonate_admin_id')) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();
            $sessionId = session()->getId();

            if ($user->session_id && $user->session_id !== $sessionId) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->withErrors([
                    'email' => 'You were logged out because your account was accessed from another device.',
                ]);
            }
        }

        return $next($request);
    }
}
