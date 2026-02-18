<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_google_user_is_forced_verified_on_callback(): void
    {
        $user = User::factory()->create([
            'google_id' => 'google-123',
            'email' => 'google-existing@example.com',
            'email_verified_at' => null,
        ]);

        Socialite::shouldReceive('driver->user')->once()->andReturn(
            $this->fakeGoogleUser(
                id: 'google-123',
                name: 'Existing Google User',
                email: 'google-existing@example.com',
            ),
        );

        $response = $this->get(route('google.callback'));

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_existing_email_user_is_linked_and_forced_verified_on_callback(): void
    {
        $user = User::factory()->create([
            'google_id' => null,
            'email' => 'email-user@example.com',
            'email_verified_at' => null,
        ]);

        Socialite::shouldReceive('driver->user')->once()->andReturn(
            $this->fakeGoogleUser(
                id: 'google-999',
                name: 'Email User',
                email: 'email-user@example.com',
            ),
        );

        $response = $this->get(route('google.callback'));

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-999', $user->fresh()->google_id);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    private function fakeGoogleUser(string $id, string $name, string $email): object
    {
        return new class($id, $name, $email)
        {
            public function __construct(
                private readonly string $id,
                private readonly string $name,
                private readonly string $email
            ) {}

            public function getId(): string
            {
                return $this->id;
            }

            public function getName(): string
            {
                return $this->name;
            }

            public function getNickname(): string
            {
                return $this->name;
            }

            public function getEmail(): string
            {
                return $this->email;
            }
        };
    }
}
