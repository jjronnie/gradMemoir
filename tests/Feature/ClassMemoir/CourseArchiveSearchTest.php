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

        $this->get('/course/csc-class-of-2026?view=grid&search=alpha')
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

    public function test_course_archive_view_query_is_exposed_and_sanitized(): void
    {
        $course = Course::factory()->create([
            'short_name' => 'MKT',
        ]);
        CourseYear::factory()->create([
            'course_id' => $course->id,
            'year' => '2026',
        ]);

        $this->get('/course/mkt-class-of-2026?view=book')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Show')
                ->where('view', 'book')
            );

        $this->get('/course/mkt-class-of-2026?view=invalid')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Show')
                ->where('view', 'book')
            );

        $this->get('/course/mkt-class-of-2026')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Show')
                ->where('view', 'book')
            );
    }

    public function test_course_archive_book_view_ignores_search_filter(): void
    {
        $course = Course::factory()->create([
            'short_name' => 'MKT',
        ]);
        $courseYear = CourseYear::factory()->create([
            'course_id' => $course->id,
            'year' => '2026',
        ]);

        User::factory()->create([
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

        $this->get('/course/mkt-class-of-2026?view=book&search=alpha')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Show')
                ->where('view', 'book')
                ->where('search', '')
                ->has('students.data', 2)
            );
    }
}
