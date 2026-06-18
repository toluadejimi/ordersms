<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnforceApiSession
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $expected = $user->session_id;
            $current = $request->header('X-Session-ID');

            // If both expected and current exist but don't match, logout
            if ($expected && $current && $expected !== $current) {
                $request->user()->currentAccessToken()?->delete();

                return response()->json([
                    'success' => false,
                    'message' => 'You have been logged out. Your account was accessed on another device.',
                ], 401);
            }
        }

        return $next($request);
    }
}
