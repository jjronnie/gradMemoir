<?php

namespace App\Http\Controllers;

use App\Models\CourseYear;
use App\Support\CourseYearSlugGenerator;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseYearController extends Controller
{
    public function show(Request $request, string $shortName, string $year): Response
    {
        $slug = CourseYearSlugGenerator::fromShortNameAndYear($shortName, $year);

        $courseYear = CourseYear::query()
            ->with(['course.university', 'course.university.media'])
            ->where('slug', $slug)
            ->first();

        if ($courseYear === null) {
            $normalizedShortName = CourseYearSlugGenerator::sanitizeShortName($shortName);

            $courseYear = CourseYear::query()
                ->with(['course.university', 'course.university.media'])
                ->where('year', $year)
                ->get()
                ->first(function (CourseYear $candidate) use ($normalizedShortName): bool {
                    return CourseYearSlugGenerator::sanitizeShortName((string) $candidate->course?->short_name) === $normalizedShortName;
                });
        }

        abort_if($courseYear === null, 404);

        $search = trim((string) $request->string('search'));

        $students = $courseYear->users()
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

        $courseImage = $courseYear->course?->university?->getFirstMediaUrl('logo', 'full');

        return Inertia::render('Courses/Show', [
            'courseYear' => $courseYear,
            'students' => $students,
            'search' => $search,
            'seo' => [
                'title' => "{$courseYear->course?->name} Class of {$courseYear->year} - ".config('app.name'),
                'description' => "Browse {$courseYear->course?->name} Class of {$courseYear->year} memories and student profiles.",
                'image' => $courseImage !== '' ? $courseImage : url('/featured.webp'),
                'type' => 'website',
            ],
        ]);
    }
}
