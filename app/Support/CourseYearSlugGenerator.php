<?php

namespace App\Support;

use Illuminate\Support\Str;

class CourseYearSlugGenerator
{
    public static function sanitizeShortName(string $shortName): string
    {
        $sanitized = Str::of($shortName)
            ->lower()
            ->squish()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->value();

        return $sanitized !== '' ? $sanitized : 'course';
    }

    public static function fromShortNameAndYear(string $shortName, string $year): string
    {
        $sanitizedShortName = self::sanitizeShortName($shortName);

        return "course/{$sanitizedShortName}-class-of-{$year}";
    }
}
