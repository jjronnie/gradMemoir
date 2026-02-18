<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\Course;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SeoPropsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_contains_expected_seo_props(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->where('seo.title', config('app.name').' - Lets keep it here')
                ->where('seo.image', url('/featured.webp'))
            );
    }

    public function test_profile_page_contains_profile_specific_seo_props(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Camera',
            'username' => 'jane_camera',
            'bio' => 'Creative storyteller and photography enthusiast.',
        ]);

        $this->get('/@'.$user->username)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profiles/Show')
                ->where('seo.title', 'Jane Camera | '.config('app.name'))
                ->where('seo.description', 'Creative storyteller and photography enthusiast.')
            );
    }

    public function test_course_and_university_pages_contain_expected_seo_title_patterns(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $university = University::factory()->create([
            'name' => 'Memoir University',
            'slug' => 'memoir-university',
            'created_by' => $superadmin->id,
        ]);
        $course = Course::factory()->create([
            'name' => 'BSc Computer Science',
            'slug' => 'bsc-computer-science',
            'university_id' => $university->id,
            'created_by' => $superadmin->id,
        ]);

        $this->get('/courses/'.$course->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Show')
                ->where('seo.title', 'BSc Computer Science - '.config('app.name'))
            );

        $this->get('/universities/'.$university->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Universities/Show')
                ->where('seo.title', 'Memoir University - '.config('app.name'))
            );
    }
}
