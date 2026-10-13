<?php

namespace App\Support;

use App\Models\Cour;
use App\Models\Cycle;
use App\Models\Departement;
use App\Models\Filiere;
use App\Models\Specialite;
use App\Models\Ue;

/**
 * La cascade academique de l'institut : Cycle > Departement > Filiere >
 * Specialite > UE > Matiere. C'est la chaine que suivent deja les ecrans de
 * notes et de barbillard ; on la pose ici une fois pour que tous les ecrans
 * se comportent pareil.
 *
 * Chaque etage vide ceux qui le suivent : changer de departement sans vider
 * la filiere laissait choisir une filiere d'un autre departement.
 *
 * Le niveau de la selection est le dernier etage rempli — on s'arrete ou l'on
 * veut, et c'est ce qui decide de la portee d'une affectation ou d'une
 * demande de document.
 */
trait CascadeAcademique
{
    public $cycle_id = null;
    public $departement_id = null;
    public $filiere_id = null;
    public $specialite_id = null;
    public $ue_id = null;
    public $cour_id = null;

    /** @var iterable<int, Cycle> */
    public $cycles = [];
    /** @var iterable<int, Departement> */
    public $departements = [];
    /** @var iterable<int, Filiere> */
    public $filieres = [];
    /** @var iterable<int, Specialite> */
    public $specialites = [];
    /** @var iterable<int, Ue> */
    public $ues = [];
    /** @var iterable<int, Cour> */
    public $matieres = [];

    /** A appeler dans mount(). */
    public function initialiserCascade(): void
    {
        $this->cycles = Cycle::where('status', 'Success')->orderBy('name')->get();
        $this->departements = collect();
        $this->filieres = collect();
        $this->specialites = collect();
        $this->ues = collect();
        $this->matieres = collect();
    }

    public function updatedCycleId($value): void
    {
        $this->departement_id = null;
        $this->filiere_id = null;
        $this->specialite_id = null;
        $this->ue_id = null;
        $this->cour_id = null;

        $this->departements = $value
            ? Departement::where('cycle_id', $value)->where('status', 'Success')->orderBy('nom')->get()
            : collect();

        $this->filieres = collect();
        $this->specialites = collect();
        $this->ues = collect();
        $this->matieres = collect();
    }

    public function updatedDepartementId($value): void
    {
        $this->filiere_id = null;
        $this->specialite_id = null;
        $this->ue_id = null;
        $this->cour_id = null;

        $this->filieres = $value
            ? Filiere::where('departement_id', $value)->where('status', 'Success')->orderBy('name')->get()
            : collect();

        $this->specialites = collect();
        $this->ues = collect();
        $this->matieres = collect();
    }

    public function updatedFiliereId($value): void
    {
        $this->specialite_id = null;
        $this->ue_id = null;
        $this->cour_id = null;

        $this->specialites = $value
            ? Specialite::where('filiere_id', $value)->where('status', 'Success')->orderBy('name')->get()
            : collect();

        $this->ues = collect();
        $this->matieres = collect();
    }

    public function updatedSpecialiteId($value): void
    {
        $this->ue_id = null;
        $this->cour_id = null;

        $this->ues = $value
            ? Ue::where('specialite_id', $value)->where('status', 'Success')->orderBy('name')->get()
            : collect();

        // Sans UE choisie, on propose les matieres de la specialite : toutes
        // ne sont pas rattachees a une UE dans le catalogue.
        $this->matieres = $value
            ? Cour::where('specialite_id', $value)->where('status', '!=', 'failed')->orderBy('name')->get()
            : collect();
    }

    public function updatedUeId($value): void
    {
        $this->cour_id = null;

        $this->matieres = $value
            ? Cour::where('ue_id', $value)->where('status', '!=', 'failed')->orderBy('name')->get()
            : ($this->specialite_id
                ? Cour::where('specialite_id', $this->specialite_id)->where('status', '!=', 'failed')->orderBy('name')->get()
                : collect());
    }

    /**
     * Remet la cascade sur une selection existante — a l'ouverture d'une
     * fiche, il faut que les listes du dessus soient deja garnies, sinon les
     * deroulants s'affichent vides sur une valeur pourtant choisie.
     */
    public function rechargerCascade(?int $cycle, ?int $departement, ?int $filiere, ?int $specialite): void
    {
        $this->cycle_id = $cycle;
        $this->departements = $cycle
            ? Departement::where('cycle_id', $cycle)->where('status', 'Success')->orderBy('nom')->get()
            : collect();

        $this->departement_id = $departement;
        $this->filieres = $departement
            ? Filiere::where('departement_id', $departement)->where('status', 'Success')->orderBy('name')->get()
            : collect();

        $this->filiere_id = $filiere;
        $this->specialites = $filiere
            ? Specialite::where('filiere_id', $filiere)->where('status', 'Success')->orderBy('name')->get()
            : collect();

        $this->specialite_id = $specialite;
        $this->ues = $specialite
            ? Ue::where('specialite_id', $specialite)->where('status', 'Success')->orderBy('name')->get()
            : collect();
        $this->matieres = $specialite
            ? Cour::where('specialite_id', $specialite)->where('status', '!=', 'failed')->orderBy('name')->get()
            : collect();
    }

    public function viderCascade(): void
    {
        $this->cycle_id = null;
        $this->departement_id = null;
        $this->filiere_id = null;
        $this->specialite_id = null;
        $this->departements = collect();
        $this->filieres = collect();
        $this->specialites = collect();
        $this->ues = collect();
        $this->matieres = collect();
    }

    /**
     * Le dernier etage rempli : « cours », « ue », « specialite »,
     * « filiere », « departement », ou null si l'on n'a rien choisi.
     */
    public function niveauCascade(): ?string
    {
        return match (true) {
            filled($this->cour_id) => 'cours',
            filled($this->ue_id) => 'ue',
            filled($this->specialite_id) => 'specialite',
            filled($this->filiere_id) => 'filiere',
            filled($this->departement_id) => 'departement',
            default => null,
        };
    }

    /** L'identifiant correspondant au dernier etage rempli. */
    public function cibleCascade(): ?int
    {
        return match ($this->niveauCascade()) {
            'cours' => (int) $this->cour_id,
            'ue' => (int) $this->ue_id,
            'specialite' => (int) $this->specialite_id,
            'filiere' => (int) $this->filiere_id,
            'departement' => (int) $this->departement_id,
            default => null,
        };
    }
}
