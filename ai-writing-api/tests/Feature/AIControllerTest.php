<?php

namespace Tests\Feature;

use App\Jobs\ProcessAIRequestJob;
use App\Models\AIRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Tests fonctionnels pour les endpoints IA.
 */
class AIControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'ai_requests_remaining' => 10,
        ]);
    }

    /**
     * Test de génération de texte avec succès.
     */
    public function test_user_can_generate_text(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/ai/generate-text', [
                'prompt' => 'Écris un article sur Laravel et ses avantages.',
            ]);

        $response->assertStatus(202)
            ->assertJsonStructure([
                'message',
                'request_id',
                'status',
                'ai_requests_remaining',
            ]);

        // Vérifie que le job a été dispatché
        Queue::assertPushed(ProcessAIRequestJob::class);

        // Vérifie que la requête est en base
        $this->assertDatabaseHas('ai_requests', [
            'user_id' => $this->user->id,
            'type' => 'generate',
            'status' => 'pending',
        ]);
    }

    /**
     * Test de résumé de texte.
     */
    public function test_user_can_summarize_text(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/ai/summarize', [
                'text' => 'Laravel est un framework PHP open source créé par Taylor Otwell. Il offre de nombreuses fonctionnalités pour le développement web moderne.',
            ]);

        $response->assertStatus(202);
        Queue::assertPushed(ProcessAIRequestJob::class);

        $this->assertDatabaseHas('ai_requests', [
            'user_id' => $this->user->id,
            'type' => 'summarize',
        ]);
    }

    /**
     * Test de réécriture de texte.
     */
    public function test_user_can_rewrite_text(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/ai/rewrite', [
                'text' => 'Le développement web c\'est cool et Laravel aide beaucoup.',
            ]);

        $response->assertStatus(202);
        Queue::assertPushed(ProcessAIRequestJob::class);

        $this->assertDatabaseHas('ai_requests', [
            'user_id' => $this->user->id,
            'type' => 'rewrite',
        ]);
    }

    /**
     * Test de génération de questions.
     */
    public function test_user_can_generate_questions(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/ai/questions', [
                'text' => 'Laravel utilise le pattern MVC et dispose d\'un ORM appelé Eloquent pour la gestion de base de données.',
            ]);

        $response->assertStatus(202);
        Queue::assertPushed(ProcessAIRequestJob::class);

        $this->assertDatabaseHas('ai_requests', [
            'user_id' => $this->user->id,
            'type' => 'questions',
        ]);
    }

    /**
     * Test de refus quand le quota est épuisé.
     */
    public function test_user_cannot_make_request_when_quota_exhausted(): void
    {
        $this->user->update(['ai_requests_remaining' => 0]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/ai/generate-text', [
                'prompt' => 'Écris un article sur Laravel.',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Quota de requêtes IA épuisé.',
                'ai_requests_remaining' => 0,
            ]);
    }

    /**
     * Test de validation du prompt (trop court).
     */
    public function test_generate_text_requires_valid_prompt(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/ai/generate-text', [
                'prompt' => 'Court',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['prompt']);
    }

    /**
     * Test de validation du texte à résumer (trop court).
     */
    public function test_summarize_requires_valid_text(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/ai/summarize', [
                'text' => 'Texte trop court.',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    }

    /**
     * Test de l'historique des requêtes.
     */
    public function test_user_can_get_history(): void
    {
        // Crée quelques requêtes pour l'utilisateur
        AIRequest::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/ai/history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test que l'utilisateur ne voit que ses propres requêtes.
     */
    public function test_user_can_only_see_own_requests(): void
    {
        $otherUser = User::factory()->create();

        AIRequest::factory()->count(2)->create(['user_id' => $this->user->id]);
        AIRequest::factory()->count(3)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/ai/history');

        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test de récupération du détail d'une requête.
     */
    public function test_user_can_get_request_detail(): void
    {
        $aiRequest = AIRequest::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'generate',
            'prompt' => 'Test prompt',
            'status' => 'completed',
            'response' => 'Test response',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/ai/request/{$aiRequest->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $aiRequest->id)
            ->assertJsonPath('data.type', 'generate');
    }

    /**
     * Test qu'un utilisateur ne peut pas voir la requête d'un autre.
     */
    public function test_user_cannot_access_other_user_request(): void
    {
        $otherUser = User::factory()->create();
        $aiRequest = AIRequest::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/ai/request/{$aiRequest->id}");

        $response->assertStatus(404);
    }

    /**
     * Test d'accès aux endpoints sans authentification.
     */
    public function test_unauthenticated_user_cannot_access_ai_endpoints(): void
    {
        $this->postJson('/api/ai/generate-text', ['prompt' => 'Test'])
            ->assertStatus(401);

        $this->getJson('/api/ai/history')
            ->assertStatus(401);
    }
}
