<?php

namespace Database\Factories;

use App\Models\Forum;
use App\Models\ForumMes;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ForumMes>
 */
class ForumMesFactory extends Factory
{
    protected $model = ForumMes::class;

    public function definition(): array
    {
        return [
            'forum_id' => Forum::factory(),
            'user_id'  => User::factory(),
            'subject'  => fake()->sentence(3),
            'mes'      => fake()->paragraph(),
        ];
    }
}
