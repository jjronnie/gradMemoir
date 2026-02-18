<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\FeaturedProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomeFeaturedGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_gallery_uses_featured_profiles_only(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $featuredOne = User::factory()->create();
        $featuredTwo = User::factory()->create();
        User::factory()->create();

        FeaturedProfile::query()->create([
            'user_id' => $featuredOne->id,
            'sort_order' => 1,
            'created_by' => $superadmin->id,
        ]);

        FeaturedProfile::query()->create([
            'user_id' => $featuredTwo->id,
            'sort_order' => 2,
            'created_by' => $superadmin->id,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->has('galleryPhotos', 2)
            );
    }
}
