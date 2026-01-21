<?php

namespace App\Http\Controllers;

use App\Http\Requests\AI\GenerateTextRequest;
use App\Http\Requests\AI\QuestionsRequest;
use App\Http\Requests\AI\RewriteRequest;
use App\Http\Requests\AI\SummarizeRequest;
use App\Jobs\ProcessAIRequestJob;
use App\Models\AIRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller IA
 *
 * Gère toutes les requêtes liées aux fonctionnalités IA.
 * Les controllers sont fins : validation via FormRequest, logique dans les Services/Jobs.
 */
class AIController extends Controller
{
    /**
     * Génère du texte à partir d'un prompt.
     * POST /api/ai/generate-text
     *
     * @param GenerateTextRequest $request
     * @return JsonResponse
     */
    public function generateText(GenerateTextRequest $request): JsonResponse
    {
        return $this->processAIRequest(
            $request->user(),
            AIRequest::TYPE_GENERATE,
            $request->prompt
        );
    }

    /**
     * Résume un texte.
     * POST /api/ai/summarize
     *
     * @param SummarizeRequest $request
     * @return JsonResponse
     */
    public function summarize(SummarizeRequest $request): JsonResponse
    {
        return $this->processAIRequest(
            $request->user(),
            AIRequest::TYPE_SUMMARIZE,
            $request->text
        );
    }

    /**
     * Réécrit un texte.
     * POST /api/ai/rewrite
     *
     * @param RewriteRequest $request
     * @return JsonResponse
     */
    public function rewrite(RewriteRequest $request): JsonResponse
    {
        return $this->processAIRequest(
            $request->user(),
            AIRequest::TYPE_REWRITE,
            $request->text
        );
    }

    /**
     * Génère des questions à partir d'un texte.
     * POST /api/ai/questions
     *
     * @param QuestionsRequest $request
     * @return JsonResponse
     */
    public function questions(QuestionsRequest $request): JsonResponse
    {
        return $this->processAIRequest(
            $request->user(),
            AIRequest::TYPE_QUESTIONS,
            $request->text
        );
    }

    /**
     * Récupère l'historique des requêtes IA de l'utilisateur.
     * GET /api/ai/history
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function history(Request $request): JsonResponse
    {
        $requests = $request->user()
            ->aiRequests()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $requests->items(),
            'pagination' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    /**
     * Récupère le détail d'une requête IA spécifique.
     * GET /api/ai/request/{id}
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $aiRequest = AIRequest::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'data' => $aiRequest,
        ]);
    }

    /**
     * Méthode privée pour traiter une requête IA.
     * Centralise la logique commune à tous les endpoints.
     *
     * @param \App\Models\User $user
     * @param string $type
     * @param string $prompt
     * @return JsonResponse
     */
    private function processAIRequest($user, string $type, string $prompt): JsonResponse
    {
        // Vérification du quota
        if (!$user->hasQuotaRemaining()) {
            return response()->json([
                'message' => 'Quota de requêtes IA épuisé.',
                'ai_requests_remaining' => $user->ai_requests_remaining,
            ], 403);
        }

        // Création de la requête IA en base
        $aiRequest = AIRequest::create([
            'user_id' => $user->id,
            'type' => $type,
            'prompt' => $prompt,
            'status' => AIRequest::STATUS_PENDING,
        ]);

        // Dispatch du job pour traitement asynchrone
        ProcessAIRequestJob::dispatch($aiRequest);

        return response()->json([
            'message' => 'Requête IA créée avec succès. Traitement en cours.',
            'request_id' => $aiRequest->id,
            'status' => $aiRequest->status,
            'ai_requests_remaining' => $user->ai_requests_remaining - 1,
        ], 202);
    }
}
