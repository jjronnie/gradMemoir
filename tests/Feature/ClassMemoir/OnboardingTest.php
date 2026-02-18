<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\Course;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_redirected_to_onboarding_when_not_completed(): void
    {
        $user = User::factory()->create([
            'onboarding_completed' => false,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('onboarding.show'));
    }

    public function test_user_can_complete_onboarding(): void
    {
        $user = User::factory()->create([
            'onboarding_completed' => false,
        ]);
        $creator = User::factory()->superadmin()->create();
        $university = University::factory()->create([
            'created_by' => $creator->id,
        ]);
        $course = Course::factory()->create([
            'university_id' => $university->id,
            'created_by' => $creator->id,
        ]);

        $response = $this->actingAs($user)->post('/onboarding', [
            'university_id' => $university->id,
            'course_id' => $course->id,
            'username' => 'test_user_name',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success', 'Onboarding completed. You can now complete your profile and add your 12 memories.');

        $user->refresh();

        $this->assertTrue($user->onboarding_completed);
        $this->assertSame($university->id, $user->university_id);
        $this->assertSame($course->id, $user->course_id);
        $this->assertSame('test_user_name', $user->username);
    }
}
