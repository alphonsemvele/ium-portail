<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documents demandes au personnel enseignant.
 *
 * L'administration ouvre une demande (« syllabus du semestre 1 ») et la
 * dirige vers un departement, une filiere, une specialite, ou tout le monde.
 * Chaque enseignant concerne la voit dans son espace, y depose ses pieces,
 * et l'administration accepte ou refuse.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_document', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 150);
            $table->text('description')->nullable();
            // A qui on demande. « tous » = l'ensemble du personnel.
            $table->enum('cible_type', ['tous', 'departement', 'filiere', 'specialite'])->default('tous');
            $table->unsignedBigInteger('cible_id')->nullable();
            $table->string('annee_academique', 20);
            $table->date('date_limite')->nullable();
            $table->boolean('obligatoire')->default(true);
            // Un syllabus suffit ; une progression + un sujet d'examen, non.
            $table->boolean('plusieurs_fichiers')->default(false);
            // Extensions acceptees, separees par des virgules. Vide = celles
            // par defaut (voir config/documents.php).
            $table->string('formats', 120)->nullable();
            $table->enum('statut', ['ouverte', 'fermee'])->default('ouverte');
            $table->unsignedBigInteger('cree_par')->nullable();
            $table->timestamps();

            $table->index(['annee_academique', 'statut']);
            $table->index(['cible_type', 'cible_id']);
        });

        Schema::create('depots_document', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_document_id')->constrained('demandes_document')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('statut', ['en_attente', 'accepte', 'refuse'])->default('en_attente');
            // Motif du refus : sans lui, l'enseignant redepose la meme chose.
            $table->text('motif')->nullable();
            $table->timestamp('depose_le')->nullable();
            $table->unsignedBigInteger('tranche_par')->nullable();
            $table->timestamp('tranche_le')->nullable();
            $table->timestamp('relance_le')->nullable();
            $table->timestamps();

            $table->unique(['demande_document_id', 'user_id'], 'depot_unicite');
        });

        Schema::create('fichiers_document', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depot_document_id')->constrained('depots_document')->cascadeOnDelete();
            // Range hors du dossier public : ces pieces sont internes.
            $table->string('chemin', 255);
            $table->string('nom_original', 180);
            $table->unsignedBigInteger('taille')->default(0);
            $table->string('mime', 120)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fichiers_document');
        Schema::dropIfExists('depots_document');
        Schema::dropIfExists('demandes_document');
    }
};
