<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait TokenValidation
{
    /**
     * Enforce that current token matches the authenticated token.
     *
     * @param Request $request
     * @return void
     */
    public function ensureValidToken(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->current_token !== $request->bearerToken()) {
            abort(response()->json([
                'message' => 'Session expired. Please login again.',
            ], 401));
        }
    }
}
