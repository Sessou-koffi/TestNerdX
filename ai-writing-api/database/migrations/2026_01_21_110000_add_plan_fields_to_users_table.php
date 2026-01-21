<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration pour ajouter les champs de plan et quota IA à la table users.
 * - plan : type d'abonnement de l'utilisateur (free, premium, etc.)
 * - ai_requests_remaining : nombre de requêtes IA restantes pour l'utilisateur
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Plan de l'utilisateur (free par défaut)
            $table->string('plan')->default('free')->after('password');

            // Quota de requêtes IA restantes (10 par défaut pour le plan free)
            $table->unsignedInteger('ai_requests_remaining')->default(10)->after('plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['plan', 'ai_requests_remaining']);
        });
    }
};
