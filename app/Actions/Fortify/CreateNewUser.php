<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use App\Support\TurnstileVerifier;
use App\Support\UsernameGenerator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'turnstile_token' => ['nullable', 'string'],
        ])->validate();

        if (! TurnstileVerifier::verify((string) ($input['turnstile_token'] ?? ''))) {
            throw ValidationException::withMessages([
                'turnstile' => 'Turnstile verification failed. Please try again.',
            ]);
        }

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'username' => UsernameGenerator::generateUnique($input['name']),
            'role' => UserRole::Student->value,
            'onboarding_completed' => false,
        ]);
    }
}
