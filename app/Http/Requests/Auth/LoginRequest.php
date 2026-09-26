<?php

namespace App\Http\Requests\Auth;

use App\Models\LoginSecurityLog;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
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

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            // Registrar intento fallido en rate limiter (expira a los 5 minutos)
            RateLimiter::hit($this->throttleKey(), 300);

            $attempts = RateLimiter::attempts($this->throttleKey());

            // --- Logging en archivo ---
            Log::warning('Intento de inicio de sesión fallido', [
                'email'      => $this->input('email'),
                'ip'         => $this->ip(),
                'user_agent' => $this->userAgent(),
                'intentos'   => $attempts,
            ]);

            // --- Logging en base de datos ---
            $bloqueado_hasta = null;
            if ($attempts >= 10) {
                $seconds = RateLimiter::availableIn($this->throttleKey());
                $bloqueado_hasta = now()->addSeconds($seconds);

                Log::warning('IP BLOQUEADA por exceso de intentos de login', [
                    'ip'              => $this->ip(),
                    'email'           => $this->input('email'),
                    'bloqueado_hasta' => $bloqueado_hasta->toDateTimeString(),
                ]);
            }

            LoginSecurityLog::create([
                'ip'                 => $this->ip(),
                'email'              => $this->input('email'),
                'user_agent'         => substr($this->userAgent() ?? '', 0, 500),
                'evento'             => ($attempts >= 10) ? 'bloqueado' : 'intento_fallido',
                'intentos_acumulados' => $attempts,
                'bloqueado_hasta'    => $bloqueado_hasta,
                'created_at'         => now(),
            ]);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Login exitoso — limpiar rate limiter y registrar éxito
        $this->logLoginSuccess();
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        // Permitir hasta 10 intentos fallidos antes de bloquear por 5 minutos
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 10)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());
        $minutes = ceil($seconds / 60);

        // Registrar el evento de bloqueo activo
        Log::warning('Intento de acceso bloqueado (throttle activo)', [
            'ip'               => $this->ip(),
            'email'            => $this->input('email'),
            'segundos_restantes' => $seconds,
        ]);

        LoginSecurityLog::create([
            'ip'                 => $this->ip(),
            'email'              => $this->input('email'),
            'user_agent'         => substr($this->userAgent() ?? '', 0, 500),
            'evento'             => 'bloqueado',
            'intentos_acumulados' => RateLimiter::attempts($this->throttleKey()),
            'bloqueado_hasta'    => now()->addSeconds($seconds),
            'created_at'         => now(),
        ]);

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => $minutes,
            ]),
        ])->status(429);
    }

    /**
     * Log a successful login to the security log.
     */
    protected function logLoginSuccess(): void
    {
        LoginSecurityLog::create([
            'ip'                 => $this->ip(),
            'email'              => $this->input('email'),
            'user_agent'         => substr($this->userAgent() ?? '', 0, 500),
            'evento'             => 'exitoso',
            'intentos_acumulados' => RateLimiter::attempts($this->throttleKey()),
            'bloqueado_hasta'    => null,
            'created_at'         => now(),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     * Keyed by IP only so that different accounts from the same IP share the limit.
     */
    public function throttleKey(): string
    {
        return 'login|' . $this->ip();
    }
}
