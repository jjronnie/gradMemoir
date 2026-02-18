<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FeaturedProfileStoreRequest;
use App\Models\FeaturedProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeaturedProfileController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));

        $featuredProfiles = FeaturedProfile::query()
            ->with(['user.media', 'user.university'])
            ->orderBy('sort_order')
            ->paginate(50)
            ->withQueryString();

        $searchResults = User::query()
            ->where('status', 'active')
            ->whereIn('role', ['student', 'admin'])
            ->with(['media', 'featuredProfile'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit($search === '' ? 0 : 30)
            ->get(['id', 'name', 'username', 'email']);

        return Inertia::render('Admin/FeaturedProfiles/Index', [
            'featuredProfiles' => $featuredProfiles,
            'search' => $search,
            'searchResults' => $searchResults,
        ]);
    }

    public function store(FeaturedProfileStoreRequest $request): RedirectResponse
    {
        FeaturedProfile::query()->create([
            'user_id' => $request->validated('user_id'),
            'sort_order' => ((int) FeaturedProfile::query()->max('sort_order')) + 1,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Featured profile added.');
    }

    public function update(Request $request, FeaturedProfile $featuredProfile): RedirectResponse
    {
        $request->validate([
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $featuredProfile->update([
            'sort_order' => $request->integer('sort_order'),
        ]);

        return back()->with('success', 'Featured profile reordered.');
    }

    public function destroy(FeaturedProfile $featuredProfile): RedirectResponse
    {
        $featuredProfile->delete();

        return back()->with('success', 'Featured profile removed.');
    }
}
