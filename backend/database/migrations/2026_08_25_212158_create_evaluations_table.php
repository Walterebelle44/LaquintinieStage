<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stagiaire_id')->constrained('stagiaires')->cascadeOnDelete();
            $table->foreignId('evaluateur_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['grille_academique', 'bilan_competences', 'suivi_mensuel']);
            $table->string('periode')->nullable(); // ex: "Mois 1", "Semestre 1"
            $table->json('criteres')->nullable(); // grille dynamique {critere: note}
            $table->decimal('note_globale', 4, 2)->nullable();
            $table->text('appreciation')->nullable();
            $table->text('points_forts')->nullable();
            $table->text('axes_amelioration')->nullable();
            $table->boolean('valide')->default(false);
            $table->date('date_evaluation');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
