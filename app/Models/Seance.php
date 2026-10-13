<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Une case de l'emploi du temps : un jour, un creneau, et ce qui s'y passe.
 */
class Seance extends Model
{
    protected $fillable = [
        'emploi_du_temps_id', 'jour', 'creneau', 'heure_debut', 'heure_fin',
        'type', 'cour_id', 'intitule', 'enseignant_id', 'salle_id',
        'salle_libelle', 'seance_numero', 'seance_total',
    ];

    protected $casts = [
        'jour' => 'integer',
        'creneau' => 'integer',
        'seance_numero' => 'integer',
        'seance_total' => 'integer',
    ];

    public function emploiDuTemps(): BelongsTo
    {
        return $this->belongsTo(EmploiDuTemps::class);
    }

    public function cour(): BelongsTo
    {
        return $this->belongsTo(Cour::class);
    }

    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enseignant_id');
    }

    public function salle(): BelongsTo
    {
        return $this->belongsTo(Salle::class);
    }

    /** Ce qui est ecrit en haut de la case : la matiere, ou le type. */
    public function matiere(): string
    {
        return $this->cour?->name
            ?: ($this->intitule ?: config("emploi_du_temps.types.{$this->type}", 'Séance'));
    }

    /** L'enseignant, tel qu'il figure sur le document : « Dr ESSOMBA ». */
    public function nomEnseignant(): ?string
    {
        if (! $this->enseignant) {
            return null;
        }

        return trim($this->enseignant->name.' '.$this->enseignant->lastname) ?: null;
    }

    public function salleAffichee(): ?string
    {
        return $this->salle?->name ?: ($this->salle_libelle ?: null);
    }

    /** « 1/5 » : la seance dans son volume horaire, si on le suit. */
    public function progression(): ?string
    {
        return $this->seance_numero && $this->seance_total
            ? $this->seance_numero.'/'.$this->seance_total
            : null;
    }

    public function horaire(): string
    {
        return substr((string) $this->heure_debut, 0, 5).' - '.substr((string) $this->heure_fin, 0, 5);
    }

    public function jourLong(): string
    {
        return config("emploi_du_temps.jours.{$this->jour}.long", '—');
    }
}
