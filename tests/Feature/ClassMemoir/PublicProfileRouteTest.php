<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicProfileRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_is_available_with_at_prefixed_username_url(): void
    {
        $user = User::factory()->create([
            'username' => 'class_user_100',
        ]);

        $this->get('/@'.$user->username)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profiles/Show')
                ->where('profile.username', $user->username)
            );
    }
}
