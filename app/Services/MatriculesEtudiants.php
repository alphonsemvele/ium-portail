<?php

namespace App\Services;

use App\Models\User;
use App\Support\AnneeAcademique;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Attribution des matricules des etudiants de l'institut.
 *
 * Format : IUM-26-0147 — sigle de l'institut, annee d'entree sur deux
 * chiffres, sequence de quatre chiffres repartant a chaque rentree. C'est le
 * format arrete pour le groupe : le matricule d'un eleve porte toujours son
 * institut, celui du personnel jamais (le portail delivre ce dernier).
 *
 * L'annee est celle de l'entree dans l'etablissement, jamais celle de la
 * promotion de sortie : elle ne bouge plus, meme en cas de redoublement ou de
 * reorientation. Un numero n'est jamais reattribue, ni modifie.
 */
class MatriculesEtudiants
{
    /** Chiffres de la sequence, a l'interieur d'une annee. */
    public const LONGUEUR = 4;

    /** Les roles qui designent un etudiant dans cette base. */
    public const ROLES = ['student', 'etudiant'];

    /**
     * Le sigle porte par les matricules : « IUM », et non « IUM NDAZOA ».
     * Le matricule ne contient ni espace ni minuscule.
     */
    public function sigle(): string
    {
        $sigle = (string) config('etablissement.sigle_matricule')
            ?: (string) config('etablissement.sigle', 'IUM');

        return mb_strtoupper((string) strtok(trim($sigle), ' ')) ?: 'IUM';
    }

    /** Ce qui fait un matricule de l'institut : SIGLE-AA-NNNN. */
    public function gabarit(): string
    {
        return '/^'.preg_quote($this->sigle(), '/').'-(\d{2})-(\d{'.self::LONGUEUR.'})$/';
    }

    public function estConforme(?string $matricule): bool
    {
        return $matricule !== null && preg_match($this->gabarit(), $matricule) === 1;
    }

    /**
     * Sequence contenue dans un matricule, 0 s'il n'est pas a ce format.
     *
     * Un numero venu d'un autre plan ne compte pas dans la numerotation.
     */
    public function sequenceDe(?string $matricule): int
    {
        if (! $matricule || ! preg_match($this->gabarit(), $matricule, $trouve)) {
            return 0;
        }

        return (int) $trouve[2];
    }

    /** Annee portee par un matricule, null s'il n'en porte pas. */
    public function anneeDe(?string $matricule): ?int
    {
        if (! $matricule || ! preg_match($this->gabarit(), $matricule, $trouve)) {
            return null;
        }

        return 2000 + (int) $trouve[1];
    }

    public function formater(int $annee, int $sequence): string
    {
        return $this->sigle().'-'.$this->deuxChiffres($annee)
            .'-'.str_pad((string) $sequence, self::LONGUEUR, '0', STR_PAD_LEFT);
    }

    /** Le plus haut numero deja attribue pour cette annee, 0 si aucun. */
    public function dernierNumero(int $annee): int
    {
        $debut = $this->sigle().'-'.$this->deuxChiffres($annee).'-';

        return User::whereNotNull('matricule')
            ->where('matricule', 'like', $debut.'%')
            ->pluck('matricule')
            ->map(fn ($matricule) => $this->sequenceDe($matricule))
            ->max() ?? 0;
    }

    /** Le prochain numero libre de l'annee demandee. */
    public function prochain(?int $annee = null): string
    {
        $annee ??= $this->anneeCourante();

        return $this->formater($annee, $this->dernierNumero($annee) + 1);
    }

    /**
     * L'annee de la rentree en cours : un etudiant inscrit en fevrier 2027,
     * dans l'annee academique 2026/2027, entre en 2026 et non en 2027.
     */
    public function anneeCourante(): int
    {
        return (int) strtok(AnneeAcademique::courante(), '/');
    }

    /**
     * Annee d'entree retenue pour un etudiant : celle de la rentree de sa
     * premiere session d'examen, qui est ce que l'application sait de plus
     * proche de son arrivee ; a defaut, celle de la creation de son dossier.
     *
     * Elle ne bouge plus ensuite : ni au redoublement, ni a la reorientation.
     */
    public function anneeEntree(User $etudiant): int
    {
        $premiere = $etudiant->premiere_session ?? null;

        if ($premiere) {
            $annee = AnneeAcademique::pour(CarbonImmutable::parse($premiere));

            if ($annee) {
                return (int) strtok($annee, '/');
            }
        }

        if ($etudiant->created_at) {
            $annee = AnneeAcademique::pour($etudiant->created_at);

            if ($annee) {
                return (int) strtok($annee, '/');
            }
        }

        return $this->anneeCourante();
    }

    /**
     * Les etudiants de l'institut, dans l'ordre ou ils seront numerotes, avec
     * la date de leur premiere session d'examen : c'est elle qui dit l'annee
     * d'entree, sans une requete par etudiant.
     */
    public function etudiants(bool $seulementSansMatricule = true): Collection
    {
        $premiereSession = DB::table('notes')
            ->join('examens', 'examens.id', '=', 'notes.examen_id')
            ->selectRaw('min(examens.date)')
            ->whereColumn('notes.etudiant_id', 'users.id');

        return User::select('users.*')
            ->selectSub($premiereSession, 'premiere_session')
            ->whereIn('role', self::ROLES)
            ->when($seulementSansMatricule, fn ($q) => $q->where(function ($q) {
                $q->whereNull('matricule')->orWhere('matricule', '');
            }))
            ->orderBy('created_at')->orderBy('lastname')->orderBy('name')->orderBy('id')
            ->get();
    }

    /**
     * Attribue un matricule aux etudiants designes.
     *
     * Ceux qui portent deja un matricule au format de l'institut sont laisses
     * tranquilles : un numero ne change pas. Les autres — sans numero, ou avec
     * un numero venu d'un ancien plan — en recoivent un ; l'ancien reste brule,
     * il ne sera donne a personne d'autre. `$remplacer` renumerote tout le
     * monde, y compris les numeros conformes : a n'employer que sur ordre.
     *
     * @param  iterable<int, User>  $etudiants
     * @return array<int, array{id: int, nom: string, matricule: string, ancien: ?string}>
     */
    public function attribuer(iterable $etudiants, bool $remplacer = false): array
    {
        return DB::transaction(function () use ($etudiants, $remplacer) {
            $compteurs = [];
            $attribues = [];

            foreach ($etudiants as $etudiant) {
                if (! $remplacer && $this->estConforme($etudiant->matricule)) {
                    continue;
                }

                $annee = $this->anneeEntree($etudiant);
                $compteurs[$annee] ??= $this->dernierNumero($annee);

                $ancien = $etudiant->matricule;
                $etudiant->update(['matricule' => $this->formater($annee, ++$compteurs[$annee])]);

                $attribues[] = [
                    'id' => $etudiant->id,
                    'nom' => trim($etudiant->name.' '.$etudiant->lastname),
                    'matricule' => $etudiant->matricule,
                    'ancien' => $ancien,
                ];
            }

            return $attribues;
        });
    }

    private function deuxChiffres(int $annee): string
    {
        return str_pad((string) ($annee % 100), 2, '0', STR_PAD_LEFT);
    }
}
