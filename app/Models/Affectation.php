<?php

namespace App\Models;

use App\Support\AnneeAcademique;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * La charge confiee a un membre du personnel : un departement, une filiere ou
 * une specialite, pour une annee academique.
 *
 * @see database/migrations/2026_10_13_110000_create_affectations_table.php
 */
class Affectation extends Model
{
    protected $fillable = [
        'user_id', 'niveau', 'departement_id', 'filiere_id', 'specialite_id',
        'ue_id', 'cour_id', 'annee_academique', 'fonction',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class);
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function specialite(): BelongsTo
    {
        return $this->belongsTo(Specialite::class);
    }

    public function ue(): BelongsTo
    {
        return $this->belongsTo(Ue::class);
    }

    public function matiere(): BelongsTo
    {
        return $this->belongsTo(Cour::class, 'cour_id');
    }

    public function scopeDeLAnnee(Builder $query, ?string $annee = null): Builder
    {
        return $query->where('annee_academique', $annee ?: AnneeAcademique::courante());
    }

    /**
     * Pose une charge au niveau choisi et remplit les parents par deduction :
     * affecter quelqu'un a une matiere, c'est l'affecter a son UE, a sa
     * specialite, a sa filiere et a son departement.
     *
     * @return array{niveau: string, departement_id: ?int, filiere_id: ?int, specialite_id: ?int, ue_id: ?int, cour_id: ?int}
     */
    public static function chaine(string $niveau, int $cible): array
    {
        $vide = [
            'departement_id' => null, 'filiere_id' => null,
            'specialite_id' => null, 'ue_id' => null, 'cour_id' => null,
        ];

        return match ($niveau) {
            'cours' => (function () use ($vide, $cible) {
                $matiere = Cour::with('ue.specialite.filiere')->find($cible);
                $specialite = $matiere?->ue?->specialite ?? $matiere?->specialite;

                return array_merge($vide, [
                    'niveau' => 'cours',
                    'cour_id' => $cible,
                    'ue_id' => $matiere?->ue_id,
                    'specialite_id' => $specialite?->id ?? $matiere?->specialite_id,
                    'filiere_id' => $specialite?->filiere_id ?? $matiere?->filiere_id,
                    'departement_id' => $specialite?->filiere?->departement_id,
                ]);
            })(),
            'ue' => (function () use ($vide, $cible) {
                $ue = Ue::with('specialite.filiere')->find($cible);

                return array_merge($vide, [
                    'niveau' => 'ue',
                    'cour_id' => null,
                    'ue_id' => $cible,
                    'specialite_id' => $ue?->specialite_id,
                    'filiere_id' => $ue?->specialite?->filiere_id ?? $ue?->filiere_id,
                    'departement_id' => $ue?->specialite?->filiere?->departement_id,
                ]);
            })(),
            'specialite' => (function () use ($vide, $cible) {
                $specialite = Specialite::with('filiere')->find($cible);

                return array_merge($vide, [
                    'niveau' => 'specialite',
                    'specialite_id' => $cible,
                    'filiere_id' => $specialite?->filiere_id,
                    'departement_id' => $specialite?->filiere?->departement_id,
                ]);
            })(),
            'filiere' => array_merge($vide, [
                'niveau' => 'filiere',
                'filiere_id' => $cible,
                'departement_id' => Filiere::find($cible)?->departement_id,
            ]),
            default => array_merge($vide, [
                'niveau' => 'departement',
                'departement_id' => $cible,
            ]),
        };
    }

    /** Ce qui est confie, en une ligne lisible. */
    public function intitule(): string
    {
        return match ($this->niveau) {
            'cours' => $this->matiere?->name ?? 'Matière supprimée',
            'ue' => $this->ue?->name ?? 'UE supprimée',
            'specialite' => $this->specialite?->name ?? 'Spécialité supprimée',
            'filiere' => $this->filiere?->name ?? 'Filière supprimée',
            default => $this->departement?->nom ?? 'Département supprimé',
        };
    }

    /** Le chemin complet : Département › Filière › Spécialité › UE › Matière. */
    public function chemin(): string
    {
        return collect([
            $this->departement?->nom,
            $this->filiere?->name,
            $this->specialite?->name,
            $this->ue?->name,
            $this->matiere?->name,
        ])->filter()->implode(' › ');
    }
}
