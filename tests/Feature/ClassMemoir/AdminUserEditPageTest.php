<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\Course;
use App\Models\CourseYear;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminUserEditPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_view_user_edit_page(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $university = University::factory()->create([
            'created_by' => $superadmin->id,
        ]);
        $course = Course::factory()->create([
            'university_id' => $university->id,
            'created_by' => $superadmin->id,
        ]);
        CourseYear::factory()->create([
            'course_id' => $course->id,
            'year' => '2026',
        ]);
        $managedUser = User::factory()->create();

        $this->actingAs($superadmin)
            ->get("/admin/users/{$managedUser->id}/edit")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Edit')
                ->where('user.id', $managedUser->id)
                ->where('currentUserId', $superadmin->id)
                ->has('universities', 1)
                ->has('courses', 1)
                ->has('courseYears', 1)
            );
    }

    public function test_student_cannot_access_admin_user_edit_page(): void
    {
        $student = User::factory()->create();
        $managedUser = User::factory()->create();

        $this->actingAs($student)
            ->get("/admin/users/{$managedUser->id}/edit")
            ->assertForbidden();
    }
}
