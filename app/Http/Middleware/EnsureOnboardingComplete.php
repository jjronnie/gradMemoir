<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if (! $user->onboarding_completed && ! $request->routeIs('onboarding.*')) {
            return redirect()->route('onboarding.show');
        }

        if (
            $user->onboarding_completed
            && $request->routeIs('onboarding.show')
            && $user->role === UserRole::Superadmin
        ) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->onboarding_completed && $request->routeIs('onboarding.show')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
