<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_add_more_than_eight_photos(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        for ($index = 0; $index < 8; $index++) {
            $post = Post::query()->create([
                'user_id' => $user->id,
                'body' => null,
                'published' => true,
                'published_at' => now(),
            ]);

            $post->addMedia(UploadedFile::fake()->image("existing-{$index}.jpg"))
                ->toMediaCollection('post_photos');
        }

        $response = $this->actingAs($user)->post(
            '/posts',
            [
                'body' => 'Extra photo',
                'photos' => [UploadedFile::fake()->image('extra.jpg')],
            ],
            [
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['photos']);
    }
}
