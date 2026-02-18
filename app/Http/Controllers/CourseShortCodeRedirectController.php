<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseShortCodeRedirectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $shortcode): RedirectResponse
    {
        $course = Course::query()->where('shortcode', $shortcode)->firstOrFail();

        return redirect()->route('courses.show', ['slug' => $course->slug]);
    }
}
