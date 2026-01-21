<?php

namespace Tests\Unit;

use App\Models\AIRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

## Tests unitaires pour le modèle AIRequest.

class AIRequestTest extends TestCase
{
    use RefreshDatabase;

    ## Test de création d'une requête IA.

    public function test_ai_request_can_be_created(): void
    {
        $user = User::factory()->create();

        $aiRequest = AIRequest::create([
            'user_id' => $user->id,
            'type' => AIRequest::TYPE_GENERATE,
            'prompt' => 'Test prompt',
            'status' => AIRequest::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('ai_requests', [
            'id' => $aiRequest->id,
            'type' => 'generate',
            'status' => 'pending',
        ]);
    }

    ## Test de la relation user.
    public function test_ai_request_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $aiRequest = AIRequest::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $aiRequest->user);
        $this->assertEquals($user->id, $aiRequest->user->id);
    }

    ## Test de la méthode isPending().
    public function test_is_pending_returns_correct_value(): void
    {
        $pending = AIRequest::factory()->create(['status' => AIRequest::STATUS_PENDING]);
        $completed = AIRequest::factory()->create(['status' => AIRequest::STATUS_COMPLETED]);

        $this->assertTrue($pending->isPending());
        $this->assertFalse($completed->isPending());
    }

    ## Test de la méthode isCompleted().
    public function test_is_completed_returns_correct_value(): void
    {
        $pending = AIRequest::factory()->create(['status' => AIRequest::STATUS_PENDING]);
        $completed = AIRequest::factory()->create(['status' => AIRequest::STATUS_COMPLETED]);

        $this->assertFalse($pending->isCompleted());
        $this->assertTrue($completed->isCompleted());
    }

    ## Test de la méthode isFailed().
    public function test_is_failed_returns_correct_value(): void
    {
        $pending = AIRequest::factory()->create(['status' => AIRequest::STATUS_PENDING]);
        $failed = AIRequest::factory()->create(['status' => AIRequest::STATUS_FAILED]);

        $this->assertFalse($pending->isFailed());
        $this->assertTrue($failed->isFailed());
    }

   ## Test de la méthode markAsCompleted().
    public function test_mark_as_completed_updates_status_and_response(): void
    {
        $aiRequest = AIRequest::factory()->create([
            'status' => AIRequest::STATUS_PENDING,
            'response' => null,
        ]);

        $aiRequest->markAsCompleted('This is the AI response');

        $aiRequest->refresh();
        $this->assertEquals(AIRequest::STATUS_COMPLETED, $aiRequest->status);
        $this->assertEquals('This is the AI response', $aiRequest->response);
    }

    ## Test de la méthode markAsFailed().

    public function test_mark_as_failed_updates_status_and_error_message(): void
    {
        $aiRequest = AIRequest::factory()->create([
            'status' => AIRequest::STATUS_PENDING,
        ]);

        $aiRequest->markAsFailed('API Error: Rate limit exceeded');

        $aiRequest->refresh();
        $this->assertEquals(AIRequest::STATUS_FAILED, $aiRequest->status);
        $this->assertEquals('API Error: Rate limit exceeded', $aiRequest->error_message);
    }

    ## Test des constantes de type.
    public function test_type_constants_are_defined(): void
    {
        $this->assertEquals('generate', AIRequest::TYPE_GENERATE);
        $this->assertEquals('summarize', AIRequest::TYPE_SUMMARIZE);
        $this->assertEquals('rewrite', AIRequest::TYPE_REWRITE);
        $this->assertEquals('questions', AIRequest::TYPE_QUESTIONS);
    }

   ## Test des constantes de statut.
    public function test_status_constants_are_defined(): void
    {
        $this->assertEquals('pending', AIRequest::STATUS_PENDING);
        $this->assertEquals('completed', AIRequest::STATUS_COMPLETED);
        $this->assertEquals('failed', AIRequest::STATUS_FAILED);
    }
}
