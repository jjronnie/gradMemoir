<?php

namespace App\Http\Middleware;

use App\Support\TurnstileVerifier;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ValidateTurnstile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! TurnstileVerifier::shouldValidate()) {
            return $next($request);
        }

        $token = (string) $request->input('turnstile_token', $request->input('cf-turnstile-response'));

        if ($token === '') {
            throw ValidationException::withMessages([
                'turnstile' => 'Turnstile verification is required.',
            ]);
        }

        if (! TurnstileVerifier::verify($token, $request->ip())) {
            throw ValidationException::withMessages([
                'turnstile' => 'Turnstile verification failed. Please try again.',
            ]);
        }

        return $next($request);
    }
}
