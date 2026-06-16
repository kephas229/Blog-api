<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
{
    /**
     * Autoriser l'utilisateur à faire cette requête.
     */
    public function authorize(): bool
    {
        return true; // <-- OBLIGATOIRE : Changez false par true pour activer la validation
    }

    /**
     * Définir les règles de validation qui s'appliquent à la requête de modification.
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:191', // Vérifié uniquement si fourni, ne doit pas être vide
            'content' => 'sometimes|required|string',       // Vérifié uniquement si fourni
            'short_description' => 'sometimes|required|string|max:255', // Vérifié uniquement si fourni
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // L'image reste facultative
        ];
    }

    /**
     * Personnaliser les messages d'erreur (Optionnel)
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Le titre ne peut pas être vidé.',
            'title.max' => 'Le titre ne doit pas dépasser 191 caractères.',
            'content.required' => 'Le contenu ne peut pas être vidé.',
            'short_description.required' => 'La description courte ne peut pas être vidée.',
            'short_description.max' => 'La description courte ne doit pas dépasser 255 caractères.',
            'image.image' => 'Le fichier doit être une image valide.',
            'image.max' => 'L\'image ne doit pas dépasser 2 Mo.',
        ];
    }
}
