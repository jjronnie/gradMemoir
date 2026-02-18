<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseArchiveController extends Controller
{
    public function show(Request $request, string $slug): Response
    {
        $course = Course::query()
            ->with(['university', 'university.media'])
            ->where('slug', $slug)
            ->firstOrFail();

        $search = trim((string) $request->string('search'));

        $students = $course->students()
            ->where('status', 'active')
            ->with('media')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $courseImage = $course->university?->getFirstMediaUrl('logo', 'full');

        return Inertia::render('Courses/Show', [
            'course' => $course,
            'students' => $students,
            'search' => $search,
            'seo' => [
                'title' => $course->name.' - '.config('app.name'),
                'description' => "Browse {$course->name} class memories and student profiles.",
                'image' => $courseImage !== '' ? $courseImage : url('/featured.webp'),
                'type' => 'website',
            ],
        ]);
    }
}
