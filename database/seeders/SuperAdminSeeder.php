<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\UsernameGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = config('classmemoir.superadmin_email');
        $password = config('classmemoir.superadmin_password');

        if (! is_string($email) || $email === '' || ! is_string($password) || $password === '') {
            return;
        }

        $existingUser = User::query()->where('email', $email)->first();
        $username = $existingUser?->username ?? UsernameGenerator::generateUnique('super_admin', $existingUser?->id);

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'JRonnie',
                'username' => $username,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'role' => UserRole::Superadmin->value,
                'onboarding_completed' => true,
            ],
        );
    }
}
