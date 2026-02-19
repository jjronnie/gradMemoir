<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use RyanChandler\LaravelCloudflareTurnstile\Facades\Turnstile;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/Register')
            );
    }

    public function test_new_users_can_register()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/onboarding');

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();

        $this->assertNotNull($user->username);
        $this->assertSame('student', $user->role->value);
        $this->assertFalse($user->onboarding_completed);
    }

    public function test_turnstile_is_required_for_registration_in_production(): void
    {
        $this->app['env'] = 'production';
        $this->withoutMiddleware(ValidateCsrfToken::class);
        Turnstile::fake();

        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'StrongPass123!@#',
            'password_confirmation' => 'StrongPass123!@#',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('cf-turnstile-response');
        $this->assertGuest();
    }

    public function test_new_users_can_register_in_production_with_turnstile(): void
    {
        $this->app['env'] = 'production';
        $this->withoutMiddleware(ValidateCsrfToken::class);
        Turnstile::fake();

        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'StrongPass123!@#',
            'password_confirmation' => 'StrongPass123!@#',
            'cf-turnstile-response' => Turnstile::dummy(),
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/onboarding');
    }
}
