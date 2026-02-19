<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignCourseYearAdminRequest;
use App\Http\Requests\StoreCourseYearRequest;
use App\Models\Course;
use App\Models\CourseYear;
use Illuminate\Http\RedirectResponse;

class CourseYearController extends Controller
{
    public function store(StoreCourseYearRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('assignAdmin', CourseYear::class);

        CourseYear::query()->create([
            'course_id' => $course->id,
            'year' => (string) $request->validated('year'),
            'admin_id' => $request->validated('admin_id'),
        ]);

        return back()->with('success', 'Cohort created.');
    }

    public function updateAdmin(AssignCourseYearAdminRequest $request, CourseYear $courseYear): RedirectResponse
    {
        $this->authorize('assignAdmin', CourseYear::class);

        $courseYear->update([
            'admin_id' => $request->validated('admin_id'),
        ]);

        return back()->with('success', 'Cohort admin updated.');
    }
}
