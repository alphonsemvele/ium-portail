<?php

namespace App\Models;

use App\Support\AnneeAcademique;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Le syllabus d'une matiere : sa description officielle pour un semestre.
 *
 * @see database/migrations/2026_10_15_090000_create_syllabus_table.php
 */
class Syllabus extends Model
{
    protected $table = 'syllabus';

    /** Les etats, et ce qu'on en dit a l'ecran. */
    public const STATUTS = [
        'brouillon' => 'Brouillon',
        'soumis' => 'Soumis',
        'valide' => 'Validé',
        'a_revoir' => 'À revoir',
    ];

    /** Les sections redigees, dans l'ordre du document officiel. */
    public const SECTIONS = [
        'objectifs' => 'Objectifs pédagogiques',
        'competences' => 'Compétences à acquérir',
        'outils' => 'Outils pédagogiques',
        'prerequis' => 'Prérequis',
        'resume' => 'Résumé',
        'contenu' => 'Contenu (plan du cours + chronogramme)',
        'mode_enseignement' => "Mode d'enseignement",
        'mode_evaluation' => "Mode d'évaluation",
        'bibliographie' => 'Bibliographie',
    ];

    /** Sans celles-ci, le document ne decrit rien : on refuse de le soumettre. */
    public const SECTIONS_REQUISES = ['objectifs', 'competences', 'contenu', 'mode_evaluation'];

    protected $fillable = [
        'user_id', 'cour_id', 'ue_id', 'specialite_id', 'filiere_id', 'departement_id',
        'annee_academique', 'semestre', 'niveau', 'langue',
        'credits', 'volume_total', 'volume_cm', 'volume_td', 'volume_tp', 'volume_tpe',
        'equipe', 'objectifs', 'competences', 'outils', 'prerequis', 'resume',
        'contenu', 'mode_enseignement', 'mode_evaluation', 'bibliographie',
        'fichier', 'fichier_nom', 'fichier_taille',
        'statut', 'motif', 'soumis_le', 'tranche_par', 'tranche_le', 'notifie_a', 'notifie_le',
    ];

    protected $casts = [
        'equipe' => 'array',
        'soumis_le' => 'datetime',
        'tranche_le' => 'datetime',
        'notifie_le' => 'datetime',
    ];

    // ------------------------------------------------------------- relations

    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function matiere(): BelongsTo
    {
        return $this->belongsTo(Cour::class, 'cour_id');
    }

    public function ue(): BelongsTo
    {
        return $this->belongsTo(Ue::class);
    }

    public function specialite(): BelongsTo
    {
        return $this->belongsTo(Specialite::class);
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class);
    }

    public function trancheur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tranche_par');
    }

    // ---------------------------------------------------------------- portee

    public function scopeDeLAnnee(Builder $query, ?string $annee = null): Builder
    {
        return $query->where('annee_academique', $annee ?: AnneeAcademique::courante());
    }

    /** Les syllabus des departements dont l'utilisateur a la charge. */
    public function scopePourChef(Builder $query, User $chef): Builder
    {
        return $query->whereIn('departement_id', $chef->departementsDirigees());
    }

    // --------------------------------------------------------------- lecture

    public function libelleStatut(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    /** Tant qu'il n'est pas valide, l'enseignant peut encore le reprendre. */
    public function estModifiable(): bool
    {
        return $this->statut !== 'valide';
    }

    public function estSoumis(): bool
    {
        return in_array($this->statut, ['soumis', 'valide', 'a_revoir'], true);
    }

    /** Les sections encore vides parmi celles qu'on exige. */
    public function sectionsManquantes(): array
    {
        return collect(self::SECTIONS_REQUISES)
            ->filter(fn (string $champ) => blank($this->{$champ}))
            ->map(fn (string $champ) => self::SECTIONS[$champ])
            ->values()
            ->all();
    }

    /** Le chef du departement de la matiere : c'est lui qu'on previent. */
    public function chefDeDepartement(): ?User
    {
        return $this->departement_id
            ? Departement::find($this->departement_id)?->responsable
            : null;
    }

    /** Le chemin academique, comme sur les autres ecrans : Dept › Filière › … */
    public function chemin(): string
    {
        return collect([
            $this->departement?->nom,
            $this->filiere?->name,
            $this->specialite?->name,
            $this->ue?->name,
        ])->filter()->implode(' › ');
    }

    /** « CMN231 - ALGORITHMIQUE AVANCEE 2 », comme sur le document. */
    public function intitule(): string
    {
        $matiere = $this->matiere;

        return collect([$matiere?->code, $matiere?->name])->filter()->implode(' - ')
            ?: 'Matière supprimée';
    }

    /**
     * L'equipe pedagogique, normalisee : trois lignes au plus, les vides
     * ecartees.
     *
     * @return array<int, array{nom: string, charge: string}>
     */
    public function equipePedagogique(): array
    {
        return collect($this->equipe ?? [])
            ->map(fn ($ligne) => [
                'nom' => trim((string) ($ligne['nom'] ?? '')),
                'charge' => trim((string) ($ligne['charge'] ?? '')),
            ])
            ->filter(fn (array $ligne) => $ligne['nom'] !== '')
            ->take(3)
            ->values()
            ->all();
    }

    // --------------------------------------------------------------- ecriture

    /**
     * Le syllabus d'une matiere pour un semestre, cree a vide s'il n'existe
     * pas encore — avec ce que la matiere sait deja de lui-meme.
     */
    public static function pourMatiere(Cour $matiere, User $enseignant, int $semestre, ?string $annee = null): self
    {
        $annee ??= AnneeAcademique::courante();

        $existant = self::where('cour_id', $matiere->id)
            ->where('annee_academique', $annee)
            ->where('semestre', $semestre)
            ->first();

        if ($existant) {
            return $existant;
        }

        $chaine = Affectation::chaine('cours', $matiere->id);

        return new self([
            'user_id' => $enseignant->id,
            'cour_id' => $matiere->id,
            'ue_id' => $chaine['ue_id'],
            'specialite_id' => $chaine['specialite_id'],
            'filiere_id' => $chaine['filiere_id'],
            'departement_id' => $chaine['departement_id'],
            'annee_academique' => $annee,
            'semestre' => $semestre,
            'niveau' => $matiere->ue?->cycle?->name ?? $matiere->cycle?->name,
            'langue' => 'Français',
            'credits' => $matiere->credit ?: $matiere->ue?->credits,
            'volume_total' => $matiere->hour_number ?: $matiere->ue?->hour_number,
            'equipe' => [[
                'nom' => trim($enseignant->name.' '.$enseignant->lastname),
                'charge' => 'CM',
            ]],
        ]);
    }
}
