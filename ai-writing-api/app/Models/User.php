<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'plan',
        'ai_requests_remaining',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ai_requests_remaining' => 'integer',
        ];
    }

    public function aiRequests(): HasMany
    {
        return $this->hasMany(AIRequest::class);
    }

    public function hasQuotaRemaining(): bool
    {
        return $this->ai_requests_remaining > 0;
    }

    public function decrementQuota(): void
    {
        if ($this->ai_requests_remaining > 0) {
            $this->decrement('ai_requests_remaining');
        }
    }
}
