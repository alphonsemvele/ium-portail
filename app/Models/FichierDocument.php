<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Une piece deposee. Le fichier est range hors du dossier public : il ne
 * s'ouvre que par la route de telechargement, apres verification des droits.
 */
class FichierDocument extends Model
{
    protected $table = 'fichiers_document';

    protected $fillable = [
        'depot_document_id', 'chemin', 'nom_original', 'taille', 'mime',
    ];

    protected $casts = [
        'taille' => 'integer',
    ];

    public function depot(): BelongsTo
    {
        return $this->belongsTo(DepotDocument::class, 'depot_document_id');
    }

    /** Poids lisible : « 1,2 Mo ». */
    public function poids(): string
    {
        $octets = $this->taille;

        if ($octets < 1024) {
            return $octets.' o';
        }

        if ($octets < 1024 * 1024) {
            return number_format($octets / 1024, 0, ',', ' ').' Ko';
        }

        return number_format($octets / 1048576, 1, ',', ' ').' Mo';
    }
}
