<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCourseDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_delete_course(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $course = Course::factory()->create();

        $this->actingAs($superadmin)
            ->delete("/admin/courses/{$course->id}")
            ->assertRedirect(route('admin.courses.index'));

        $this->assertDatabaseMissing('courses', [
            'id' => $course->id,
        ]);
    }

    public function test_student_cannot_delete_course(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        $this->actingAs($student)
            ->delete("/admin/courses/{$course->id}")
            ->assertForbidden();
    }
}
