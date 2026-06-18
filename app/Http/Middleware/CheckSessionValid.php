<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSessionValid
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->session_id !== $request->session()->getId()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('kicked', 'You were logged out because your account was used on another device.');
        }

        return $next($request);
    }
}

