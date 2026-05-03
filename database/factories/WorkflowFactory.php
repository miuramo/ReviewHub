<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workflow>
 */
class WorkflowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'subject' => $this->faker->randomElement(['ec', 'aec', 'meta', 'rev1', 'rev2', 'rev3']),
            'task' => $this->faker->randomElement(['assign', 'approve', 'submit', 'confirm']),
            'object' => $this->faker->randomElement(['ec', 'aec', 'meta', 'rev1', 'rev2', 'rev3']),
            'num_of_days' => $this->faker->numberBetween(1, 30),
            'next_workflow_id' => [],
            'join' => [],
        ];
    }
}
