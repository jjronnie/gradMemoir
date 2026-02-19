<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsernameAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_check_username_availability(): void
    {
        $user = User::factory()->create();
        User::factory()->create([
            'username' => 'taken_name',
        ]);

        $takenResponse = $this
            ->actingAs($user)
            ->postJson(route('api.username.check'), [
                'username' => 'taken_name',
            ]);

        $takenResponse
            ->assertOk()
            ->assertJsonPath('data.available', false);

        $availableResponse = $this
            ->actingAs($user)
            ->postJson(route('api.username.check'), [
                'username' => 'new_name',
            ]);

        $availableResponse
            ->assertOk()
            ->assertJsonPath('data.available', true);
    }

    public function test_guest_user_cannot_check_username_availability(): void
    {
        $response = $this->postJson(route('api.username.check'), [
            'username' => 'new_name',
        ]);

        $response->assertStatus(401);
    }

    public function test_username_availability_check_normalizes_input_before_lookup(): void
    {
        $user = User::factory()->create();
        User::factory()->create([
            'username' => 'john_doe',
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson(route('api.username.check'), [
                'username' => '@John   Doe!!',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.username', 'john_doe')
            ->assertJsonPath('data.available', false);
    }
}
