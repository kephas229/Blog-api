<?php

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'visitor_name' => $this->faker->name(), // Génère un faux nom (ex: Jean Dupont)
            'visitor_email' => $this->faker->safeEmail(), // Génère un faux email valide
            'message' => $this->faker->sentence(12), // Génère un faux message de 12 mots
            'article_id' => \App\Models\Article::factory(), // Lie un article automatiquement
        ];
    }


}
