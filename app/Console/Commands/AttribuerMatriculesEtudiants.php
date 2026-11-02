<?php

namespace App\Console\Commands;

use App\Services\MatriculesEtudiants;
use Illuminate\Console\Command;

/**
 * Attribue aux etudiants leur matricule au format de l'institut.
 *
 *   php artisan matricules:etudiants              ceux qui n'en ont pas
 *   php artisan matricules:etudiants --reprendre  ceux d'un autre format aussi
 *   php artisan matricules:etudiants --simuler    sans rien enregistrer
 */
class AttribuerMatriculesEtudiants extends Command
{
    protected $signature = 'matricules:etudiants
                            {--reprendre : renumerote aussi les matricules venus d\'un autre plan}
                            {--simuler : affiche ce qui serait attribue, sans rien enregistrer}';

    protected $description = "Attribue aux etudiants un matricule au format SIGLE-AA-NNNN";

    public function handle(MatriculesEtudiants $matricules): int
    {
        $reprendre = (bool) $this->option('reprendre');

        // Sans --reprendre, seuls les etudiants sans matricule sont concernes.
        $etudiants = $matricules->etudiants(! $reprendre);

        if ($etudiants->isEmpty()) {
            $this->info('Aucun étudiant à numéroter.');

            return self::SUCCESS;
        }

        if ($this->option('simuler')) {
            $compteurs = [];
            $lignes = [];

            foreach ($etudiants as $etudiant) {
                if ($matricules->estConforme($etudiant->matricule)) {
                    continue;
                }

                $annee = $matricules->anneeEntree($etudiant);
                $compteurs[$annee] ??= $matricules->dernierNumero($annee);

                $lignes[] = [
                    trim($etudiant->name.' '.$etudiant->lastname),
                    $etudiant->matricule ?: '—',
                    $matricules->formater($annee, ++$compteurs[$annee]),
                ];
            }

            $this->table(['Étudiant', 'Actuel', 'Proposé'], $lignes);
            $this->info(count($lignes).' matricule(s) seraient attribués. Rien n\'a été enregistré.');

            return self::SUCCESS;
        }

        // Un matricule deja au format reste le sien, dans les deux cas.
        $attribues = $matricules->attribuer($etudiants);

        $this->table(
            ['Étudiant', 'Ancien', 'Matricule'],
            array_map(fn ($a) => [$a['nom'], $a['ancien'] ?: '—', $a['matricule']], $attribues)
        );

        $this->info(count($attribues).' matricule(s) attribué(s).');

        return self::SUCCESS;
    }
}
