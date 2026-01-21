<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request de validation pour le résumé de texte.
 */
class SummarizeRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'min:50', 'max:10000'],
        ];
    }

    /**
     * Messages d'erreur personnalisés.
     */
    public function messages(): array
    {
        return [
            'text.required' => 'Le texte à résumer est requis.',
            'text.min' => 'Le texte doit contenir au moins 50 caractères.',
            'text.max' => 'Le texte ne peut pas dépasser 10000 caractères.',
        ];
    }
}
