<?php

namespace App\Jobs\Media;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Image\Exceptions\CouldNotLoadImage;

class GenerateResponsiveImagesJob extends \Spatie\MediaLibrary\ResponsiveImages\Jobs\GenerateResponsiveImagesJob
{
    public function handle(): bool
    {
        if (! Storage::disk($this->media->disk)->exists($this->media->getPathRelativeToRoot())) {
            $this->cleanupStaleTemporaryMediaFiles();

            return true;
        }

        try {
            return parent::handle();
        } catch (CouldNotLoadImage $exception) {
            logger()->warning('Skipping responsive image generation for media with missing or unreadable source.', [
                'media_id' => $this->media->id,
                'message' => $exception->getMessage(),
            ]);
            $this->cleanupStaleTemporaryMediaFiles();

            return true;
        }
    }

    protected function cleanupStaleTemporaryMediaFiles(): void
    {
        $temporaryDirectory = config('media-library.temporary_directory_path') ?: storage_path('media-library/temp');

        if (! File::isDirectory($temporaryDirectory)) {
            return;
        }

        $staleTimestamp = time() - 300;

        foreach (File::directories($temporaryDirectory) as $directory) {
            $files = File::files($directory);

            if ($files === []) {
                File::deleteDirectory($directory);

                continue;
            }

            $allFilesAreStaleZeroByte = collect($files)->every(
                fn (\SplFileInfo $file): bool => $file->getSize() === 0 && $file->getMTime() <= $staleTimestamp
            );

            if ($allFilesAreStaleZeroByte) {
                File::deleteDirectory($directory);
            }
        }
    }
}
