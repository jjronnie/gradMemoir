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
                'courseYear:id,course_id,year,slug',
                'courseYear.course:id,name,short_name',
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

        $profile = [
            ...$user->toArray(),
            'course' => $user->courseYear === null || $user->courseYear->course === null
                ? null
                : [
                    'name' => $user->courseYear->course->name,
                    'short_name' => $user->courseYear->course->short_name,
                    'year' => $user->courseYear->year,
                    'slug' => $user->courseYear->slug,
                ],
        ];

        return Inertia::render('Profiles/Show', [
            'profile' => $profile,
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
