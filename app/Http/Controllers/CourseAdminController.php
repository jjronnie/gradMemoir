<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseAdminController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $course = Course::query()
            ->with(['university'])
            ->where('admin_id', $request->user()->id)
            ->firstOrFail();

        $search = trim((string) $request->string('search'));

        $members = $course->students()
            ->with(['university', 'course', 'media'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('CourseAdmin/Index', [
            'course' => $course,
            'members' => $members,
            'search' => $search,
        ]);
    }
}
