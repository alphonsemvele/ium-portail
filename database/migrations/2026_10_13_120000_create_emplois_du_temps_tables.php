<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Emploi du temps hebdomadaire, calque sur le document officiel de l'institut :
 * une specialite, un niveau, un semestre, une semaine — et une grille de
 * jours x creneaux dont chaque case porte une matiere, un enseignant, une
 * salle et le numero de la seance dans son volume horaire (« 1/5 »).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emplois_du_temps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('specialite_id');
            // Tel qu'il est ecrit sur le document : « BTS 1 - IRT ».
            $table->string('niveau', 80);
            $table->unsignedTinyInteger('semestre')->default(1);
            $table->string('annee_academique', 20);
            $table->date('date_debut');
            $table->date('date_fin');
            /*
             * Les creneaux de CET emploi du temps. Ils sont copies de la
             * configuration a la creation, puis lui appartiennent : une
             * specialite qui finit a 18h ne doit pas deformer les autres.
             */
            $table->json('creneaux');
            $table->enum('statut', ['brouillon', 'publie'])->default('brouillon');
            $table->timestamp('publie_le')->nullable();
            $table->unsignedBigInteger('cree_par')->nullable();
            $table->timestamps();

            $table->index(['annee_academique', 'specialite_id']);
            $table->index(['date_debut', 'date_fin']);
            $table->unique(['specialite_id', 'niveau', 'semestre', 'date_debut'], 'emploi_semaine_unicite');
        });

        Schema::create('seances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emploi_du_temps_id')->constrained('emplois_du_temps')->cascadeOnDelete();
            // 1 = lundi … 6 = samedi (config/emploi_du_temps.php).
            $table->unsignedTinyInteger('jour');
            // Rang du creneau dans la grille de l'emploi du temps.
            $table->unsignedTinyInteger('creneau');
            // Horaires recopies a l'enregistrement : la seance garde son heure
            // meme si la grille est retouchee plus tard.
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->enum('type', ['cours', 'tpe', 'activite', 'autre'])->default('cours');
            $table->unsignedBigInteger('cour_id')->nullable();
            // Ce qui s'affiche quand il n'y a pas de matiere au catalogue :
            // « TPE », « Activite scolaire », ou un intitule libre.
            $table->string('intitule', 150)->nullable();
            $table->unsignedBigInteger('enseignant_id')->nullable();
            $table->unsignedBigInteger('salle_id')->nullable();
            // Le document ecrit « Salle 23 (TC) » : toutes les salles ne sont
            // pas au catalogue, on accepte le texte libre.
            $table->string('salle_libelle', 80)->nullable();
            // « 1/5 » : la seance et le nombre total prevu pour la matiere.
            $table->unsignedSmallInteger('seance_numero')->nullable();
            $table->unsignedSmallInteger('seance_total')->nullable();
            $table->timestamps();

            $table->index('enseignant_id');
            $table->unique(['emploi_du_temps_id', 'jour', 'creneau'], 'seance_case_unicite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seances');
        Schema::dropIfExists('emplois_du_temps');
    }
};
