<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(): Response
    {
        $search = trim((string) request()->string('search'));

        $courses = Course::query()
            ->with(['university'])
            ->withCount('courseYears')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('short_name', 'like', "%{$search}%")
                        ->orWhere('shortcode', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses,
            'search' => $search,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Courses/Create', [
            'universities' => University::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        Course::query()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.courses.index')->with('success', 'Program created.');
    }

    public function edit(Course $course): Response
    {
        $course->load([
            'university',
            'courseYears' => function ($query): void {
                $query->with('admin')->orderByDesc('year');
            },
        ]);

        return Inertia::render('Admin/Courses/Edit', [
            'course' => $course,
            'universities' => University::query()->orderBy('name')->get(['id', 'name']),
            'admins' => User::query()->where('role', 'admin')->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $course->update($request->validated());

        return redirect()->route('admin.courses.index')->with('success', 'Program updated.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Program deleted.');
    }
}
