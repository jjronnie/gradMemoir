<?php

namespace Tests\Feature\ClassMemoir;

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
        $managedUser = User::factory()->create();

        $this->actingAs($superadmin)
            ->get("/admin/users/{$managedUser->id}/edit")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Edit')
                ->where('user.id', $managedUser->id)
                ->where('currentUserId', $superadmin->id)
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
