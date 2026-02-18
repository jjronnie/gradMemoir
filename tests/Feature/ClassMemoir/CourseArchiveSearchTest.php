<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\Course;
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
            'slug' => 'bsc-computer-science',
        ]);

        $matchingStudent = User::factory()->create([
            'name' => 'Alpha Search',
            'username' => 'alpha_search',
            'course_id' => $course->id,
            'university_id' => $course->university_id,
        ]);

        User::factory()->create([
            'name' => 'Beta Student',
            'username' => 'beta_student',
            'course_id' => $course->id,
            'university_id' => $course->university_id,
        ]);

        $this->get('/courses/'.$course->slug.'?search=alpha')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Show')
                ->where('search', 'alpha')
                ->has('students.data', 1)
                ->where('students.data.0.id', $matchingStudent->id)
            );
    }
}
