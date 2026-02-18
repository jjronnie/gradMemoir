<?php

namespace Tests\Unit;

use App\Jobs\Media\GenerateResponsiveImagesJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_serialization_exposes_conversion_urls_and_falls_back_when_original_is_missing(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'username' => '@media_user',
        ]);

        $media = $user->addMedia(UploadedFile::fake()->image('avatar.jpg', 800, 800))
            ->withCustomProperties([
                'owner_username' => 'media_user',
            ])
            ->toMediaCollection('avatar');

        $serialized = $media->fresh()->toArray();

        $this->assertIsArray($serialized['conversions']);
        $this->assertNotNull($serialized['conversions']['thumb']);
        $this->assertNotNull($serialized['conversions']['full']);

        Storage::disk('public')->delete($media->getPathRelativeToRoot());

        $serializedAfterPrune = $media->fresh()->toArray();

        $this->assertSame($serializedAfterPrune['conversions']['full'], $serializedAfterPrune['original_url']);
    }

    public function test_safe_responsive_job_does_not_fail_when_original_file_is_missing(): void
    {
        Storage::fake('public');

        $temporaryPath = storage_path('framework/testing/media-temp');
        File::ensureDirectoryExists("{$temporaryPath}/stale");
        touch("{$temporaryPath}/stale/empty.png", time() - 600);

        config()->set('media-library.temporary_directory_path', $temporaryPath);

        $user = User::factory()->create([
            'username' => '@queue_user',
        ]);

        $media = $user->addMedia(UploadedFile::fake()->image('avatar.jpg', 800, 800))
            ->withCustomProperties([
                'owner_username' => 'queue_user',
            ])
            ->toMediaCollection('avatar');

        Storage::disk('public')->delete($media->getPathRelativeToRoot());

        $result = (new GenerateResponsiveImagesJob($media->fresh()))->handle();

        $this->assertTrue($result);
        $this->assertDirectoryDoesNotExist("{$temporaryPath}/stale");
    }
}
