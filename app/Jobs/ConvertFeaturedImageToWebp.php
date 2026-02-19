<?php

namespace App\Jobs;

use App\Models\FeaturedImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class ConvertFeaturedImageToWebp implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /**
     * Create a new class instance.
     */
    public function __construct(public FeaturedImage $featuredImage) {}

    public function handle(): void
    {
        $featuredImage = $this->featuredImage->fresh();

        if ($featuredImage === null) {
            return;
        }

        $media = $featuredImage->getFirstMedia('featured_images');

        if ($media === null) {
            $featuredImage->delete();

            return;
        }

        $generated = $media->generated_conversions;
        $thumbReady = (bool) ($generated['thumb'] ?? false);
        $fullReady = (bool) ($generated['full'] ?? false);

        if (! $thumbReady || ! $fullReady) {
            $this->release(2);

            return;
        }

        if (! $featuredImage->is_ready) {
            $featuredImage->forceFill([
                'is_ready' => true,
            ])->save();
        }
    }
}
