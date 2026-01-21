<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request de validation pour la génération de texte.
 */
class GenerateTextRequest extends FormRequest
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
            'prompt' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    /**
     * Messages d'erreur personnalisés.
     */
    public function messages(): array
    {
        return [
            'prompt.required' => 'Le prompt est requis.',
            'prompt.min' => 'Le prompt doit contenir au moins 10 caractères.',
            'prompt.max' => 'Le prompt ne peut pas dépasser 5000 caractères.',
        ];
    }
}
