<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service OpenAI
 *
 * Centralise tous les appels à l'API OpenAI.
 * Utilise les variables d'environnement pour la configuration.
 */
class OpenAIService
{
    /**
     * URL de base de l'API OpenAI.
     */
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';

    /**
     * Clé API OpenAI.
     */
    protected string $apiKey;

    /**
     * Modèle OpenAI à utiliser.
     */
    protected string $model;

    /**
     * Constructeur - Initialise la configuration depuis l'environnement.
     */
    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', '');
        $this->model = config('services.openai.model', 'gpt-3.5-turbo');
    }

    /**
     * Envoie un prompt à l'API OpenAI et retourne la réponse.
     *
     * @param string $prompt Le prompt à envoyer
     * @param string|null $systemPrompt Instructions système optionnelles
     * @return string La réponse de l'IA
     * @throws Exception En cas d'erreur API
     */
    public function sendPrompt(string $prompt, ?string $systemPrompt = null): string
    {
        // Vérification de la clé API
        if (empty($this->apiKey)) {
            throw new Exception('La clé API OpenAI n\'est pas configurée.');
        }

        // Construction des messages
        $messages = [];

        if ($systemPrompt) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt,
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $prompt,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(60)
            ->post($this->apiUrl, [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => config('services.openai.max_tokens', 2000),
                'temperature' => config('services.openai.temperature', 0.7),
            ]);

            // Vérification de la réponse
            if (!$response->successful()) {
                Log::error('OpenAI API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('Erreur API OpenAI: ' . $response->status());
            }

            $data = $response->json();

            // Extraction du contenu de la réponse
            if (!isset($data['choices'][0]['message']['content'])) {
                throw new Exception('Format de réponse OpenAI invalide.');
            }

            return trim($data['choices'][0]['message']['content']);

        } catch (Exception $e) {
            Log::error('OpenAI Service Error', [
                'message' => $e->getMessage(),
                'prompt' => substr($prompt, 0, 100), // Log partiel pour debug
            ]);
            throw $e;
        }
    }

    /**
     * Génère du texte à partir d'un sujet.
     */
    public function generateText(string $prompt): string
    {
        $systemPrompt = "Tu es un assistant de rédaction professionnel. Génère du contenu clair, bien structuré et de qualité.";
        return $this->sendPrompt($prompt, $systemPrompt);
    }

    /**
     * Résume un texte.
     */
    public function summarize(string $text): string
    {
        $systemPrompt = "Tu es un expert en synthèse. Résume le texte suivant de manière concise tout en conservant les informations essentielles.";
        return $this->sendPrompt($text, $systemPrompt);
    }

    /**
     * Réécrit un texte.
     */
    public function rewrite(string $text): string
    {
        $systemPrompt = "Tu es un expert en réécriture. Reformule le texte suivant en améliorant sa clarté et son style, tout en conservant le sens original.";
        return $this->sendPrompt($text, $systemPrompt);
    }

    /**
     * Génère des questions à partir d'un texte.
     */
    public function generateQuestions(string $text): string
    {
        $systemPrompt = "Tu es un expert pédagogique. Génère des questions pertinentes et variées basées sur le texte suivant pour tester la compréhension.";
        return $this->sendPrompt($text, $systemPrompt);
    }
}
