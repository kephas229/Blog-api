<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6), // Un faux titre de 6 mots
            'content' => $this->faker->paragraphs(3, true), // Un faux texte de 3 paragraphes
            'short_description' => $this->faker->sentence(15), // Une fausse description courte de 15 mots
            'image' => null, // Pas d'image par défaut pour les tests
            'user_id' => \App\Models\User::factory(), // Crée un utilisateur automatiquement pour cet article
        ];
    }

}
