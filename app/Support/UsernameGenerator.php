<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

class UsernameGenerator
{
    public static function generateUnique(string $name, ?int $ignoreUserId = null): string
    {
        $base = self::normalize($name);

        if ($base === '') {
            $base = 'student';
        }

        $candidate = Str::limit($base, 30, '');
        $counter = 2;

        while (self::exists($candidate, $ignoreUserId)) {
            $suffix = '_'.$counter;
            $maxBaseLength = 30 - strlen($suffix);
            $candidate = Str::limit($base, max($maxBaseLength, 3), '').$suffix;
            $counter++;
        }

        return $candidate;
    }

    public static function normalize(string $username): string
    {
        $normalized = Str::of($username)
            ->ascii()
            ->lower()
            ->replace(' ', '_')
            ->replaceMatches('/[^a-z0-9_]/', '')
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->value();

        return substr($normalized, 0, 30);
    }

    private static function exists(string $username, ?int $ignoreUserId = null): bool
    {
        return User::query()
            ->when($ignoreUserId !== null, function ($query) use ($ignoreUserId) {
                $query->whereKeyNot($ignoreUserId);
            })
            ->where('username', $username)
            ->exists();
    }
}
