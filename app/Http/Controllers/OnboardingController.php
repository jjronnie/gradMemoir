<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\OnboardingCompleteRequest;
use App\Models\Course;
use App\Models\CourseYear;
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
        $selectedCourseId = $request->integer('course_id') ?: null;
        $selectedCourseYearId = $request->integer('course_year_id') ?: null;

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

        $courseYears = $selectedCourseId === null
            ? collect()
            : CourseYear::query()
                ->select(['id', 'year', 'slug', 'course_id'])
                ->where('course_id', $selectedCourseId)
                ->when($selectedUniversityId !== null, function ($query) use ($selectedUniversityId): void {
                    $query->whereHas('course', function ($courseQuery) use ($selectedUniversityId): void {
                        $courseQuery->where('university_id', $selectedUniversityId);
                    });
                })
                ->orderByDesc('year')
                ->get();

        return Inertia::render('Onboarding/Index', [
            'universities' => $universities,
            'courses' => $courses,
            'courseYears' => $courseYears,
            'search' => $search,
            'selectedUniversityId' => $selectedUniversityId,
            'selectedCourseId' => $selectedCourseId,
            'selectedCourseYearId' => $selectedCourseYearId,
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

        $courseYear = CourseYear::query()
            ->whereKey($validated['course_year_id'])
            ->where('course_id', $course->id)
            ->first();

        if ($courseYear === null) {
            throw ValidationException::withMessages([
                'course_year_id' => 'Selected cohort does not belong to the selected program.',
            ]);
        }

        DB::transaction(function () use ($request, $user, $validated, $course, $courseYear): void {

            $user->forceFill([
                'university_id' => $validated['university_id'],
                'course_id' => $course->id,
                'course_year_id' => $courseYear->id,
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
            'Onboarding completed. You can now complete your profile and add your memories.'
        );
    }
}
