<?php

namespace Database\Factories;

use App\Models\AIRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory pour le modèle AIRequest.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AIRequest>
 */
class AIRequestFactory extends Factory
{
    /**
     * Le modèle associé à cette factory.
     */
    protected $model = AIRequest::class;

    /**
     * Définit l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement([
                AIRequest::TYPE_GENERATE,
                AIRequest::TYPE_SUMMARIZE,
                AIRequest::TYPE_REWRITE,
                AIRequest::TYPE_QUESTIONS,
            ]),
            'prompt' => fake()->paragraph(),
            'response' => null,
            'status' => AIRequest::STATUS_PENDING,
            'error_message' => null,
        ];
    }

    /**
     * État : requête complétée.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AIRequest::STATUS_COMPLETED,
            'response' => fake()->paragraphs(3, true),
        ]);
    }

    /**
     * État : requête échouée.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AIRequest::STATUS_FAILED,
            'error_message' => 'API Error: ' . fake()->sentence(),
        ]);
    }

    /**
     * État : type génération.
     */
    public function generate(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AIRequest::TYPE_GENERATE,
        ]);
    }

    /**
     * État : type résumé.
     */
    public function summarize(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AIRequest::TYPE_SUMMARIZE,
        ]);
    }
}
