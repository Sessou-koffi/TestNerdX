<?php

namespace Tests\Unit;

use App\Models\AIRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests unitaires pour le modèle User.
 */
class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de création d'un utilisateur avec les valeurs par défaut.
     */
    public function test_user_has_default_plan_and_quota(): void
    {
        $user = User::factory()->create();

        $this->assertEquals('free', $user->plan);
        $this->assertEquals(10, $user->ai_requests_remaining);
    }

    /**
     * Test de la méthode hasQuotaRemaining() quand il reste du quota.
     */
    public function test_has_quota_remaining_returns_true_when_quota_available(): void
    {
        $user = User::factory()->create(['ai_requests_remaining' => 5]);

        $this->assertTrue($user->hasQuotaRemaining());
    }

    /**
     * Test de la méthode hasQuotaRemaining() quand le quota est épuisé.
     */
    public function test_has_quota_remaining_returns_false_when_quota_exhausted(): void
    {
        $user = User::factory()->create(['ai_requests_remaining' => 0]);

        $this->assertFalse($user->hasQuotaRemaining());
    }

    /**
     * Test de la méthode decrementQuota().
     */
    public function test_decrement_quota_decreases_remaining_requests(): void
    {
        $user = User::factory()->create(['ai_requests_remaining' => 5]);

        $user->decrementQuota();

        $this->assertEquals(4, $user->fresh()->ai_requests_remaining);
    }

    /**
     * Test que decrementQuota() ne descend pas en dessous de zéro.
     */
    public function test_decrement_quota_does_not_go_below_zero(): void
    {
        $user = User::factory()->create(['ai_requests_remaining' => 0]);

        $user->decrementQuota();

        $this->assertEquals(0, $user->fresh()->ai_requests_remaining);
    }

    /**
     * Test de la relation aiRequests.
     */
    public function test_user_has_many_ai_requests(): void
    {
        $user = User::factory()->create();
        AIRequest::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->aiRequests);
        $this->assertInstanceOf(AIRequest::class, $user->aiRequests->first());
    }
}
