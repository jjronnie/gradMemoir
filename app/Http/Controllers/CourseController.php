<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function show(string $shortName): Response
    {
        $course = Course::findByShortNameSlug($shortName);

        abort_if($course === null, 404);

        $course->load([
            'university',
            'university.media',
            'courseYears' => function ($query): void {
                $query->withCount(['users as active_students_count' => function ($userQuery): void {
                    $userQuery->where('status', 'active');
                }])->orderByDesc('year');
            },
        ]);

        $logo = $course->university?->getFirstMediaUrl('logo', 'full');

        return Inertia::render('Courses/Overview', [
            'course' => $course,
            'courseYears' => $course->courseYears,
            'seo' => [
                'title' => $course->name.' - '.config('app.name'),
                'description' => "Explore cohorts for {$course->name}.",
                'image' => $logo !== '' ? $logo : url('/featured.webp'),
                'type' => 'website',
            ],
        ]);
    }
}
