<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseApplication;
use App\Models\FeaturedProfile;
use App\Models\Post;
use App\Models\University;
use App\Models\User;
use App\Models\UserFlag;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $statusBreakdown = [
            'active' => User::query()->where('status', 'active')->count(),
            'banned' => User::query()->where('status', 'banned')->count(),
            'suspended' => User::query()->where('status', 'suspended')->count(),
        ];

        $recentUsers = User::query()
            ->latest()
            ->limit(5)
            ->with(['university', 'course'])
            ->get(['id', 'name', 'email', 'username', 'role', 'status', 'created_at', 'university_id', 'course_id']);

        $postBreakdown = [
            'published' => Post::query()->where('published', true)->count(),
            'processing' => Post::query()->where('published', false)->count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'universities' => University::query()->count(),
                'courses' => Course::query()->count(),
                'users' => User::query()->count(),
                'posts' => Post::query()->count(),
                'photos' => Media::query()->where('model_type', Post::class)->count(),
                'featuredProfiles' => FeaturedProfile::query()->count(),
                'applicationsPending' => CourseApplication::query()->where('status', 'pending')->count(),
                'applicationsReviewed' => CourseApplication::query()->where('status', 'reviewed')->count(),
                'flagsPending' => UserFlag::query()->where('reviewed', false)->count(),
                'flagsReviewed' => UserFlag::query()->where('reviewed', true)->count(),
                'statusBreakdown' => $statusBreakdown,
                'postBreakdown' => $postBreakdown,
            ],
            'recentUsers' => $recentUsers,
        ]);
    }
}
