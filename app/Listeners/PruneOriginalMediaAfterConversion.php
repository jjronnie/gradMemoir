<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Conversions\Events\ConversionHasBeenCompletedEvent;
use Spatie\MediaLibrary\MediaCollections\Filesystem;

class PruneOriginalMediaAfterConversion
{
    public function handle(ConversionHasBeenCompletedEvent $event): void
    {
        $media = $event->media->fresh();

        if ($media === null) {
            return;
        }

        if ((bool) $media->getCustomProperty('original_pruned', false)) {
            return;
        }

        $requiredConversions = $media->getMediaConversionNames();

        if ($requiredConversions === []) {
            return;
        }

        $allGenerated = collect($requiredConversions)
            ->every(fn (string $conversionName): bool => $media->hasGeneratedConversion($conversionName));

        if (! $allGenerated) {
            return;
        }

        $originalRelativePath = $media->getPathRelativeToRoot();

        if (Storage::disk($media->disk)->exists($originalRelativePath)) {
            app(Filesystem::class)->removeFile($media, $originalRelativePath);
        }

        app(Filesystem::class)->removeResponsiveImages($media, 'media_library_original');

        $media->setCustomProperty('original_pruned', true);
        $media->save();
    }
}
