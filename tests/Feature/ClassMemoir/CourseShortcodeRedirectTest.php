<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\Course;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseShortcodeRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_shortcode_route_redirects_to_course_slug_route(): void
    {
        $creator = User::factory()->superadmin()->create();
        $university = University::factory()->create([
            'created_by' => $creator->id,
        ]);
        $course = Course::factory()->create([
            'university_id' => $university->id,
            'created_by' => $creator->id,
            'shortcode' => 'course123',
        ]);

        $response = $this->get('/c/course123');

        $response->assertRedirect(route('courses.show', ['slug' => $course->slug]));
    }
}
