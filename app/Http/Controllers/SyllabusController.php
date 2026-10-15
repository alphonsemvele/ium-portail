<?php

namespace App\Http\Controllers;

use App\Models\Syllabus;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sortie papier d'un syllabus, au format du document officiel, et
 * telechargement du fichier que l'enseignant a joint.
 *
 * Les routes sont declarees a la main dans routes/web.php, avec leurs
 * middlewares : on empeche la decouverte automatique d'en ajouter une
 * seconde, ouverte celle-la.
 */
#[DoNotDiscover]
class SyllabusController extends Controller
{
    public function pdf(Syllabus $syllabus): Response
    {
        $this->verifierAcces($syllabus);

        $syllabus->load(['matiere', 'ue', 'specialite', 'filiere', 'departement', 'enseignant']);

        $logo = public_path(config('etablissement.logo', 'images/logo.png'));

        $pdf = Pdf::loadView('pdf.syllabus', [
            'syllabus' => $syllabus,
            'logo' => is_file($logo) ? $logo : null,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Syllabus_'.Str::slug($syllabus->intitule()).'.pdf');
    }

    public function fichier(Syllabus $syllabus): Response
    {
        $this->verifierAcces($syllabus);

        abort_if(blank($syllabus->fichier), 404, 'Aucun fichier joint.');
        abort_unless(Storage::disk('local')->exists($syllabus->fichier), 404, 'Fichier introuvable.');

        return Storage::disk('local')->download(
            $syllabus->fichier,
            $syllabus->fichier_nom ?: 'syllabus.pdf',
        );
    }

    /**
     * Qui peut voir un syllabus : son enseignant, le chef du departement dont
     * releve la matiere, et l'administration.
     */
    private function verifierAcces(Syllabus $syllabus): void
    {
        /** @var User|null $utilisateur */
        $utilisateur = auth()->user();

        abort_unless(
            $utilisateur !== null && (
                $syllabus->user_id === $utilisateur->id
                || $utilisateur->aLeRole('admin')
                || in_array($syllabus->departement_id, $utilisateur->departementsDirigees(), true)
            ),
            403,
            'Ce syllabus ne vous concerne pas.',
        );
    }
}
