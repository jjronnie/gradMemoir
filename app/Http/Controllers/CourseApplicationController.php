<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseApplicationStoreRequest;
use App\Jobs\SendApplicationNotification;
use App\Models\CourseApplication;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CourseApplicationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Apply');
    }

    public function store(CourseApplicationStoreRequest $request): RedirectResponse
    {
        $application = CourseApplication::query()->create($request->validated());

        SendApplicationNotification::dispatch($application);

        return back()->with('success', 'Application submitted successfully.');
    }
}
