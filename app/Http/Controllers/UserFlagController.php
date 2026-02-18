<?php

namespace App\Http\Controllers;

use App\Http\Requests\FlagUserRequest;
use App\Models\User;
use App\Models\UserFlag;
use Illuminate\Http\RedirectResponse;

class UserFlagController extends Controller
{
    public function store(FlagUserRequest $request, User $user): RedirectResponse
    {
        UserFlag::query()->create([
            'user_id' => $user->id,
            'flagged_by' => $request->user()->id,
            'reason' => $request->string('reason')->value(),
            'reviewed' => false,
        ]);

        return back()->with('success', 'User flagged for review.');
    }
}
