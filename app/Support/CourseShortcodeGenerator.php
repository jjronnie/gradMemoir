<?php

namespace App\Support;

use App\Models\Course;
use Illuminate\Support\Str;

class CourseShortcodeGenerator
{
    public static function generateUnique(): string
    {
        do {
            $shortcode = Str::lower(Str::random(8));
        } while (Course::query()->where('shortcode', $shortcode)->exists());

        return $shortcode;
    }
}
