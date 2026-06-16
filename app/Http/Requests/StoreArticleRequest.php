<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    /**
     * Autoriser l'utilisateur à faire cette requête.
     */
    public function authorize(): bool
    {
        return true; //  OBLIGATOIRE pour activer la validation
    }

    /**
     * Définir les règles de validation qui s'appliquent à la requête.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:191', // Le titre est obligatoire et fait moins de 191 caractères
            'content' => 'required|string',       // Le contenu textuel est obligatoire
            'short_description' => 'required|string|max:255', // La description courte est obligatoire et fait moins de 255 caractères
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // L'image est facultative, doit être une photo de 2 Mo maximum
            'category_id' => 'required|exists:categories,id', // L'identifiant de la catégorie est obligatoire et doit exister dans la table categories
        ];
    }

    /**
     * Personnaliser les messages d'erreur 
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Le titre de l\'article est obligatoire.',
            'title.max' => 'Le titre ne doit pas dépasser 191 caractères.',
            'content.required' => 'Le contenu de l\'article est obligatoire.',
            'short_description.required' => 'La description courte de l\'article est obligatoire.',
            'short_description.max' => 'La description courte ne doit pas dépasser 255 caractères.',
            'image.image' => 'Le fichier doit être une image valide.',
            'image.max' => 'L\'image ne doit pas dépasser 2 Mo.',
        ];
    }
}
