<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\FeaturedProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminFeaturedProfileSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_results_include_featured_status_for_matching_users(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $featuredUser = User::factory()->create([
            'name' => 'Ada Featured',
            'username' => 'ada_featured',
            'email' => 'ada-featured@example.test',
        ]);
        $nonFeaturedUser = User::factory()->create([
            'name' => 'Ben Search',
            'username' => 'ben_search',
            'email' => 'ben-search@example.test',
        ]);

        $featuredProfile = FeaturedProfile::query()->create([
            'user_id' => $featuredUser->id,
            'sort_order' => 1,
            'created_by' => $superadmin->id,
        ]);

        $this->actingAs($superadmin)
            ->get('/admin/featured-profiles?search=search@example')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/FeaturedProfiles/Index')
                ->where('search', 'search@example')
                ->has('searchResults', 1)
                ->where('searchResults.0.id', $nonFeaturedUser->id)
                ->where('searchResults.0.featured_profile', null)
            );

        $this->actingAs($superadmin)
            ->get('/admin/featured-profiles?search=ada_featured')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/FeaturedProfiles/Index')
                ->has('searchResults', 1)
                ->where('searchResults.0.id', $featuredUser->id)
                ->where('searchResults.0.featured_profile.id', $featuredProfile->id)
            );
    }
}
