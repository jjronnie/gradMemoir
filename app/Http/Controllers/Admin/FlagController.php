<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserFlag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FlagController extends Controller
{
    public function index(): Response
    {
        $flags = UserFlag::query()
            ->with(['user.media', 'flaggedBy'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Flags/Index', [
            'flags' => $flags,
        ]);
    }

    public function update(Request $request, UserFlag $userFlag): RedirectResponse
    {
        $request->validate([
            'reviewed' => ['required', 'boolean'],
        ]);

        $userFlag->update([
            'reviewed' => (bool) $request->boolean('reviewed'),
        ]);

        return back()->with('success', 'Flag updated.');
    }

    public function destroy(UserFlag $userFlag): RedirectResponse
    {
        $userFlag->delete();

        return back()->with('success', 'Flag deleted.');
    }
}
