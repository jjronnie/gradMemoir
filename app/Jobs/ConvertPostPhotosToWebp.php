<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class ConvertPostPhotosToWebp implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public function __construct(public Post $post) {}

    public function handle(): void
    {
        $post = $this->post->fresh();

        if ($post === null) {
            return;
        }

        $mediaItems = $post->getMedia('post_photos');
        $allConverted = $mediaItems->every(function ($media): bool {
            $generated = $media->generated_conversions;

            return (bool) ($generated['thumb'] ?? false) && (bool) ($generated['full'] ?? false);
        });

        if (! $allConverted && $mediaItems->count() > 0) {
            $this->release(2);

            return;
        }

        $post->forceFill([
            'published' => true,
            'published_at' => now(),
        ])->save();
    }
}
