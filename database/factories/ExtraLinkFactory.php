<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExtraLink>
 */
class ExtraLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->words(2, true),
            'url' => fake()->url(),
            'color' => fake()->randomElement(['cyan', 'blue', 'green', 'red', 'yellow']),
            'role_name' => 'ec',
            'is_header' => true,
            'is_footer' => false,
        ];
    }

    public function footer(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_header' => false,
            'is_footer' => true,
        ]);
    }
}
