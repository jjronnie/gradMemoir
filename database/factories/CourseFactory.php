<?php

namespace Database\Factories;

use App\Models\University;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $shortName = Str::upper(fake()->lexify('??'));
        $year = (string) fake()->numberBetween(1990, 2030);

        return [
            'university_id' => University::factory(),
            'name' => fake()->words(3, true),
            'short_name' => $shortName,
            'nickname' => null,
            'year' => $year,
            'slug' => Str::slug("{$shortName}-class-of-{$year}"),
            'shortcode' => Str::lower(Str::random(8)),
            'admin_id' => User::factory()->admin(),
            'created_by' => User::factory()->superadmin(),
        ];
    }
}
