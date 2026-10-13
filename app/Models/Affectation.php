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
        'annee_academique', 'fonction',
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

    public function scopeDeLAnnee(Builder $query, ?string $annee = null): Builder
    {
        return $query->where('annee_academique', $annee ?: AnneeAcademique::courante());
    }

    /**
     * Pose une charge au niveau choisi et remplit les parents par deduction :
     * affecter quelqu'un a une specialite, c'est l'affecter a sa filiere et a
     * son departement.
     *
     * @return array{niveau: string, departement_id: ?int, filiere_id: ?int, specialite_id: ?int}
     */
    public static function chaine(string $niveau, int $cible): array
    {
        return match ($niveau) {
            'specialite' => (function () use ($cible) {
                $specialite = Specialite::with('filiere')->find($cible);

                return [
                    'niveau' => 'specialite',
                    'specialite_id' => $cible,
                    'filiere_id' => $specialite?->filiere_id,
                    'departement_id' => $specialite?->filiere?->departement_id,
                ];
            })(),
            'filiere' => [
                'niveau' => 'filiere',
                'specialite_id' => null,
                'filiere_id' => $cible,
                'departement_id' => Filiere::find($cible)?->departement_id,
            ],
            default => [
                'niveau' => 'departement',
                'specialite_id' => null,
                'filiere_id' => null,
                'departement_id' => $cible,
            ],
        };
    }

    /** Ce qui est confie, en une ligne lisible. */
    public function intitule(): string
    {
        return match ($this->niveau) {
            'specialite' => $this->specialite?->name ?? 'Spécialité supprimée',
            'filiere' => $this->filiere?->name ?? 'Filière supprimée',
            default => $this->departement?->nom ?? 'Département supprimé',
        };
    }

    /** Le chemin complet : Département › Filière › Spécialité. */
    public function chemin(): string
    {
        return collect([
            $this->departement?->nom,
            $this->filiere?->name,
            $this->specialite?->name,
        ])->filter()->implode(' › ');
    }
}
