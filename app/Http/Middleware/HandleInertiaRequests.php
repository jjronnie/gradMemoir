<?php

namespace App\Http\Middleware;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $photoCount = null;
        $photoLimit = 8;
        $photoSlotsRemaining = null;

        if ($user !== null) {
            $user->loadMissing('courseYear');

            $photoCount = Media::query()
                ->where('model_type', Post::class)
                ->whereIn('model_id', $user->posts()->select('id'))
                ->count();
            $photoSlotsRemaining = max($photoLimit - $photoCount, 0);
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'appName' => config('app.name'),
            'appUrl' => rtrim((string) (config('app.url') ?: $request->getSchemeAndHttpHost()), '/'),
            'seo' => [
                'title' => config('app.name'),
                'description' => 'Lets keep it here. Preserve class memories, profiles, and graduation stories in one place.',
                'image' => url('/featured.webp'),
                'type' => 'website',
            ],
            'auth' => [
                'user' => $user === null ? null : [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'website' => $user->website,
                    'is_verified' => $user->is_verified,
                    'avatar' => $user->getFirstMediaUrl('avatar', 'thumb') !== ''
                        ? $user->getFirstMediaUrl('avatar', 'thumb')
                        : $user->getFirstMediaUrl('avatar', 'full'),
                    'status' => $user->status?->value,
                    'roles' => $user->getRoleNames()->values(),
                    'onboarding_completed' => $user->onboarding_completed,
                    'university_id' => $user->university_id,
                    'course_id' => $user->course_id,
                    'course_year_id' => $user->course_year_id,
                    'course_year_slug' => $user->courseYear?->slug,
                    'course_year_url' => $user->courseYear?->slug === null ? null : '/'.$user->courseYear->slug,
                    'profile_url' => $user->profile_url,
                    'email_verified_at' => optional($user->email_verified_at)?->toIso8601String(),
                    'created_at' => optional($user->created_at)?->toIso8601String(),
                    'photo_count' => $photoCount,
                    'photo_limit' => $photoLimit,
                    'photo_slots_remaining' => $photoSlotsRemaining,
                ],
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
