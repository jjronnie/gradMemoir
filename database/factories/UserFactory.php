<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $username = Str::of(fake()->unique()->userName())
            ->replace('.', '_')
            ->replace('-', '_')
            ->replaceMatches('/[^a-zA-Z0-9_]/', '')
            ->lower()
            ->value();

        return [
            'name' => fake()->name(),
            'nickname' => null,
            'email' => fake()->unique()->safeEmail(),
            'google_id' => null,
            'username' => $username,
            'username_updated_at' => null,
            'role' => UserRole::Student->value,
            'status' => UserStatus::Active->value,
            'is_verified' => false,
            'bio' => null,
            'profession' => null,
            'quote' => null,
            'location' => null,
            'phone' => null,
            'facebook_username' => null,
            'x_username' => null,
            'tiktok_username' => null,
            'instagram_username' => null,
            'website' => null,
            'email_public' => null,
            'onboarding_completed' => true,
            'university_id' => null,
            'course_id' => null,
            'course_year_id' => null,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin->value,
        ]);
    }

    public function superadmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Superadmin->value,
        ]);
    }
}
