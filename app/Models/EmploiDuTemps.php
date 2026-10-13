<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * L'emploi du temps d'une specialite pour une semaine.
 *
 * @see database/migrations/2026_10_13_120000_create_emplois_du_temps_tables.php
 */
class EmploiDuTemps extends Model
{
    protected $table = 'emplois_du_temps';

    protected $fillable = [
        'specialite_id', 'niveau', 'semestre', 'annee_academique',
        'date_debut', 'date_fin', 'creneaux', 'statut', 'publie_le', 'cree_par',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'creneaux' => 'array',
        'publie_le' => 'datetime',
        'semestre' => 'integer',
    ];

    public function specialite(): BelongsTo
    {
        return $this->belongsTo(Specialite::class);
    }

    public function seances(): HasMany
    {
        return $this->hasMany(Seance::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function scopePublie(Builder $query): Builder
    {
        return $query->where('statut', 'publie');
    }

    public function estPublie(): bool
    {
        return $this->statut === 'publie';
    }

    /** « Du 13/10 au 17/10/26 », comme sur le document officiel. */
    public function semaine(): string
    {
        return 'Du '.$this->date_debut->format('d/m').' au '.$this->date_fin->format('d/m/y');
    }

    /**
     * Les creneaux, remis d'aplomb : toujours une liste indexee depuis 0,
     * avec les trois cles attendues.
     *
     * @return array<int, array{debut: string, fin: string, pause: bool}>
     */
    public function grilleCreneaux(): array
    {
        return collect($this->creneaux ?: config('emploi_du_temps.creneaux'))
            ->map(fn ($c) => [
                'debut' => substr((string) ($c['debut'] ?? '00:00'), 0, 5),
                'fin' => substr((string) ($c['fin'] ?? '00:00'), 0, 5),
                'pause' => (bool) ($c['pause'] ?? false),
            ])
            ->values()
            ->all();
    }

    /**
     * Les seances rangees par case : $cases[jour][creneau].
     *
     * @return array<int, array<int, Seance>>
     */
    public function cases(): array
    {
        $cases = [];

        foreach ($this->seances as $seance) {
            $cases[$seance->jour][$seance->creneau] = $seance;
        }

        return $cases;
    }

    /** L'intitule complet, pour les listes et le PDF. */
    public function titre(): string
    {
        return trim(($this->specialite?->name ?? 'Spécialité supprimée').' · '.$this->niveau);
    }
}
