<?php

namespace Tests\Feature\ClassMemoir;

use App\Models\Course;
use App\Models\CourseYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseYearSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_year_slug_matches_expected_format_and_is_immutable(): void
    {
        $course = Course::factory()->create([
            'short_name' => 'BSc CS',
        ]);

        $courseYear = CourseYear::query()->create([
            'course_id' => $course->id,
            'year' => '2026',
        ]);

        $this->assertSame('course/bsc-cs-class-of-2026', $courseYear->slug);

        $courseYear->update([
            'year' => '2027',
        ]);

        $this->assertSame('course/bsc-cs-class-of-2026', $courseYear->fresh()->slug);
    }
}
