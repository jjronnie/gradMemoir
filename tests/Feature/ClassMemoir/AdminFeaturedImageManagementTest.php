<?php

namespace Tests\Feature\ClassMemoir;

use App\Jobs\ConvertFeaturedImageToWebp;
use App\Models\FeaturedImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminFeaturedImageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_upload_up_to_five_featured_images_and_dispatch_conversion_jobs(): void
    {
        Storage::fake('public');
        Queue::fake();

        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($superadmin)
            ->post('/admin/featured-profiles/images', [
                'images' => [
                    UploadedFile::fake()->image('featured-admin-1.jpg'),
                    UploadedFile::fake()->image('featured-admin-2.jpg'),
                    UploadedFile::fake()->image('featured-admin-3.jpg'),
                    UploadedFile::fake()->image('featured-admin-4.jpg'),
                    UploadedFile::fake()->image('featured-admin-5.jpg'),
                ],
            ])
            ->assertStatus(302)
            ->assertSessionHas('success', 'Featured images uploaded.');

        $featuredImages = FeaturedImage::query()->get();

        $this->assertCount(5, $featuredImages);

        $featuredImages->each(function (FeaturedImage $featuredImage) use ($superadmin): void {
            $this->assertSame($superadmin->id, $featuredImage->created_by);
            $this->assertFalse($featuredImage->is_ready);
            $this->assertNotNull($featuredImage->getFirstMedia('featured_images'));
        });

        Queue::assertPushed(ConvertFeaturedImageToWebp::class, 5);
    }

    public function test_superadmin_cannot_upload_more_than_five_images_in_a_single_request(): void
    {
        Storage::fake('public');
        Queue::fake();

        $superadmin = User::factory()->superadmin()->create();

        $this->actingAs($superadmin)
            ->from('/admin/featured-profiles')
            ->post('/admin/featured-profiles/images', [
                'images' => [
                    UploadedFile::fake()->image('featured-1.jpg'),
                    UploadedFile::fake()->image('featured-2.jpg'),
                    UploadedFile::fake()->image('featured-3.jpg'),
                    UploadedFile::fake()->image('featured-4.jpg'),
                    UploadedFile::fake()->image('featured-5.jpg'),
                    UploadedFile::fake()->image('featured-6.jpg'),
                ],
            ])
            ->assertRedirect('/admin/featured-profiles')
            ->assertSessionHasErrors(['images']);

        $this->assertSame(0, FeaturedImage::query()->count());
        Queue::assertNothingPushed();
    }

    public function test_superadmin_can_delete_featured_image(): void
    {
        Storage::fake('public');

        $superadmin = User::factory()->superadmin()->create();

        $featuredImage = FeaturedImage::query()->create([
            'created_by' => $superadmin->id,
            'is_ready' => true,
        ]);

        $featuredImage->addMedia(UploadedFile::fake()->image('featured-delete.jpg'))
            ->toMediaCollection('featured_images');

        $this->actingAs($superadmin)
            ->delete("/admin/featured-profiles/images/{$featuredImage->id}")
            ->assertStatus(302)
            ->assertSessionHas('success', 'Featured image removed.');

        $this->assertDatabaseMissing('featured_images', [
            'id' => $featuredImage->id,
        ]);
    }
}
