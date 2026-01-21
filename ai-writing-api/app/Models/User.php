<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modèle User
 *
 * Représente un utilisateur de l'API SaaS.
 * Gère l'authentification API via Sanctum et le quota de requêtes IA.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Les attributs assignables en masse.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'plan',
        'ai_requests_remaining',
    ];

    /**
     * Les attributs cachés lors de la sérialisation.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Les attributs à caster.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ai_requests_remaining' => 'integer',
        ];
    }

    /**
     * Relation : un utilisateur a plusieurs requêtes IA.
     */
    public function aiRequests(): HasMany
    {
        return $this->hasMany(AIRequest::class);
    }

    /**
     * Vérifie si l'utilisateur a encore du quota disponible.
     */
    public function hasQuotaRemaining(): bool
    {
        return $this->ai_requests_remaining > 0;
    }

    /**
     * Décrémente le quota de requêtes IA de l'utilisateur.
     */
    public function decrementQuota(): void
    {
        if ($this->ai_requests_remaining > 0) {
            $this->decrement('ai_requests_remaining');
        }
    }
}
