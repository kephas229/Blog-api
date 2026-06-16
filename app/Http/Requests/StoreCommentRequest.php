<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Autoriser l'utilisateur à faire cette requête.
     */
    public function authorize(): bool
    {
        return true; // <-- OBLIGATOIRE : Changez false par true pour activer la validation
    }

    /**
     * Définir les règles de validation qui s'appliquent à la requête.
     */
    public function rules(): array
    {
        return [
            'visitor_name' => 'required|string|max:191',
            'visitor_email' => 'required|email|max:191',
            'message' => 'required|string|max:1000',
            'article_id' => 'required|exists:articles,id',
        ];
    }


    /**
     * Personnaliser les messages d'erreur 
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Le contenu du commentaire est obligatoire.',
            'content.max' => 'Le commentaire ne peut pas dépasser 1000 caractères.',
            'article_id.required' => 'L\'identifiant de l\'article est obligatoire.',
            'article_id.exists' => 'L\'article sélectionné n\'existe pas.',
        ];
    }
}
