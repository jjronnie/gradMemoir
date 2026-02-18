<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUniversityRequest;
use App\Http\Requests\UpdateUniversityRequest;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UniversityController extends Controller
{
    public function index(): Response
    {
        $universities = University::query()
            ->with('media')
            ->withCount('courses')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Universities/Index', [
            'universities' => $universities,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Universities/Create');
    }

    public function store(StoreUniversityRequest $request): RedirectResponse
    {
        $university = University::query()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        if ($request->hasFile('logo')) {
            $university->clearMediaCollection('logo');
            $university->addMediaFromRequest('logo')
                ->withCustomProperties([
                    'university_slug' => $university->slug,
                ])
                ->toMediaCollection('logo');
        }

        return redirect()->route('admin.universities.index')->with('success', 'University created.');
    }

    public function edit(University $university): Response
    {
        $university->load('media');

        return Inertia::render('Admin/Universities/Edit', [
            'university' => $university,
        ]);
    }

    public function update(UpdateUniversityRequest $request, University $university): RedirectResponse
    {
        $university->update($request->validated());

        if ($request->hasFile('logo')) {
            $university->clearMediaCollection('logo');
            $university->addMediaFromRequest('logo')
                ->withCustomProperties([
                    'university_slug' => $university->slug,
                ])
                ->toMediaCollection('logo');
        }

        return redirect()->route('admin.universities.index')->with('success', 'University updated.');
    }

    public function destroy(University $university): RedirectResponse
    {
        $university->delete();

        return redirect()->route('admin.universities.index')->with('success', 'University deleted.');
    }
}
