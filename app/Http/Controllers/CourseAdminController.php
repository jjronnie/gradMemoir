<?php

namespace App\Http\Controllers;

use App\Models\CourseYear;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseAdminController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $courseYear = CourseYear::query()
            ->with(['course.university'])
            ->where('admin_id', $request->user()->id)
            ->firstOrFail();

        $this->authorize('manageMembers', $courseYear);

        $search = trim((string) $request->string('search'));

        $members = $courseYear->users()
            ->with(['university', 'course', 'courseYear', 'media'])
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
            'courseYear' => $courseYear,
            'members' => $members,
            'search' => $search,
        ]);
    }
}
