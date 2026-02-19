<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Jobs\ConvertPostPhotosToWebp;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $posts = Post::query()
            ->whereBelongsTo($request->user())
            ->with('media')
            ->latest()
            ->paginate(9)
            ->through(fn (Post $post): array => $this->serializePost($post));

        return Inertia::render('Posts/Index', [
            'posts' => $posts,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Posts/Create');
    }

    public function store(StorePostRequest $request): JsonResponse|RedirectResponse
    {
        $post = Post::query()->create([
            'user_id' => $request->user()->id,
            'body' => $request->string('body')->value() ?: null,
            'published' => false,
        ]);

        foreach ($request->file('photos', []) as $photo) {
            $post->addMedia($photo)
                ->withCustomProperties([
                    'owner_username' => ltrim((string) $request->user()->username, '@'),
                ])
                ->toMediaCollection('post_photos');
        }

        ConvertPostPhotosToWebp::dispatch($post);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'post_id' => $post->id,
                    'published' => $post->published,
                    'progress' => 0,
                ],
                'message' => 'Photo uploaded successfully. Your post will be visible in a minute or two.',
                'errors' => null,
            ], 201);
        }

        return redirect()->route('posts.create')->with('success', 'Photo uploaded successfully. Your post will be visible in a minute or two.');
    }

    public function edit(Request $request, Post $post): Response
    {
        abort_unless($post->user_id === $request->user()?->id, 404);

        $post->load('media');

        return Inertia::render('Posts/Edit', [
            'post' => $this->serializePost($post),
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()?->id, 404);

        $post->update([
            'body' => $request->string('body')->value() ?: null,
        ]);

        return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()?->id, 404);

        $post->clearMediaCollection('post_photos');
        $post->delete();

        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePost(Post $post): array
    {
        $photos = $post->getMedia('post_photos')
            ->map(fn (Media $media): array => [
                'id' => $media->id,
                'thumb' => $media->getUrl('thumb'),
                'full' => $media->getUrl('full') !== '' ? $media->getUrl('full') : $media->getUrl(),
                'url' => $media->getUrl(),
                'file_name' => $media->file_name,
            ])
            ->values();

        return [
            'id' => $post->id,
            'body' => $post->body,
            'published' => $post->published,
            'published_at' => optional($post->published_at)->toIso8601String(),
            'created_at' => optional($post->created_at)->toIso8601String(),
            'updated_at' => optional($post->updated_at)->toIso8601String(),
            'photos_count' => $photos->count(),
            'photos' => $photos,
        ];
    }
}
