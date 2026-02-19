<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\CourseYear;
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

        $user->load(['courseYear.course.university', 'university']);

        $managedCourseYear = $user->role?->value === 'admin'
            ? CourseYear::query()
                ->where('admin_id', $user->id)
                ->with('course')
                ->first()
            : null;

        $recentPosts = Post::query()
            ->whereBelongsTo($user)
            ->latest()
            ->limit(3)
            ->get();
        $photoCount = Media::query()
            ->where('model_type', Post::class)
            ->whereIn('model_id', $user->posts()->select('id'))
            ->count();
        $photoLimit = 8;

        return Inertia::render('Dashboard', [
            'recentPosts' => $recentPosts,
            'managedCourseYear' => $managedCourseYear?->only(['id', 'year', 'slug', 'course_id']),
            'myUniversity' => $user->university?->only(['id', 'name', 'slug']),
            'photoUsage' => [
                'used' => $photoCount,
                'limit' => $photoLimit,
                'remaining' => max($photoLimit - $photoCount, 0),
            ],
        ]);
    }
}
