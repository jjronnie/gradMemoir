<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckUsernameAvailabilityRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UsernameAvailabilityController extends Controller
{
    public function __invoke(CheckUsernameAvailabilityRequest $request): JsonResponse
    {
        $username = (string) $request->validated('username');

        $available = ! User::query()
            ->when($request->user() !== null, function ($query) use ($request): void {
                $query->whereKeyNot($request->user()->id);
            })
            ->where('username', $username)
            ->exists();

        return response()->json([
            'data' => [
                'available' => $available,
                'username' => $username,
            ],
            'message' => 'Username availability checked.',
            'errors' => null,
        ]);
    }
}
