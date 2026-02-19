<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\Course;
use App\Models\CourseYear;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UniversityProgramListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_university_page_lists_programs_and_program_page_lists_cohorts(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $university = University::factory()->create([
            'slug' => 'memoir-university',
            'created_by' => $superadmin->id,
        ]);
        $course = Course::factory()->create([
            'name' => 'Computer Science',
            'short_name' => 'CSC',
            'university_id' => $university->id,
            'created_by' => $superadmin->id,
        ]);
        Course::factory()->create([
            'name' => 'Mass Communication',
            'short_name' => 'MAC',
            'university_id' => $university->id,
            'created_by' => $superadmin->id,
        ]);
        CourseYear::factory()->create([
            'course_id' => $course->id,
            'year' => '2025',
        ]);
        CourseYear::factory()->create([
            'course_id' => $course->id,
            'year' => '2026',
        ]);

        $this->get('/universities/memoir-university')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Universities/Show')
                ->has('courses', 2)
                ->where('courses.0.name', 'Computer Science')
            );

        $this->get('/course/csc')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Overview')
                ->where('course.name', 'Computer Science')
                ->has('courseYears', 2)
            );
    }
}
