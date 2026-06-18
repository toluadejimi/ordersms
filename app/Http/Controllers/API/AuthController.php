<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    // 📝 Register via API
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|numeric|digits:11|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
            'ref'      => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $referralCode = strtoupper(Str::random(8));
        $referredBy = null;

        if ($request->filled('ref')) {
            $referrer = User::where('referral_code', $request->ref)->first();
            if ($referrer) {
                $referredBy = $referrer->referral_code;
            }
        }

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'password'      => Hash::make($request->password),
            'referral_code' => $referralCode,
            'referred_by'   => $referredBy,
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please check your email for verification link.',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    // 🔐 Login
    public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422);
    }

    // 🔍 Get user by email
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials',
        ], 401);
    }

    $inputPassword = $request->password;
    $storedPassword = $user->password;

    $isValid = false;

    // ✅ Try bcrypt
    if (Str::startsWith($storedPassword, '$2y$') || Str::startsWith($storedPassword, '$argon2')) {
        // Safe to check with bcrypt
        $isValid = Hash::check($inputPassword, $storedPassword);
    } else {
        // 👴 Legacy plain-text support
        $isValid = $inputPassword === $storedPassword;

        if ($isValid) {
            // 🔁 Upgrade password to bcrypt
            $user->password = Hash::make($inputPassword);
            $user->save();
        }
    }

    if (!$isValid) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials',
        ], 401);
    }

    // 🔐 Destroy old session if needed
    if ($user->session_id) {
        \Session::getHandler()->destroy($user->session_id);
    }

    // 🔄 Invalidate old tokens
    $user->tokens()->delete();

    // 🔑 Generate new session ID and token
    $user->session_id = Str::uuid()->toString();
    $user->save();

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'success'        => true,
        'message'        => 'Login successful',
        'token'          => $token,
        'session_id'     => $user->session_id,
        'email_verified' => $user->hasVerifiedEmail(),
        'user'           => $user,
    ]);
}


    // 🔓 Logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
