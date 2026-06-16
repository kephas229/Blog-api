<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        $this->faker->locale = 'fr_FR';

        return [
            'title'             => $this->faker->sentence(8),
            'content'           => implode("\n\n", $this->faker->paragraphs(5)),
            'short_description' => $this->faker->sentence(20),
            'image'             => null,
            'user_id'           => \App\Models\User::factory(),
            'category_id'       => \App\Models\Category::factory(),
        ];
    }
}
