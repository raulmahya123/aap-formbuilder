<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Enter your company email address.',
            'email.email' => 'Enter a valid email address.',
            'password.required' => 'Enter your password.',
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

    // Cek apakah akun nonaktif
    $user = \App\Models\User::where('email', $this->input('email'))->first();
    if ($user && !$user->is_active) {
        RateLimiter::hit($this->throttleKey());
        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => 'Your account is inactive. Please contact an administrator.',
        ]);
    }

    // Hanya izinkan user yang aktif
    $credentials = $this->only('email', 'password') + ['is_active' => 1];

    if (! \Illuminate\Support\Facades\Auth::attempt($credentials, $this->boolean('remember'))) {
        RateLimiter::hit($this->throttleKey());

        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    RateLimiter::clear($this->throttleKey());
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
            'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
