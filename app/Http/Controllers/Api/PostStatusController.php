<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PostStatusController extends Controller
{
    public function __invoke(Post $post): JsonResponse
    {
        abort_unless(
            $post->user_id === request()->user()?->id || $post->published,
            403,
        );

        $mediaItems = $post->getMedia('post_photos');
        $totalMedia = $mediaItems->count();
        $converted = $mediaItems->filter(function (Media $media): bool {
            $generated = $media->generated_conversions;

            return (bool) ($generated['thumb'] ?? false) && (bool) ($generated['full'] ?? false);
        })->count();

        $progress = $post->published
            ? 100
            : ($totalMedia === 0 ? 0 : (int) round(($converted / $totalMedia) * 100));

        return response()->json([
            'data' => [
                'published' => $post->published,
                'progress' => $progress,
            ],
            'message' => 'Post status fetched.',
            'errors' => null,
        ]);
    }
}
