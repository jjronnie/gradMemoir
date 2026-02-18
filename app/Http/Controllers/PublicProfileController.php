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

        return Inertia::render('Profiles/Show', [
            'profile' => $user,
            'posts' => $posts,
        ]);
    }
}
