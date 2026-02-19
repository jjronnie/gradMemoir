<?php

use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\CourseYearController as AdminCourseYearController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FeaturedProfileController as AdminFeaturedProfileController;
use App\Http\Controllers\Admin\FlagController as AdminFlagController;
use App\Http\Controllers\Admin\UniversityController as AdminUniversityController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\PostStatusController;
use App\Http\Controllers\Api\UsernameAvailabilityController;
use App\Http\Controllers\CourseAdminController;
use App\Http\Controllers\CourseApplicationController;
use App\Http\Controllers\CourseArchiveController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseShortCodeRedirectController;
use App\Http\Controllers\CourseYearController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MoreController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\UniversityArchiveController;
use App\Http\Controllers\UserFlagController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/universities', [UniversityArchiveController::class, 'index'])->name('universities.index');
Route::get('/universities/{slug}', [UniversityArchiveController::class, 'show'])->name('universities.show');
Route::get('/university/{slug}', [UniversityArchiveController::class, 'show'])->name('university.show');
Route::get('/course/{shortName}-class-of-{year}', [CourseYearController::class, 'show'])
    ->where('shortName', '[a-z0-9-]+')
    ->where('year', '\d{4}')
    ->name('course-years.show');
Route::get('/course/{shortName}', [CourseController::class, 'show'])
    ->where('shortName', '[a-z0-9-]+')
    ->name('courses.overview');
Route::get('/courses/{slug}', [CourseArchiveController::class, 'show'])->name('courses.show');
Route::get('/c/{shortcode}', CourseShortCodeRedirectController::class)->name('courses.shortcode');

Route::get('/how-it-works', [MoreController::class, 'howItWorks'])->name('how-it-works');
Route::get('/terms', [MoreController::class, 'terms'])->name('terms');
Route::get('/more', [MoreController::class, 'index'])->name('more');
Route::get('/account-suspended', [MoreController::class, 'accountSuspended'])->name('account.suspended');

Route::get('/apply', [CourseApplicationController::class, 'create'])->name('apply.create');
Route::post('/apply', [CourseApplicationController::class, 'store'])->name('apply.store');

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

Route::post('/api/check-username', UsernameAvailabilityController::class)
    ->middleware(['auth', 'check.status'])
    ->name('api.username.check');
Route::get('/posts/{post}/status', PostStatusController::class)
    ->middleware(['auth', 'check.status'])
    ->name('posts.status');

Route::middleware(['auth', 'check.status'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    Route::middleware(['verified', 'onboarding.complete'])->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
        Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
        Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
        Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
        Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

        Route::get('/course-admin', CourseAdminController::class)
            ->middleware('role:admin')
            ->name('course-admin');
        Route::post('/users/{user}/flag', [UserFlagController::class, 'store'])
            ->middleware('role:admin')
            ->name('users.flag');
    });
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'check.status', 'onboarding.complete', 'role:superadmin'])
    ->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('universities', AdminUniversityController::class)->except(['show']);

        Route::resource('courses', AdminCourseController::class)->except(['show']);
        Route::post('/courses/{course}/course-years', [AdminCourseYearController::class, 'store'])->name('course-years.store');
        Route::put('/course-years/{courseYear}/admin', [AdminCourseYearController::class, 'updateAdmin'])->name('course-years.admin');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'updateDetails'])->name('users.update');
        Route::put('/users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.status');
        Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        Route::get('/featured-profiles', [AdminFeaturedProfileController::class, 'index'])->name('featured-profiles.index');
        Route::post('/featured-profiles', [AdminFeaturedProfileController::class, 'store'])->name('featured-profiles.store');
        Route::delete('/featured-profiles/{featuredProfile}', [AdminFeaturedProfileController::class, 'destroy'])->name('featured-profiles.destroy');
        Route::put('/featured-profiles/{featuredProfile}', [AdminFeaturedProfileController::class, 'update'])->name('featured-profiles.update');

        Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
        Route::put('/applications/{courseApplication}', [AdminApplicationController::class, 'update'])->name('applications.update');
        Route::delete('/applications/{courseApplication}', [AdminApplicationController::class, 'destroy'])->name('applications.destroy');

        Route::get('/flags', [AdminFlagController::class, 'index'])->name('flags.index');
        Route::put('/flags/{userFlag}', [AdminFlagController::class, 'update'])->name('flags.update');
        Route::delete('/flags/{userFlag}', [AdminFlagController::class, 'destroy'])->name('flags.destroy');
    });

require __DIR__.'/settings.php';

Route::get('/{username}', [PublicProfileController::class, 'show'])
    ->where('username', '^(?!login$|register$|logout$|password$|two-factor$|email$|settings$|admin$|dashboard$|onboarding$|course$|courses$|university$|universities$|posts$|more$|terms$|apply$|how-it-works$|auth$|api$|c$)[a-z0-9_]{3,30}$')
    ->name('profile.public');

Route::get('/@{username}', [PublicProfileController::class, 'show'])
    ->where('username', '[a-z0-9_]{3,30}')
    ->name('profile.public.handle');
