<?php

namespace App\Http\Controllers;

use App\Models\University;
use Inertia\Inertia;
use Inertia\Response;

class UniversityArchiveController extends Controller
{
    public function index(): Response
    {
        $universities = University::query()
            ->with('media')
            ->withCount('courses')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Universities/Index', [
            'universities' => $universities,
            'seo' => [
                'title' => 'Universities - '.config('app.name'),
                'description' => 'Discover universities and class archives.',
                'image' => url('/featured.webp'),
                'type' => 'website',
            ],
        ]);
    }

    public function show(string $slug): Response
    {
        $university = University::query()
            ->with('media')
            ->where('slug', $slug)
            ->firstOrFail();

        $courses = $university->courses()
            ->withCount(['students as active_students_count' => function ($query): void {
                $query->where('status', 'active');
            }])
            ->orderBy('year', 'desc')
            ->orderBy('name')
            ->get();

        $logo = $university->getFirstMediaUrl('logo', 'full');

        return Inertia::render('Universities/Show', [
            'university' => $university,
            'courses' => $courses,
            'seo' => [
                'title' => $university->name.' - '.config('app.name'),
                'description' => "Explore courses and class memoirs for {$university->name}.",
                'image' => $logo !== '' ? $logo : url('/featured.webp'),
                'type' => 'website',
            ],
        ]);
    }
}
