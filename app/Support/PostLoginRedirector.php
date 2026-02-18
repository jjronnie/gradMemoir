<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;

class PostLoginRedirector
{
    public static function redirectPath(User $user): string
    {
        if ($user->status !== UserStatus::Active) {
            return '/account-suspended';
        }

        if ($user->role === UserRole::Superadmin) {
            return '/admin/dashboard';
        }

        if (! $user->onboarding_completed) {
            return '/onboarding';
        }

        return '/dashboard';
    }
}
