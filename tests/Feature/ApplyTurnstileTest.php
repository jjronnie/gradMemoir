<?php

namespace Tests\Feature;

use App\Jobs\SendApplicationNotification;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use RyanChandler\LaravelCloudflareTurnstile\Facades\Turnstile;
use Tests\TestCase;

class ApplyTurnstileTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_submission_works_in_local_without_turnstile(): void
    {
        Queue::fake();

        $response = $this->post(route('apply.store'), [
            'applicant_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '15551234567',
            'university_name' => 'Sample University',
            'course_name' => 'Computer Science',
            'year' => '2026',
            'notes' => 'Please add this course.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('course_applications', [
            'email' => 'jane@example.com',
            'course_name' => 'Computer Science',
        ]);
        Queue::assertPushed(SendApplicationNotification::class);
    }

    public function test_turnstile_is_required_for_apply_in_production(): void
    {
        $this->app['env'] = 'production';
        $this->withoutMiddleware(ValidateCsrfToken::class);
        Turnstile::fake();
        Queue::fake();

        $response = $this->from(route('apply.create'))->post(route('apply.store'), [
            'applicant_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '15551234567',
            'university_name' => 'Sample University',
            'course_name' => 'Computer Science',
            'year' => '2026',
            'notes' => 'Please add this course.',
        ]);

        $response->assertRedirect(route('apply.create'));
        $response->assertSessionHasErrors('cf-turnstile-response');
        $this->assertDatabaseCount('course_applications', 0);
        Queue::assertNothingPushed();
    }

    public function test_apply_submission_works_in_production_with_turnstile(): void
    {
        $this->app['env'] = 'production';
        $this->withoutMiddleware(ValidateCsrfToken::class);
        Turnstile::fake();
        Queue::fake();

        $response = $this->post(route('apply.store'), [
            'applicant_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '15551234567',
            'university_name' => 'Sample University',
            'course_name' => 'Computer Science',
            'year' => '2026',
            'notes' => 'Please add this course.',
            'cf-turnstile-response' => Turnstile::dummy(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('course_applications', [
            'email' => 'jane@example.com',
            'course_name' => 'Computer Science',
        ]);
        Queue::assertPushed(SendApplicationNotification::class);
    }
}
