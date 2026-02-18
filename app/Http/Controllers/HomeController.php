<?php

namespace App\Http\Controllers;

use App\Models\FeaturedProfile;
use App\Models\University;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $featuredProfiles = FeaturedProfile::query()
            ->with([
                'user' => function ($query): void {
                    $query
                        ->where('status', 'active')
                        ->with(['media', 'university']);
                },
            ])
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (FeaturedProfile $featuredProfile): bool => $featuredProfile->user !== null)
            ->values();

        $universities = University::query()
            ->withCount('courses')
            ->orderBy('name')
            ->limit(12)
            ->get();

        $featuredUsers = $featuredProfiles
            ->pluck('user')
            ->filter()
            ->values();

        $galleryPhotos = $featuredUsers
            ->map(function ($user): array {
                $thumb = $user->getFirstMediaUrl('avatar', 'thumb');
                $full = $user->getFirstMediaUrl('avatar', 'full');
                $fallback = $user->avatar_url;

                return [
                    'thumb' => $thumb !== '' ? $thumb : ($full !== '' ? $full : $fallback),
                    'full' => $full !== '' ? $full : ($thumb !== '' ? $thumb : $fallback),
                ];
            })
            ->filter(fn (array $photo): bool => $photo['thumb'] !== '')
            ->take(21)
            ->values();

        return Inertia::render('Home', [
            'featuredProfiles' => $featuredProfiles,
            'universities' => $universities,
            'galleryPhotos' => $galleryPhotos,
            'seo' => [
                'title' => config('app.name').' - Lets keep it here',
                'description' => 'Lets keep it here. Explore featured class memories and graduation portfolios.',
                'image' => url('/featured.webp'),
                'type' => 'website',
            ],
        ]);
    }
}
