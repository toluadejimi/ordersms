<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class PreventAllMultipleSessions
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->session_id !== session()->getId()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'message' => 'You were logged out because your session was replaced.',
            ], 401);
        }

        return $next($request);
    }
}
