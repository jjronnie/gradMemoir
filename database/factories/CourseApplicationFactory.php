<?php

namespace Database\Factories;

use App\Enums\CourseApplicationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseApplication>
 */
class CourseApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'applicant_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('##########'),
            'university_name' => fake()->company().' University',
            'course_name' => fake()->words(3, true),
            'year' => (string) fake()->numberBetween(1990, 2030),
            'notes' => fake()->optional()->sentence(),
            'status' => CourseApplicationStatus::Pending->value,
        ];
    }
}
