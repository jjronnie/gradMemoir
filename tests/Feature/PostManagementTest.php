<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PostManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createPostForUser(User $user): Post
    {
        Storage::fake('public');

        $post = Post::query()->create([
            'user_id' => $user->id,
            'body' => 'Original caption',
            'published' => true,
            'published_at' => now(),
        ]);

        $post->addMedia(UploadedFile::fake()->image('memory.jpg'))
            ->withCustomProperties([
                'owner_username' => ltrim((string) $user->username, '@'),
            ])
            ->toMediaCollection('post_photos');

        return $post;
    }

    public function test_authenticated_user_can_view_posts_index(): void
    {
        $user = User::factory()->create();
        $post = $this->createPostForUser($user);

        $this->actingAs($user)
            ->get(route('posts.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Posts/Index')
                ->where('posts.data.0.id', $post->id)
                ->where('posts.data.0.photos_count', 1)
            );
    }

    public function test_user_can_update_their_post_body(): void
    {
        $user = User::factory()->create();
        $post = $this->createPostForUser($user);

        $response = $this->actingAs($user)->put(route('posts.update', $post), [
            'body' => 'Updated caption body',
        ]);

        $response->assertRedirect(route('posts.index'));

        $post->refresh();

        $this->assertSame('Updated caption body', $post->body);
        $this->assertSame(1, $post->getMedia('post_photos')->count());
    }

    public function test_user_can_delete_post_and_its_media_files(): void
    {
        $user = User::factory()->create();
        $post = $this->createPostForUser($user);

        $media = $post->getMedia('post_photos')->firstOrFail();
        $originalPath = $media->getPathRelativeToRoot();
        $thumbPath = $media->getPathRelativeToRoot('thumb');
        $fullPath = $media->getPathRelativeToRoot('full');

        $response = $this->actingAs($user)->delete(route('posts.destroy', $post));

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
        $this->assertDatabaseMissing('media', [
            'id' => $media->id,
        ]);
        Storage::disk('public')->assertMissing($originalPath);
        Storage::disk('public')->assertMissing($thumbPath);
        Storage::disk('public')->assertMissing($fullPath);
    }

    public function test_user_cannot_manage_another_users_post(): void
    {
        $owner = User::factory()->create();
        $post = $this->createPostForUser($owner);

        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->get(route('posts.edit', $post))
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->put(route('posts.update', $post), [
                'body' => 'Attempted update',
            ])
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->delete(route('posts.destroy', $post))
            ->assertNotFound();
    }
}
