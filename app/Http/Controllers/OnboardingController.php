<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\OnboardingCompleteRequest;
use App\Models\Course;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->onboarding_completed) {
            if ($user->role === UserRole::Superadmin) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('dashboard');
        }

        $search = trim((string) $request->string('search'));
        $selectedUniversityId = $request->integer('university_id') ?: null;

        $universities = University::query()
            ->with('media')
            ->withCount('courses')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->get();

        $courses = $selectedUniversityId === null
            ? collect()
            : Course::query()
                ->where('university_id', $selectedUniversityId)
                ->orderBy('name')
                ->get();

        return Inertia::render('Onboarding/Index', [
            'universities' => $universities,
            'courses' => $courses,
            'search' => $search,
            'selectedUniversityId' => $selectedUniversityId,
        ]);
    }

    public function store(OnboardingCompleteRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $course = Course::query()
            ->whereKey($validated['course_id'])
            ->where('university_id', $validated['university_id'])
            ->first();

        if ($course === null) {
            throw ValidationException::withMessages([
                'course_id' => 'Selected course does not belong to the selected university.',
            ]);
        }

        DB::transaction(function () use ($request, $user, $validated): void {
            $user->forceFill([
                'university_id' => $validated['university_id'],
                'course_id' => $validated['course_id'],
                'username' => $validated['username'],
                'username_updated_at' => now(),
                'onboarding_completed' => true,
            ])->save();

            if ($request->hasFile('avatar')) {
                $user->clearMediaCollection('avatar');
                $user->addMediaFromRequest('avatar')
                    ->withCustomProperties([
                        'owner_username' => ltrim((string) $user->username, '@'),
                    ])
                    ->toMediaCollection('avatar');
            }
        });

        return redirect()->route('dashboard')->with(
            'success',
            'Onboarding completed. You can now complete your profile and add your 12 memories.'
        );
    }
}
