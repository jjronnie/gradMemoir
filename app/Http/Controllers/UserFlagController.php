<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\FlagUserRequest;
use App\Models\CourseYear;
use App\Models\User;
use App\Models\UserFlag;
use Illuminate\Http\RedirectResponse;

class UserFlagController extends Controller
{
    public function store(FlagUserRequest $request, User $user): RedirectResponse
    {
        $flaggedBy = $request->user();

        if ($flaggedBy?->role === UserRole::Admin) {
            $managedCourseYearId = CourseYear::query()
                ->where('admin_id', $flaggedBy->id)
                ->value('id');

            if ($managedCourseYearId === null || (int) $user->course_year_id !== (int) $managedCourseYearId) {
                abort(403);
            }
        }

        UserFlag::query()->create([
            'user_id' => $user->id,
            'flagged_by' => $flaggedBy?->id,
            'reason' => $request->string('reason')->value(),
            'reviewed' => false,
        ]);

        return back()->with('success', 'User flagged for review.');
    }
}
