<?php

namespace App\Http\Controllers;

use App\Models\EmploiDuTemps;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sortie papier de l'emploi du temps, au format du document officiel.
 *
 * La route est declaree a la main dans routes/web.php, avec ses middlewares :
 * on empeche la decouverte automatique d'en ajouter une seconde, ouverte
 * celle-la.
 */
#[DoNotDiscover]
class EmploiDuTempsController extends Controller
{
    public function pdf(EmploiDuTemps $emploi): Response
    {
        $utilisateur = auth()->user();

        /*
         * Un brouillon ne sort pas : il changera encore. Seule
         * l'administration peut l'imprimer pour relecture.
         */
        abort_if(! $emploi->estPublie() && ! $utilisateur?->aLeRole('admin'), 404);

        $emploi->load(['specialite.filiere', 'seances.cour', 'seances.enseignant', 'seances.salle']);

        $logo = public_path(config('etablissement.logo', 'images/logo.png'));

        $pdf = Pdf::loadView('pdf.emploi-du-temps', [
            'emploi' => $emploi,
            'logo' => is_file($logo) ? $logo : null,
        ])->setPaper('a4', 'landscape');

        $nom = 'Emploi-du-temps_'
            .Str::slug($emploi->specialite?->name ?? 'specialite').'_'
            .Str::slug($emploi->niveau).'_'
            .$emploi->date_debut->format('Y-m-d').'.pdf';

        return $pdf->stream($nom);
    }
}
