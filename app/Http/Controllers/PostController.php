<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Jobs\ConvertPostPhotosToWebp;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
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
}
