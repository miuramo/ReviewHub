<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'name'      => fake()->jobTitle(),
            'rank'      => 1,
            'menutext'  => fake()->word(),
            'menulink'  => '/' . fake()->slug(2),
            'rolenames' => fake()->word(),
        ];
    }
}
