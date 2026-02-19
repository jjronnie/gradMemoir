<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\FeaturedImage;
use App\Models\FeaturedProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomeFeaturedGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_gallery_uses_featured_profiles_when_no_featured_images_exist(): void
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

    public function test_home_gallery_merges_featured_images_and_sorts_by_created_at(): void
    {
        Storage::fake('public');

        $superadmin = User::factory()->superadmin()->create();
        $featuredUser = User::factory()->create();

        $featuredProfile = FeaturedProfile::query()->create([
            'user_id' => $featuredUser->id,
            'sort_order' => 1,
            'created_by' => $superadmin->id,
        ]);

        $featuredProfile->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->save();

        $featuredImage = FeaturedImage::query()->create([
            'created_by' => $superadmin->id,
            'is_ready' => true,
        ]);

        $featuredImage->addMedia(UploadedFile::fake()->image('featured-home.jpg'))
            ->toMediaCollection('featured_images');

        $featuredImage->forceFill([
            'created_at' => now(),
            'updated_at' => now(),
        ])->save();

        $notReadyImage = FeaturedImage::query()->create([
            'created_by' => $superadmin->id,
            'is_ready' => false,
        ]);

        $notReadyImage->addMedia(UploadedFile::fake()->image('featured-not-ready.jpg'))
            ->toMediaCollection('featured_images');

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->has('galleryPhotos', 2)
                ->where('galleryPhotos.0.full', fn ($value): bool => is_string($value) && ! str_contains($value, 'ui-avatars.com'))
                ->where('galleryPhotos.1.full', fn ($value): bool => is_string($value) && str_contains($value, 'ui-avatars.com'))
            );
    }
}
