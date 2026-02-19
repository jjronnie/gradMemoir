<?php

namespace App\Http\Controllers;

use App\Models\FeaturedImage;
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

        $featuredImages = FeaturedImage::query()
            ->with('media')
            ->where('is_ready', true)
            ->orderByDesc('created_at')
            ->get();

        $universities = University::query()
            ->withCount('courses')
            ->orderBy('name')
            ->limit(12)
            ->get();

        $profileGalleryItems = $featuredProfiles
            ->map(function (FeaturedProfile $featuredProfile): array {
                $user = $featuredProfile->user;

                if ($user === null) {
                    return [];
                }

                $thumb = $user->getFirstMediaUrl('avatar', 'thumb');
                $full = $user->getFirstMediaUrl('avatar', 'full');
                $fallback = $user->avatar_url;

                return [
                    'thumb' => $thumb !== '' ? $thumb : ($full !== '' ? $full : $fallback),
                    'full' => $full !== '' ? $full : ($thumb !== '' ? $thumb : $fallback),
                    'created_at' => $featuredProfile->created_at,
                ];
            })
            ->filter(fn (array $photo): bool => ($photo['thumb'] ?? '') !== '' && ($photo['created_at'] ?? null) !== null);

        $featuredImageItems = $featuredImages
            ->map(function (FeaturedImage $featuredImage): array {
                $thumb = $featuredImage->getFirstMediaUrl('featured_images', 'thumb');
                $full = $featuredImage->getFirstMediaUrl('featured_images', 'full');
                $fallback = $featuredImage->getFirstMediaUrl('featured_images');

                return [
                    'thumb' => $thumb !== '' ? $thumb : ($full !== '' ? $full : $fallback),
                    'full' => $full !== '' ? $full : ($thumb !== '' ? $thumb : $fallback),
                    'created_at' => $featuredImage->created_at,
                ];
            })
            ->filter(fn (array $photo): bool => $photo['thumb'] !== '' && ($photo['created_at'] ?? null) !== null);

        $galleryPhotos = $profileGalleryItems
            ->concat($featuredImageItems)
            ->sortByDesc('created_at')
            ->take(21)
            ->map(fn (array $photo): array => [
                'thumb' => $photo['thumb'],
                'full' => $photo['full'],
            ])
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
