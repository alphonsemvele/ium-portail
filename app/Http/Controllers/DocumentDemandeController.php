<?php

namespace App\Http\Controllers;

use App\Models\DemandeDocument;
use App\Models\DepotDocument;
use App\Models\FichierDocument;
use Illuminate\Support\Facades\Storage;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Telechargement des pieces deposees et suivi exportable.
 *
 * Les fichiers sont ranges hors du dossier public : ils ne sortent que par
 * ici, et seulement pour celui qui les a deposes ou pour l'administration.
 *
 * Les routes sont declarees a la main dans routes/web.php, avec leurs
 * middlewares : on empeche la decouverte automatique d'en ajouter une
 * seconde, ouverte celle-la.
 */
#[DoNotDiscover]
class DocumentDemandeController extends Controller
{
    public function telecharger(FichierDocument $fichier): Response
    {
        $utilisateur = auth()->user();
        $depot = $fichier->depot;

        abort_if(! $depot, 404);
        abort_unless(
            $depot->user_id === $utilisateur?->id || $utilisateur?->aLeRole('admin'),
            403,
            "Ce document ne vous concerne pas."
        );

        abort_unless(Storage::disk('local')->exists($fichier->chemin), 404, 'Fichier introuvable.');

        return Storage::disk('local')->download($fichier->chemin, $fichier->nom_original);
    }

    /**
     * Le tableau de suivi d'une demande, en CSV : qui a rendu, qui n'a pas
     * rendu, et ce qu'on en a decide.
     */
    public function export(DemandeDocument $demande): StreamedResponse
    {
        abort_unless(auth()->user()?->aLeRole('admin'), 403);

        $depots = DepotDocument::with('fichiers')
            ->where('demande_document_id', $demande->id)
            ->get()
            ->keyBy('user_id');

        $concernes = $demande->concernes();

        $nom = 'suivi_'.Str::slug($demande->titre).'_'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($concernes, $depots) {
            $sortie = fopen('php://output', 'w');

            // Les tableurs francophones lisent l'UTF-8 grace a cette marque.
            fwrite($sortie, "\xEF\xBB\xBF");
            fputcsv($sortie, ['Matricule', 'Nom', 'Poste', 'État', 'Déposé le', 'Pièces', 'Motif'], ';');

            foreach ($concernes as $agent) {
                $depot = $depots->get($agent->id);

                fputcsv($sortie, [
                    $agent->matricule ?: '',
                    trim($agent->name.' '.$agent->lastname),
                    $agent->poste ?: '',
                    $depot ? $depot->libelleStatut() : 'Non déposé',
                    $depot?->depose_le?->format('d/m/Y H:i') ?: '',
                    $depot?->fichiers->count() ?: 0,
                    $depot?->motif ?: '',
                ], ';');
            }

            fclose($sortie);
        }, $nom, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
