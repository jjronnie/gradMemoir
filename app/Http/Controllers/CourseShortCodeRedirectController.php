<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Support\CourseYearSlugGenerator;
use Illuminate\Http\RedirectResponse;

class CourseShortCodeRedirectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $shortcode): RedirectResponse
    {
        $course = Course::query()->where('shortcode', $shortcode)->firstOrFail();

        return redirect()->route('courses.overview', [
            'shortName' => CourseYearSlugGenerator::sanitizeShortName((string) $course->short_name),
        ]);
    }
}
