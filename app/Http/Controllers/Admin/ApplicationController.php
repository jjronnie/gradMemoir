<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Models\CourseApplication;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    public function index(): Response
    {
        $applications = CourseApplication::query()
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Applications/Index', [
            'applications' => $applications,
        ]);
    }

    public function update(UpdateApplicationStatusRequest $request, CourseApplication $courseApplication): RedirectResponse
    {
        $courseApplication->update([
            'status' => $request->validated('status'),
        ]);

        return back()->with('success', 'Application updated.');
    }

    public function destroy(CourseApplication $courseApplication): RedirectResponse
    {
        $courseApplication->delete();

        return back()->with('success', 'Application deleted.');
    }
}
