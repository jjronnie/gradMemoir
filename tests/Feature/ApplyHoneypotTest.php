<?php

namespace Tests\Feature;

use App\Jobs\SendApplicationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ApplyHoneypotTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_submission_works_with_empty_honeypot_fields(): void
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
            'middle_name' => '',
            'referral_code' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('course_applications', [
            'email' => 'jane@example.com',
            'course_name' => 'Computer Science',
        ]);
        Queue::assertPushed(SendApplicationNotification::class);
    }

    public function test_apply_submission_is_blocked_when_honeypot_field_is_filled(): void
    {
        Queue::fake();

        $response = $this->from(route('apply.create'))->post(route('apply.store'), [
            'applicant_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '15551234567',
            'university_name' => 'Sample University',
            'course_name' => 'Computer Science',
            'year' => '2026',
            'notes' => 'Please add this course.',
            'middle_name' => 'spammy',
        ]);

        $response->assertRedirect(route('apply.create'));
        $response->assertSessionHasErrors('middle_name');
        $this->assertDatabaseCount('course_applications', 0);
        Queue::assertNothingPushed();
    }
}
