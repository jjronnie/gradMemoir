<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseYear>
 */
class CourseYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = (string) fake()->numberBetween(1990, 2030);

        return [
            'course_id' => Course::factory(),
            'year' => $year,
            'slug' => null,
            'admin_id' => User::factory()->admin(),
        ];
    }
}
