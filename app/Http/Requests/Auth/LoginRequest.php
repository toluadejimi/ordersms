<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
public function authenticate(): void
{
    $this->ensureIsNotRateLimited();

    $credentials = $this->only('email', 'password');
    $user = Auth::getProvider()->retrieveByCredentials($credentials);

    if ($user) {
        // ✅ Check password (bcrypt or md5)
        $isPasswordCorrect = $this->checkPassword($user, $credentials['password']);

        if ($isPasswordCorrect) {
            // ✅ Check if user is banned BEFORE login
            if ($user->banned) {
                throw ValidationException::withMessages([
                    'email' => '🚫 Your account has been banned. Please contact support.',
                ]);
            }

            // ✅ Log the user in
            Auth::login($user);
            RateLimiter::clear($this->throttleKey());
            return;
        }
    }

    RateLimiter::hit($this->throttleKey());

    throw ValidationException::withMessages([
        'email' => trans('auth.failed'),
    ]);
}


    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::lower($this->input('email')).'|'.$this->ip();
    }

    /**
     * Check if the user's password matches, considering both MD5 and Bcrypt hashes.
     *
     * @param  \App\Models\User  $user
     * @param  string  $password
     * @return bool
     */
    protected function checkPassword($user, string $password): bool
    {
        // Check if password length is 32 characters (MD5 length)
        if (strlen($user->password) === 32) {
            // Check MD5 password hash
            return md5($password) === $user->password;
        }

        // Otherwise, check Bcrypt password hash using Hash::check()
        return Hash::check($password, $user->password);
    }
}
