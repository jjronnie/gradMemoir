<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use App\Support\UsernameGenerator;
use Illuminate\Support\Facades\Validator;
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
        $rules = [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ];

        if (app()->environment('production')) {
            $rules['cf-turnstile-response'] = ['required', 'turnstile'];
        }

        Validator::make($input, $rules, [
            'cf-turnstile-response.required' => 'Turnstile verification is required.',
        ])->validate();

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
