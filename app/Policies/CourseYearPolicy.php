<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\CourseYear;
use App\Models\User;

class CourseYearPolicy
{
    public function view(User $user, CourseYear $courseYear): bool
    {
        if ($user->role === UserRole::Superadmin) {
            return true;
        }

        return $courseYear->admin_id === $user->id;
    }

    public function manageMembers(User $user, CourseYear $courseYear): bool
    {
        if ($user->role === UserRole::Superadmin) {
            return true;
        }

        return $user->role === UserRole::Admin && $courseYear->admin_id === $user->id;
    }

    public function assignAdmin(User $user): bool
    {
        return $user->role === UserRole::Superadmin;
    }
}
