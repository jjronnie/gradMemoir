<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class PublicProfileController extends Controller
{
    public function show(string $username): Response
    {
        $user = User::publicScope()
            ->where('username', $username)
            ->with([
                'media',
                'university:id,name,slug',
                'university.media',
                'course:id,name,short_name,year,slug',
            ])
            ->firstOrFail();

        $posts = $user->posts()
            ->where('published', true)
            ->with('media')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $profileImage = $user->getFirstMediaUrl('avatar', 'full');
        $fallbackDescription = "View {$user->name}'s class memoir profile and published memories.";

        return Inertia::render('Profiles/Show', [
            'profile' => $user,
            'posts' => $posts,
            'seo' => [
                'title' => $user->name.' | '.config('app.name'),
                'description' => filled($user->bio) ? (string) $user->bio : $fallbackDescription,
                'image' => $profileImage !== '' ? $profileImage : url('/featured.webp'),
                'type' => 'profile',
            ],
        ]);
    }
}
