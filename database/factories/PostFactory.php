<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $published = fake()->boolean(70);

        return [
            'user_id' => User::factory(),
            'body' => fake()->optional()->paragraph(),
            'published' => $published,
            'published_at' => $published ? now() : null,
        ];
    }
}
