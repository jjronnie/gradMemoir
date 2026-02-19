<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\Course;
use App\Models\CourseYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CourseArchiveSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_archive_search_filters_students_by_name_or_username(): void
    {
        $course = Course::factory()->create([
            'short_name' => 'CSC',
        ]);
        $courseYear = CourseYear::factory()->create([
            'course_id' => $course->id,
            'year' => '2026',
        ]);

        $matchingStudent = User::factory()->create([
            'name' => 'Alpha Search',
            'username' => 'alpha_search',
            'course_id' => $course->id,
            'course_year_id' => $courseYear->id,
            'university_id' => $course->university_id,
        ]);

        User::factory()->create([
            'name' => 'Beta Student',
            'username' => 'beta_student',
            'course_id' => $course->id,
            'course_year_id' => $courseYear->id,
            'university_id' => $course->university_id,
        ]);

        $this->get('/course/csc-class-of-2026?search=alpha')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Show')
                ->where('search', 'alpha')
                ->where('courseYear.id', $courseYear->id)
                ->has('students.data', 1)
                ->where('students.data.0.id', $matchingStudent->id)
            );
    }

    public function test_public_course_year_page_only_lists_active_students(): void
    {
        $course = Course::factory()->create([
            'short_name' => 'BIO',
        ]);
        $courseYear = CourseYear::factory()->create([
            'course_id' => $course->id,
            'year' => '2027',
        ]);

        $activeStudent = User::factory()->create([
            'status' => 'active',
            'course_id' => $course->id,
            'course_year_id' => $courseYear->id,
        ]);

        User::factory()->create([
            'status' => 'banned',
            'course_id' => $course->id,
            'course_year_id' => $courseYear->id,
        ]);

        $this->get('/course/bio-class-of-2027')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Show')
                ->has('students.data', 1)
                ->where('students.data.0.id', $activeStudent->id)
            );
    }
}
