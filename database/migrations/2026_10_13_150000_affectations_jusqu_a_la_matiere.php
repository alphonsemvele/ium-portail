<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La charge descend jusqu'a la matiere.
 *
 * La chaine de l'institut va du cycle a la matiere, en passant par le
 * departement, la filiere, la specialite et l'unite d'enseignement. On
 * s'arretait a la specialite ; or ce qu'on confie a un enseignant, ce sont
 * une ou plusieurs matieres.
 *
 * Les colonnes parentes restent remplies par deduction : affecter quelqu'un a
 * une matiere l'inscrit a son UE, a sa specialite, a sa filiere et a son
 * departement. « Tout le personnel du departement » reste une seule requete.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('affectations', 'ue_id')) {
            Schema::table('affectations', function (Blueprint $table) {
                $table->unsignedBigInteger('ue_id')->nullable()->after('specialite_id');
                $table->unsignedBigInteger('cour_id')->nullable()->after('ue_id');
                $table->index(['annee_academique', 'ue_id']);
                $table->index(['annee_academique', 'cour_id']);
            });
        }

        DB::statement("ALTER TABLE `affectations`
            MODIFY `niveau` ENUM('departement','filiere','specialite','ue','cours') NOT NULL");

        // L'unicite porte desormais sur la chaine entiere.
        $index = collect(DB::select('SHOW INDEX FROM `affectations`'))
            ->pluck('Key_name')->unique();

        /*
         * La cle etrangere user_id s'appuyait sur cet index unique, faute d'un
         * autre ou elle figure en tete : on lui en donne un avant de retirer
         * celui-la, sinon MySQL refuse.
         */
        if (! $index->contains('affectations_user_id_index')) {
            DB::statement('ALTER TABLE `affectations` ADD INDEX `affectations_user_id_index` (`user_id`)');
        }

        if ($index->contains('affectations_unicite')) {
            DB::statement('ALTER TABLE `affectations` DROP INDEX `affectations_unicite`');
        }

        DB::statement('ALTER TABLE `affectations` ADD UNIQUE `affectations_unicite`
            (`user_id`, `niveau`, `departement_id`, `filiere_id`, `specialite_id`, `ue_id`, `cour_id`, `annee_academique`)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `affectations` DROP INDEX `affectations_unicite`');

        Schema::table('affectations', function (Blueprint $table) {
            $table->dropColumn(['ue_id', 'cour_id']);
        });

        DB::statement("ALTER TABLE `affectations`
            MODIFY `niveau` ENUM('departement','filiere','specialite') NOT NULL");

        DB::statement('ALTER TABLE `affectations` ADD UNIQUE `affectations_unicite`
            (`user_id`, `niveau`, `departement_id`, `filiere_id`, `specialite_id`, `annee_academique`)');
    }
};
