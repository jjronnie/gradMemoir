<?php

namespace App\Http\Controllers;

use App\Models\CourseYear;
use App\Support\CourseYearSlugGenerator;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CourseYearController extends Controller
{
    public function show(Request $request, string $shortName, string $year): InertiaResponse
    {
        $courseYear = $this->resolveCourseYear($shortName, $year);

        abort_if($courseYear === null, 404);

        return Inertia::render('Courses/Show', $this->buildPageProps($request, $courseYear, false));
    }

    public function print(Request $request, string $shortName, string $year): InertiaResponse
    {
        $courseYear = $this->resolveCourseYear($shortName, $year);

        abort_if($courseYear === null, 404);

        $this->authorize('exportYearbook', $courseYear);

        return Inertia::render('Courses/Show', $this->buildPageProps($request, $courseYear, true));
    }

    private function resolveCourseYear(string $shortName, string $year): ?CourseYear
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

        return $courseYear;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPageProps(Request $request, CourseYear $courseYear, bool $forcePrintOnly): array
    {
        $search = trim((string) $request->string('search'));
        $view = (string) $request->string('view');
        $view = in_array($view, ['grid', 'book'], true) ? $view : 'book';
        $printOnly = $forcePrintOnly || $request->boolean('print');
        $activeSearch = $view === 'grid' && ! $printOnly ? $search : '';

        $page = max((int) $request->integer('page', 1), 1);
        $cacheVersionKey = "course-year:{$courseYear->id}:students:version";
        $cacheVersion = (int) Cache::get($cacheVersionKey, 1);
        $searchHash = md5(mb_strtolower($activeSearch));
        $cacheKey = "course-year:{$courseYear->id}:students:v{$cacheVersion}:search:{$searchHash}:page:{$page}";
        $printCacheKey = "course-year:{$courseYear->id}:students:v{$cacheVersion}:search:{$searchHash}:print:all";

        $students = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($courseYear, $activeSearch) {
            return $this->studentsQuery($courseYear, $activeSearch)
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString();
        });

        $printStudents = Cache::remember($printCacheKey, now()->addMinutes(15), function () use ($courseYear, $activeSearch) {
            return $this->studentsQuery($courseYear, $activeSearch)
                ->orderBy('name')
                ->get()
                ->values();
        });

        $courseImage = $courseYear->course?->university?->getFirstMediaUrl('logo', 'full');

        return [
            'courseYear' => $courseYear,
            'students' => $students,
            'printStudents' => $printStudents,
            'search' => $activeSearch,
            'view' => $view,
            'printOnly' => $printOnly,
            'seo' => [
                'title' => "{$courseYear->course?->name} Class of {$courseYear->year} - ".config('app.name'),
                'description' => "Browse {$courseYear->course?->name} Class of {$courseYear->year} memories and student profiles.",
                'image' => $courseImage !== '' ? $courseImage : url('/featured.webp'),
                'type' => 'website',
            ],
        ];
    }

    private function studentsQuery(CourseYear $courseYear, string $search): HasMany
    {
        return $courseYear->users()
            ->where('status', 'active')
            ->with('media')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            });
    }
}
