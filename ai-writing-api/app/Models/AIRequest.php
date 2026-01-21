<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle AIRequest
 *
 * Représente une requête IA effectuée par un utilisateur.
 * Stocke le prompt, la réponse et le statut de chaque requête.
 */
class AIRequest extends Model
{
    use HasFactory;

    /**
     * Nom de la table associée.
     */
    protected $table = 'ai_requests';

    /**
     * Les attributs assignables en masse.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'prompt',
        'response',
        'status',
        'error_message',
    ];

    /**
     * Les types de requêtes IA disponibles.
     */
    public const TYPE_GENERATE = 'generate';
    public const TYPE_SUMMARIZE = 'summarize';
    public const TYPE_REWRITE = 'rewrite';
    public const TYPE_QUESTIONS = 'questions';

    /**
     * Les statuts possibles d'une requête.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    /**
     * Relation : une requête appartient à un utilisateur.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vérifie si la requête est en attente.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Vérifie si la requête est terminée.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Vérifie si la requête a échoué.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Marque la requête comme terminée avec la réponse.
     */
    public function markAsCompleted(string $response): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'response' => $response,
        ]);
    }

    /**
     * Marque la requête comme échouée avec un message d'erreur.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }
}
