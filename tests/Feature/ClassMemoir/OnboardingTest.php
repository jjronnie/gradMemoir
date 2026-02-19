<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\Course;
use App\Models\CourseYear;
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

    public function test_user_can_complete_onboarding_by_selecting_existing_cohort(): void
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
        $courseYear = CourseYear::factory()->create([
            'course_id' => $course->id,
            'year' => '2028',
        ]);

        $response = $this->actingAs($user)->post('/onboarding', [
            'university_id' => $university->id,
            'course_id' => $course->id,
            'course_year_id' => $courseYear->id,
            'username' => 'test_user_name',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success', 'Onboarding completed. You can now complete your profile and add your memories.');

        $user->refresh();

        $this->assertTrue($user->onboarding_completed);
        $this->assertSame($university->id, $user->university_id);
        $this->assertSame($course->id, $user->course_id);
        $this->assertSame($courseYear->id, $user->course_year_id);
        $this->assertSame('test_user_name', $user->username);
    }

    public function test_user_can_complete_onboarding_with_existing_cohort(): void
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
            'short_name' => 'CSC',
        ]);
        $courseYear = CourseYear::factory()->create([
            'course_id' => $course->id,
            'year' => '2026',
        ]);

        $this->actingAs($user)->post('/onboarding', [
            'university_id' => $university->id,
            'course_id' => $course->id,
            'course_year_id' => $courseYear->id,
            'username' => 'existing_cohort_user',
        ])->assertRedirect(route('dashboard'));

        $user->refresh();

        $this->assertSame($course->id, $user->course_id);
        $this->assertSame($courseYear->id, $user->course_year_id);
    }

    public function test_user_cannot_complete_onboarding_when_selected_cohort_does_not_belong_to_course(): void
    {
        $user = User::factory()->create([
            'onboarding_completed' => false,
        ]);
        $creator = User::factory()->superadmin()->create();
        $university = University::factory()->create([
            'created_by' => $creator->id,
        ]);
        $courseA = Course::factory()->create([
            'university_id' => $university->id,
            'created_by' => $creator->id,
        ]);
        $courseB = Course::factory()->create([
            'university_id' => $university->id,
            'created_by' => $creator->id,
        ]);
        $courseYearOnB = CourseYear::factory()->create([
            'course_id' => $courseB->id,
            'year' => '2029',
        ]);

        $this->actingAs($user)->post('/onboarding', [
            'university_id' => $university->id,
            'course_id' => $courseA->id,
            'course_year_id' => $courseYearOnB->id,
            'username' => 'bad_cohort_pick',
        ])->assertSessionHasErrors('course_year_id');
    }
}
