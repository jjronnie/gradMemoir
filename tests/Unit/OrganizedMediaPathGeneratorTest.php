<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\User;
use App\Support\Media\OrganizedMediaPathGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class OrganizedMediaPathGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_username_based_path_for_avatar_media(): void
    {
        $generator = new OrganizedMediaPathGenerator;
        $user = User::factory()->create([
            'username' => '@Jane_Doe',
        ]);

        $media = new Media([
            'uuid' => 'avatar-uuid',
            'model_type' => User::class,
            'model_id' => $user->id,
            'collection_name' => 'avatar',
        ]);
        $media->setRelation('model', $user);

        $this->assertSame('students/jane_doe/profile-photos/avatar-uuid/', $generator->getPath($media));
        $this->assertSame('students/jane_doe/profile-photos/avatar-uuid/conversions/', $generator->getPathForConversions($media));
    }

    public function test_it_generates_username_based_path_for_post_media(): void
    {
        $generator = new OrganizedMediaPathGenerator;
        $user = User::factory()->create([
            'username' => 'sarah_camera',
        ]);
        $post = Post::factory()->create([
            'user_id' => $user->id,
        ]);
        $post->setRelation('user', $user);

        $media = new Media([
            'uuid' => 'post-uuid',
            'model_type' => Post::class,
            'model_id' => $post->id,
            'collection_name' => 'post_photos',
        ]);
        $media->setRelation('model', $post);

        $path = $generator->getPath($media);

        $this->assertSame('students/sarah_camera/post-photos/post-uuid/', $path);
        $this->assertStringNotContainsString((string) $user->id, $path);
    }

    public function test_it_prefers_stored_owner_username_for_path_consistency(): void
    {
        $generator = new OrganizedMediaPathGenerator;

        $media = new Media([
            'uuid' => 'legacy-uuid',
            'model_type' => Post::class,
            'collection_name' => 'post_photos',
            'custom_properties' => [
                'owner_username' => '@legacy_user',
            ],
        ]);

        $this->assertSame('students/legacy_user/post-photos/legacy-uuid/', $generator->getPath($media));
    }
}
