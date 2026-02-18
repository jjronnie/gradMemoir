<?php

namespace Tests\Feature\ClassMemoir;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SuperadminUserModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_update_another_users_status_and_role(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $targetUser = User::factory()->create();

        $statusResponse = $this->actingAs($superadmin)->put(
            route('admin.users.status', $targetUser),
            ['status' => UserStatus::Suspended->value],
        );

        $statusResponse
            ->assertRedirect()
            ->assertSessionHas('success', 'User status updated.');

        $this->assertSame(
            UserStatus::Suspended->value,
            $targetUser->fresh()->status->value,
        );

        $roleResponse = $this->actingAs($superadmin)->put(
            route('admin.users.role', $targetUser),
            ['role' => UserRole::Admin->value],
        );

        $roleResponse
            ->assertRedirect()
            ->assertSessionHas('success', 'User role updated.');

        $this->assertSame(
            UserRole::Admin->value,
            $targetUser->fresh()->role->value,
        );
    }

    public function test_superadmin_cannot_update_own_status_or_role(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $statusResponse = $this->actingAs($superadmin)->put(
            route('admin.users.status', $superadmin),
            ['status' => UserStatus::Suspended->value],
        );

        $statusResponse
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                'You cannot update your own account status.',
            );

        $this->assertSame(
            UserStatus::Active->value,
            $superadmin->fresh()->status->value,
        );

        $roleResponse = $this->actingAs($superadmin)->put(
            route('admin.users.role', $superadmin),
            ['role' => UserRole::Student->value],
        );

        $roleResponse
            ->assertRedirect()
            ->assertSessionHas('error', 'You cannot update your own role.');

        $this->assertSame(
            UserRole::Superadmin->value,
            $superadmin->fresh()->role->value,
        );
    }

    public function test_superadmin_can_update_user_details_including_verification_fields(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $targetUser = User::factory()->unverified()->create([
            'website' => null,
            'is_verified' => false,
        ]);
        $verifiedAt = Carbon::parse('2026-02-18 10:15:00');

        $response = $this->actingAs($superadmin)->put(
            route('admin.users.update', $targetUser),
            [
                'name' => 'Updated User Name',
                'email' => 'updated-user@example.com',
                'username' => 'updated_user',
                'website' => 'portfolio.example.com',
                'is_verified' => true,
                'email_verified_at' => $verifiedAt->toDateTimeString(),
            ],
        );

        $response
            ->assertRedirect()
            ->assertSessionHas('success', 'User details updated.');

        $freshUser = $targetUser->fresh();

        $this->assertSame('Updated User Name', $freshUser->name);
        $this->assertSame('updated-user@example.com', $freshUser->email);
        $this->assertSame('updated_user', $freshUser->username);
        $this->assertSame('portfolio.example.com', $freshUser->website);
        $this->assertTrue((bool) $freshUser->is_verified);
        $this->assertTrue($freshUser->email_verified_at?->equalTo($verifiedAt));
    }
}
