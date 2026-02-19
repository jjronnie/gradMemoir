<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class CourseArchiveController extends Controller
{
    public function show(string $slug): RedirectResponse
    {
        if (preg_match('/^(?<short>[a-z0-9-]+)-class-of-(?<year>\d{4})$/', $slug, $matches) === 1) {
            return redirect()->route('course-years.show', [
                'shortName' => $matches['short'],
                'year' => $matches['year'],
            ]);
        }

        return redirect()->route('courses.overview', [
            'shortName' => $slug,
        ]);
    }
}
