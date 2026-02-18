<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TurnstileVerifier
{
    public static function shouldValidate(): bool
    {
        if (app()->environment(['local', 'testing'])) {
            return false;
        }

        $secretKey = config('services.turnstile.secret_key');

        return is_string($secretKey) && $secretKey !== '';
    }

    public static function verify(string $token, ?string $remoteIp = null): bool
    {
        if (! self::shouldValidate()) {
            return true;
        }

        $secretKey = config('services.turnstile.secret_key');
        $token = trim($token);

        if ($token === '') {
            return false;
        }

        $payload = [
            'secret' => $secretKey,
            'response' => $token,
        ];

        $normalizedRemoteIp = self::normalizeRemoteIp($remoteIp);

        if ($normalizedRemoteIp !== null) {
            $payload['remoteip'] = $normalizedRemoteIp;
        }

        try {
            $response = Http::asForm()
                ->timeout(8)
                ->retry(1, 200)
                ->post((string) config('services.turnstile.verify_url'), $payload);
        } catch (Throwable $exception) {
            Log::warning('Turnstile verification request failed.', [
                'message' => $exception->getMessage(),
            ]);

            return false;
        }

        $wasSuccessful = $response->successful() && (bool) $response->json('success');

        if (! $wasSuccessful) {
            Log::warning('Turnstile verification was rejected.', [
                'status' => $response->status(),
                'error_codes' => $response->json('error-codes'),
            ]);
        }

        return $wasSuccessful;
    }

    private static function normalizeRemoteIp(?string $remoteIp): ?string
    {
        if (! is_string($remoteIp)) {
            return null;
        }

        $remoteIp = trim($remoteIp);

        if ($remoteIp === '') {
            return null;
        }

        $isPublicIp = filter_var(
            $remoteIp,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );

        return is_string($isPublicIp) ? $isPublicIp : null;
    }
}
