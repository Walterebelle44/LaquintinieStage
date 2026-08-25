<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stagiaire_id')->constrained('stagiaires')->cascadeOnDelete();
            $table->enum('type', ['convention', 'rapport', 'memoire', 'attestation', 'livrable', 'autre']);
            $table->string('titre');
            $table->string('chemin_fichier');
            $table->unsignedBigInteger('taille_fichier')->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');
            $table->foreignId('valide_par_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('depose_par_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('commentaire')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
