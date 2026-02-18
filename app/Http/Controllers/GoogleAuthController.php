<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\PostLoginRedirector;
use App\Support\UsernameGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $throwable) {
            return redirect()->route('login')->with('error', 'Google sign-in failed.');
        }

        $googleId = (string) $googleUser->getId();
        $name = (string) ($googleUser->getName() ?: $googleUser->getNickname() ?: 'Student');
        $email = (string) $googleUser->getEmail();

        if ($email === '') {
            return redirect()->route('login')->with('error', 'Google account email is required.');
        }

        $existingGoogleUser = User::query()->where('google_id', $googleId)->first();

        if ($existingGoogleUser !== null) {
            if ($existingGoogleUser->email_verified_at === null) {
                $existingGoogleUser->forceFill([
                    'email_verified_at' => now(),
                ])->save();
            }

            Auth::login($existingGoogleUser, true);

            return redirect()->to(PostLoginRedirector::redirectPath($existingGoogleUser));
        }

        $existingEmailUser = User::query()->where('email', $email)->first();

        if ($existingEmailUser !== null) {
            $existingEmailUser->forceFill([
                'google_id' => $googleId,
                'email_verified_at' => $existingEmailUser->email_verified_at ?? now(),
            ])->save();

            Auth::login($existingEmailUser, true);

            return redirect()->to(PostLoginRedirector::redirectPath($existingEmailUser));
        }

        $newUser = User::query()->create([
            'name' => $name,
            'email' => $email,
            'google_id' => $googleId,
            'email_verified_at' => now(),
            'password' => Hash::make(Str::random(40)),
            'username' => UsernameGenerator::generateUnique($name),
            'role' => UserRole::Student->value,
            'onboarding_completed' => false,
        ]);

        Auth::login($newUser, true);

        return redirect()->to('/onboarding');
    }
}
