<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Examen extends Model
{
    protected $table = 'examens';

    protected $fillable = [
       
               // 'cc', 'examen', 'rattrapage', 'devoir', etc.
        'titre',
        'libelle',
        'date',
      // ex: 0.4 pour CC, 0.6 pour examen final
           // 20 par défaut
        'statut',         // 'ouvert', 'ferme', 'en_cours', 'annule'
        'description',
        'cycle_id'
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    // Relations
    public function cours(): BelongsTo
    {
        return $this->belongsTo(Cour::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    // Helper : nom complet du type
    
    public function ue()
{
    return $this->hasMany(Ue::class, 'examen_id');
}

    /**
     * Ce que les documents impriment : le libelle saisi, ou a defaut celui
     * que le titre laisse deviner.
     */
    public function libelleAffiche(): string
    {
        return filled($this->libelle) ? $this->libelle : self::libelleDepuisTitre($this->titre);
    }

    /**
     * « BTS-S1 » se lit « BTS — Semestre 1 », « M1-S8 » « Master 1 — Semestre 8 ».
     * Un titre qui ne suit pas cette forme est rendu tel quel.
     */
    public static function libelleDepuisTitre(?string $titre): string
    {
        $titre = trim((string) $titre);

        if ($titre === '') {
            return 'Session non définie';
        }

        // « M1-S8 » porte son annee : Master 1. « BTS-S1 » n'en porte pas :
        // on garde le sigle tel quel, « BTS », et non « BTS 1 ».
        $porteSonAnnee = preg_match('/^\s*[A-Za-z]+\d/', $titre) === 1;

        $niveau = $porteSonAnnee
            ? \App\Services\ReleveService::niveau($titre)
            : (preg_match('/^\s*([A-Za-z]+)/', $titre, $trouve) === 1 ? mb_strtoupper($trouve[1]) : null);

        $semestre = \App\Services\ReleveService::semestre($titre);

        // Sans semestre lisible dans le titre, il n'y a rien a reformuler.
        if (! $semestre) {
            return $titre;
        }

        return collect([
            $niveau,
            $semestre ? 'Semestre '.ltrim($semestre, 'Ss') : null,
        ])->filter()->implode(' — ');
    }
 public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }
  
}