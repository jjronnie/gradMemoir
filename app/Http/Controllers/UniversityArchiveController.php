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

        return Inertia::render('Universities/Show', [
            'university' => $university,
            'courses' => $courses,
        ]);
    }
}
