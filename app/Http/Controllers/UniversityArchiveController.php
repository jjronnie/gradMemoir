<?php

namespace App\Http\Controllers;

use App\Models\University;
use App\Support\CourseYearSlugGenerator;
use Inertia\Inertia;
use Inertia\Response;

class UniversityArchiveController extends Controller
{
    public function index(): Response
    {
        $universities = University::query()
            ->with('media')
            ->withCount('courses')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Universities/Index', [
            'universities' => $universities,
            'seo' => [
                'title' => 'Universities - '.config('app.name'),
                'description' => 'Discover universities and class archives.',
                'image' => url('/featured.webp'),
                'type' => 'website',
            ],
        ]);
    }

    public function show(string $slug): Response
    {
        $university = University::query()
            ->with('media')
            ->where('slug', $slug)
            ->firstOrFail();

        $courses = $university->courses()
            ->withCount([
                'students as active_students_count' => function ($query): void {
                    $query->where('status', 'active');
                },
                'courseYears as cohorts_count',
            ])
            ->orderBy('name')
            ->get()
            ->map(function ($course) {
                $course->route_slug = CourseYearSlugGenerator::sanitizeShortName((string) $course->short_name);

                return $course;
            });

        $logo = $university->getFirstMediaUrl('logo', 'full');

        return Inertia::render('Universities/Show', [
            'university' => $university,
            'courses' => $courses,
            'seo' => [
                'title' => $university->name.' - '.config('app.name'),
                'description' => "Explore programs and cohorts for {$university->name}.",
                'image' => $logo !== '' ? $logo : url('/featured.webp'),
                'type' => 'website',
            ],
        ]);
    }
}
