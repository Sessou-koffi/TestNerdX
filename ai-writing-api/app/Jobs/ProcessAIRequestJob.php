<?php

namespace App\Jobs;

use App\Models\AIRequest;
use App\Models\User;
use App\Services\OpenAIService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job ProcessAIRequestJob
 *
 * Traite une requête IA de manière asynchrone.
 * - Appelle le service OpenAI approprié selon le type
 * - Met à jour le statut de la requête
 * - Décrémente le quota utilisateur
 */
class ProcessAIRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Nombre de tentatives en cas d'échec.
     */
    public int $tries = 3;

    /**
     * Délai avant une nouvelle tentative (secondes).
     */
    public int $backoff = 30;

    /**
     * Timeout du job (secondes).
     */
    public int $timeout = 120;

    /**
     * Constructeur du job.
     *
     * @param AIRequest $aiRequest La requête IA à traiter
     */
    public function __construct(
        public AIRequest $aiRequest
    ) {}

    /**
     * Exécute le job.
     */
    public function handle(OpenAIService $openAIService): void
    {
        Log::info('Processing AI request', [
            'request_id' => $this->aiRequest->id,
            'type' => $this->aiRequest->type,
        ]);

        try {
            // Appel au service OpenAI selon le type de requête
            $response = match ($this->aiRequest->type) {
                AIRequest::TYPE_GENERATE => $openAIService->generateText($this->aiRequest->prompt),
                AIRequest::TYPE_SUMMARIZE => $openAIService->summarize($this->aiRequest->prompt),
                AIRequest::TYPE_REWRITE => $openAIService->rewrite($this->aiRequest->prompt),
                AIRequest::TYPE_QUESTIONS => $openAIService->generateQuestions($this->aiRequest->prompt),
                default => throw new Exception("Type de requête invalide: {$this->aiRequest->type}"),
            };

            // Mise à jour de la requête avec la réponse
            $this->aiRequest->markAsCompleted($response);

            // Décrémentation du quota utilisateur
            $this->aiRequest->user->decrementQuota();

            Log::info('AI request completed successfully', [
                'request_id' => $this->aiRequest->id,
            ]);

        } catch (Exception $e) {
            Log::error('AI request failed', [
                'request_id' => $this->aiRequest->id,
                'error' => $e->getMessage(),
            ]);

            // Marquer la requête comme échouée
            $this->aiRequest->markAsFailed($e->getMessage());

            // Re-lever l'exception pour permettre les retries si configuré
            throw $e;
        }
    }

    /**
     * Gère l'échec final du job après toutes les tentatives.
     */
    public function failed(Exception $exception): void
    {
        Log::error('AI request job failed permanently', [
            'request_id' => $this->aiRequest->id,
            'error' => $exception->getMessage(),
        ]);

        // S'assurer que la requête est marquée comme échouée
        $this->aiRequest->markAsFailed('Échec après plusieurs tentatives: ' . $exception->getMessage());
    }
}
