<?php

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        $this->faker->locale = 'fr_FR';

        return [
            'visitor_name'  => $this->faker->name(),
            'visitor_email' => $this->faker->safeEmail(),
            'message'       => $this->faker->paragraph(3),
            'article_id'    => \App\Models\Article::factory(),
        ];
    }
}
