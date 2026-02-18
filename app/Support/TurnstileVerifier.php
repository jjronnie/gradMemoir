<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

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

        if ($token === '') {
            return false;
        }

        $response = Http::asForm()->post((string) config('services.turnstile.verify_url'), [
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => $remoteIp,
        ]);

        return $response->successful() && (bool) $response->json('success');
    }
}
