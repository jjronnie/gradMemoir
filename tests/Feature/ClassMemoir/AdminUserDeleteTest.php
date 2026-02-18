<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_delete_user(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $victim = User::factory()->create();

        $this->actingAs($superadmin)
            ->delete("/admin/users/{$victim->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('users', [
            'id' => $victim->id,
        ]);
    }

    public function test_superadmin_cannot_delete_self(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($superadmin)
            ->delete("/admin/users/{$superadmin->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $superadmin->id,
        ]);
    }
}
