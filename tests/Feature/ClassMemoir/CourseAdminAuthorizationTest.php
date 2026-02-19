<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\Course;
use App\Models\CourseYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CourseAdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_only_manages_members_in_assigned_cohort(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create([
            'short_name' => 'CSE',
        ]);
        $assignedCourseYear = CourseYear::factory()->create([
            'course_id' => $course->id,
            'year' => '2026',
            'admin_id' => $admin->id,
        ]);
        $otherCourseYear = CourseYear::factory()->create([
            'course_id' => $course->id,
            'year' => '2027',
            'admin_id' => null,
        ]);

        $managedMember = User::factory()->create([
            'course_id' => $course->id,
            'course_year_id' => $assignedCourseYear->id,
        ]);

        $outsideMember = User::factory()->create([
            'course_id' => $course->id,
            'course_year_id' => $otherCourseYear->id,
        ]);

        $this->actingAs($admin)
            ->get('/course-admin')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('CourseAdmin/Index')
                ->where('courseYear.id', $assignedCourseYear->id)
                ->has('members.data', 1)
                ->where('members.data.0.id', $managedMember->id)
            );

        $this->actingAs($admin)
            ->post("/users/{$outsideMember->id}/flag", [
                'reason' => 'This user is outside my assigned cohort scope.',
            ])
            ->assertForbidden();
    }
}
