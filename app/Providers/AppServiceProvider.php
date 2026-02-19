<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use App\Http\Responses\RegisterResponse;
use App\Listeners\PruneOriginalMediaAfterConversion;
use App\Support\TurnstileVerifier;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Spatie\MediaLibrary\Conversions\Events\ConversionHasBeenCompletedEvent;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerTurnstileValidationRule();
        $this->registerMediaListeners();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }

    protected function registerMediaListeners(): void
    {
        Event::listen(ConversionHasBeenCompletedEvent::class, PruneOriginalMediaAfterConversion::class);
    }

    protected function registerTurnstileValidationRule(): void
    {
        Validator::extend('turnstile', static function (string $attribute, mixed $value): bool {
            if (! is_string($value) || trim($value) === '') {
                return false;
            }

            $turnstileFacadeClass = 'RyanChandler\\LaravelCloudflareTurnstile\\Facades\\Turnstile';
            $turnstileFakeClientClass = 'RyanChandler\\LaravelCloudflareTurnstile\\Testing\\FakeClient';

            if (class_exists($turnstileFacadeClass) && class_exists($turnstileFakeClientClass)) {
                $turnstileClient = $turnstileFacadeClass::getFacadeRoot();

                if ($turnstileClient instanceof $turnstileFakeClientClass) {
                    return $turnstileFacadeClass::siteverify($value)->success;
                }
            }

            return TurnstileVerifier::verify($value, request()->ip());
        }, 'Turnstile verification failed. Please try again.');
    }
}
