<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Ce qu'un agent a rendu pour une demande : ses fichiers, et la decision de
 * l'administration.
 */
class DepotDocument extends Model
{
    protected $table = 'depots_document';

    protected $fillable = [
        'demande_document_id', 'user_id', 'statut', 'motif',
        'depose_le', 'tranche_par', 'tranche_le', 'relance_le',
    ];

    protected $casts = [
        'depose_le' => 'datetime',
        'tranche_le' => 'datetime',
        'relance_le' => 'datetime',
    ];

    public function demande(): BelongsTo
    {
        return $this->belongsTo(DemandeDocument::class, 'demande_document_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fichiers(): HasMany
    {
        return $this->hasMany(FichierDocument::class);
    }

    public function arbitre(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tranche_par');
    }

    public function estAccepte(): bool
    {
        return $this->statut === 'accepte';
    }

    public function estRefuse(): bool
    {
        return $this->statut === 'refuse';
    }

    /** Libelle de l'etat, pour l'affichage. */
    public function libelleStatut(): string
    {
        return match ($this->statut) {
            'accepte' => 'Accepté',
            'refuse' => 'Refusé',
            default => 'En attente de validation',
        };
    }
}
