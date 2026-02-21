<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\UpdateAvatarRequest;
use App\Http\Requests\UpdateProfileSettingsRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'canUpdateUsernameAt' => optional($request->user()->username_updated_at)?->addDays(30),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(UpdateProfileSettingsRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $isUsernameDirty = isset($validated['username']) && $validated['username'] !== $user->username;

        if (
            $isUsernameDirty
            && $user->username_updated_at !== null
            && $user->username_updated_at->gt(now()->subDays(30))
        ) {
            $nextDate = $user->username_updated_at->copy()->addDays(30)->toDateString();

            throw ValidationException::withMessages([
                'username' => "You can only change your username once every 30 days. Next change available on {$nextDate}.",
            ]);
        }

        $user->fill($validated);

        if ($isUsernameDirty) {
            $user->username_updated_at = now();
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('profile.edit')->with('success', 'Profile updated successfully.');
    }

    public function updateAvatar(UpdateAvatarRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->clearMediaCollection('avatar');
        $user->addMediaFromRequest('avatar')
            ->withCustomProperties([
                'owner_username' => ltrim((string) $user->username, '@'),
            ])
            ->toMediaCollection('avatar');
        $user->touch();

        return to_route('profile.edit')->with('success', 'Profile photo updated.');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
