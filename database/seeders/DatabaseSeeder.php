<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crée 5 utilisateurs de test
        $users = User::factory(5)->create();

        // 2. Crée un utilisateur spécifique pour vous connecter facilement
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'), // Votre mot de passe de test sera 'password'
        ]);

        // 3. Crée 15 articles liés au hasard aux 5 utilisateurs
        $articles = Article::factory(15)->create([
            'user_id' => fn () => $users->random()->id,
        ]);

        // 4. Crée 30 commentaires de visiteurs liés au hasard aux articles
        Comment::factory(30)->create([
            'article_id' => fn () => $articles->random()->id,
        ]);
    }
}
