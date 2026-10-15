<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Le syllabus d'une matiere : la description officielle du cours, telle que
 * l'institut la demande a chaque enseignant en debut de semestre.
 *
 * Les champs suivent le document officiel (en-tete bilingue, identification,
 * objectifs, competences, contenu, evaluation, bibliographie). L'enseignant
 * le redige dans la plateforme — ou y joint son fichier — et le chef du
 * departement de la matiere est prevenu par courriel des qu'il est soumis.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('syllabus', function (Blueprint $table) {
            $table->id();

            // L'enseignant qui le depose, et la matiere decrite.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('cour_id');

            /*
             * La chaine academique recopiee au depot, comme pour les
             * affectations : elle sert a retrouver le chef de departement et
             * a filtrer, sans refaire la remontee a chaque affichage.
             */
            $table->unsignedBigInteger('ue_id')->nullable();
            $table->unsignedBigInteger('specialite_id')->nullable();
            $table->unsignedBigInteger('filiere_id')->nullable();
            $table->unsignedBigInteger('departement_id')->nullable();

            $table->string('annee_academique', 20);
            $table->unsignedTinyInteger('semestre')->default(1);
            // Tel qu'il est ecrit sur le document : « 2 », « BTS 1 ».
            $table->string('niveau', 80)->nullable();
            $table->string('langue', 40)->default('Français');

            // Identification : recopiee de la matiere, corrigible par l'enseignant.
            $table->unsignedSmallInteger('credits')->nullable();
            $table->unsignedSmallInteger('volume_total')->nullable();
            $table->unsignedSmallInteger('volume_cm')->nullable();
            $table->unsignedSmallInteger('volume_td')->nullable();
            $table->unsignedSmallInteger('volume_tp')->nullable();
            $table->unsignedSmallInteger('volume_tpe')->nullable();

            // Equipe pedagogique : [{nom, charge}] — le document en prevoit trois.
            $table->json('equipe')->nullable();

            $table->text('objectifs')->nullable();
            $table->text('competences')->nullable();
            $table->text('outils')->nullable();
            $table->text('prerequis')->nullable();
            $table->text('resume')->nullable();
            $table->text('contenu')->nullable();
            $table->text('mode_enseignement')->nullable();
            $table->text('mode_evaluation')->nullable();
            $table->text('bibliographie')->nullable();

            /*
             * Le fichier de l'enseignant, quand il prefere envoyer le sien.
             * Range hors du dossier public, comme les pieces des demandes de
             * documents : il ne sort que par une route authentifiee.
             */
            $table->string('fichier', 255)->nullable();
            $table->string('fichier_nom', 180)->nullable();
            $table->unsignedBigInteger('fichier_taille')->nullable();

            $table->enum('statut', ['brouillon', 'soumis', 'valide', 'a_revoir'])->default('brouillon');
            // Ce que le chef de departement demande de reprendre.
            $table->text('motif')->nullable();
            $table->timestamp('soumis_le')->nullable();
            $table->unsignedBigInteger('tranche_par')->nullable();
            $table->timestamp('tranche_le')->nullable();
            // A qui le courriel de depot est parti, et quand : on l'affiche a
            // l'enseignant plutot que de le laisser dans l'incertitude.
            $table->string('notifie_a', 180)->nullable();
            $table->timestamp('notifie_le')->nullable();

            $table->timestamps();

            // Un seul syllabus par matiere, par semestre et par annee.
            $table->unique(['cour_id', 'annee_academique', 'semestre'], 'syllabus_matiere_unicite');
            $table->index(['departement_id', 'annee_academique', 'statut'], 'syllabus_departement');
            $table->index(['user_id', 'annee_academique']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('syllabus');
    }
};
