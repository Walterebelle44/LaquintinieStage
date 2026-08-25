<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stagiaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('matricule')->unique();
            $table->enum('type_stage', ['academique', 'professionnel']);
            $table->foreignId('encadrant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('etablissement_id')->nullable()->constrained('etablissements')->nullOnDelete();

            // Parcours académique
            $table->string('maitre_stage_ecole_nom')->nullable();
            $table->string('maitre_stage_ecole_email')->nullable();
            $table->string('maitre_stage_ecole_telephone')->nullable();
            $table->boolean('convention_signee')->default(false);
            $table->string('niveau_etude')->nullable();

            // Parcours professionnel
            $table->text('referentiel_competences')->nullable();
            $table->string('employabilite_souhaitee')->nullable();

            $table->string('service')->nullable();
            $table->string('sujet')->nullable();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->enum('statut', ['en_attente', 'en_cours', 'termine', 'abandonne'])->default('en_attente');
            $table->text('notes_generales')->nullable();
            $table->timestamps();

            $table->index(['type_stage', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stagiaires');
    }
};
