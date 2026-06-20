<?php

namespace Database\Factories;

use App\Models\Forum;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Forum>
 */
class ForumFactory extends Factory
{
    protected $model = Forum::class;

    public function definition(): array
    {
        return [
            'post_id'  => Post::factory()->create()->id,
            'user_id'  => User::factory(),

            'title'    => fake()->sentence(4),
            'isclose'  => false,
        ];
    }

    /**
     * 指定した日時で作成されたフォーラムを定義する。
     * テストで年度を固定するために使用する。
     */
    public function createdAt(\DateTimeInterface|string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }
}
