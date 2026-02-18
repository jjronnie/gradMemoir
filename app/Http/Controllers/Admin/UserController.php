<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserDetailsRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Requests\UpdateUserStatusRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $currentUser = request()->user();
        $search = trim((string) request()->string('search'));
        $role = trim((string) request()->string('role'));
        $status = trim((string) request()->string('status'));

        $users = User::query()
            ->with(['course', 'university'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', function ($query) use ($role): void {
                $query->where('role', $role);
            })
            ->when($status !== '', function ($query) use ($status): void {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'currentUserId' => $currentUser?->id,
            'filters' => [
                'search' => $search,
                'role' => $role,
                'status' => $status,
            ],
        ]);
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user): RedirectResponse
    {
        if ($request->user()?->is($user)) {
            return back()->with('error', 'You cannot update your own account status.');
        }

        $user->update([
            'status' => $request->validated('status'),
        ]);

        return back()->with('success', 'User status updated.');
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        if ($request->user()?->is($user)) {
            return back()->with('error', 'You cannot update your own role.');
        }

        $user->update([
            'role' => $request->validated('role'),
        ]);

        return back()->with('success', 'User role updated.');
    }

    public function updateDetails(UpdateUserDetailsRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'website' => $validated['website'] ?? null,
            'is_verified' => (bool) $validated['is_verified'],
            'email_verified_at' => $validated['email_verified_at'] ?? null,
        ]);

        return back()->with('success', 'User details updated.');
    }

    public function edit(User $user): Response
    {
        $user->load(['course', 'university']);

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'currentUserId' => request()->user()?->id,
        ]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->is($user)) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return back()->with('success', 'User deleted.');
    }
}
