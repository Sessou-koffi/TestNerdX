<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration pour créer la table ai_requests.
 * Cette table stocke toutes les requêtes IA effectuées par les utilisateurs.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_requests', function (Blueprint $table) {
            $table->id();

            // Relation avec l'utilisateur
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Type de requête (generate, summarize, rewrite, questions)
            $table->string('type');

            // Le prompt envoyé par l'utilisateur
            $table->text('prompt');

            // La réponse de l'IA (nullable car en attente au départ)
            $table->text('response')->nullable();

            // Statut de la requête : pending, completed, failed
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');

            // Message d'erreur en cas d'échec
            $table->text('error_message')->nullable();

            $table->timestamps();

            // Index pour améliorer les performances des requêtes
            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_requests');
    }
};
