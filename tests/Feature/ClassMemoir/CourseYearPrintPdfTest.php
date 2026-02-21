<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\Course;
use App\Models\CourseYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CourseYearPrintPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_print_route_requires_authentication(): void
    {
        $course = Course::factory()->create([
            'short_name' => 'CSC',
        ]);

        CourseYear::factory()->create([
            'course_id' => $course->id,
            'year' => '2026',
        ]);

        $this->get('/course/csc-class-of-2026/print')
            ->assertRedirect('/login');
    }

    public function test_superadmin_can_open_print_only_view(): void
    {
        $course = Course::factory()->create([
            'short_name' => 'CSC',
        ]);

        $courseYear = CourseYear::factory()->create([
            'course_id' => $course->id,
            'year' => '2026',
        ]);

        $superadmin = User::factory()->superadmin()->create();

        User::factory()->create([
            'course_id' => $course->id,
            'course_year_id' => $courseYear->id,
            'university_id' => $course->university_id,
        ]);

        $this->actingAs($superadmin)
            ->get('/course/csc-class-of-2026/print')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Show')
                ->where('printOnly', true)
                ->has('printStudents', 1)
            );
    }

    public function test_non_superadmin_cannot_open_print_only_view(): void
    {
        $course = Course::factory()->create([
            'short_name' => 'CSC',
        ]);

        CourseYear::factory()->create([
            'course_id' => $course->id,
            'year' => '2026',
        ]);

        $student = User::factory()->create();

        $this->actingAs($student)
            ->get('/course/csc-class-of-2026/print')
            ->assertForbidden();
    }
}
