<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class BaseApiController extends Controller
{
    /**
     * Ensures the session is still valid for this user.
     * If not, logs them out and returns a 401 response.
     */
    protected function ensureValidSession(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->session_id !== $request->session()->getId()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'message' => '⛔ You were logged out because your session was replaced by a new login.',
            ], 401);
        }

        return null;
    }
}
