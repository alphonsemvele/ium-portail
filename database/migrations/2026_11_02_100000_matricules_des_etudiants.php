<?php

use App\Services\MatriculesEtudiants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

/**
 * Applique aux etudiants deja enregistres le matricule du groupe :
 * IUM-26-0147 — sigle de l'institut, annee d'entree, sequence de l'annee.
 *
 * Les numeros venus des anciens plans (« AGR25-000001 », « ETU-WLWOYR ») sont
 * repris ; ceux qui sont deja au format restent tels quels. La migration peut
 * donc etre rejouee sans redistribuer les numeros.
 */
return new class extends Migration
{
    public function up(): void
    {
        $matricules = app(MatriculesEtudiants::class);

        // Tous les etudiants : la reprise touche aussi ceux qui portent deja
        // un numero, quand il ne suit pas le format arrete pour le groupe.
        $attribues = $matricules->attribuer($matricules->etudiants(false));

        Log::info('Matricules étudiants : '.count($attribues).' attribué(s).');
    }

    public function down(): void
    {
        // Un matricule ne se reprend pas : les anciens numeros ne sont pas
        // restaures, et les nouveaux restent attaches a leurs etudiants.
    }
};
