<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DashboardController extends Controller
{
    public function __invoke(): Response|RedirectResponse
    {
        $user = request()->user();

        if ($user->role === UserRole::Superadmin) {
            return redirect()->route('admin.dashboard');
        }

        $user->load(['course.university', 'university']);

        $recentPosts = Post::query()
            ->whereBelongsTo($user)
            ->latest()
            ->limit(3)
            ->get();
        $photoCount = Media::query()
            ->where('model_type', Post::class)
            ->whereIn('model_id', $user->posts()->select('id'))
            ->count();
        $photoLimit = 12;

        return Inertia::render('Dashboard', [
            'recentPosts' => $recentPosts,
            'managedCourse' => $user->role?->value === 'admin'
                ? $user->course?->only(['id', 'name', 'slug', 'shortcode'])
                : null,
            'myUniversity' => $user->university?->only(['id', 'name', 'slug']),
            'photoUsage' => [
                'used' => $photoCount,
                'limit' => $photoLimit,
                'remaining' => max($photoLimit - $photoCount, 0),
            ],
        ]);
    }
}
