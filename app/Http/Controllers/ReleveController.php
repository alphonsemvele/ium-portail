<?php

namespace App\Http\Controllers;

use App\Services\ReleveService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReleveController extends Controller
{
    /**
     * Télécharge le relevé de notes de l'étudiant connecté.
     *
     * Un relevé porte sur un semestre, c'est-à-dire sur une session d'examen :
     * celle demandée, ou la plus récente à défaut.
     */
    public function telecharger(Request $request)
    {
        $user = Auth::user()->load(['filiere', 'specialite', 'cycle']);

        $sessionsDisponibles = ReleveService::sessionsDe($user);
        $demande = (int) $request->query('session');

        $examenId = $sessionsDisponibles->firstWhere('id', $demande)?->id
            ?? $sessionsDisponibles->first()?->id;

        [$sessions, $globalStats] = ReleveService::build($user, $examenId);

        $filiere = $user->filiere?->name ?? $user->specialite?->name;

        $pdf = Pdf::loadView('pdf.releve-etudiant', compact('user', 'sessions', 'globalStats', 'filiere'))
            ->setPaper('a4', 'portrait');

        $semestre = str_replace(' ', '_', (string) ($globalStats['semestre'] ?? ''));

        $filename = collect([
            'Releve',
            str_replace(' ', '_', mb_strtoupper($user->name ?? 'etudiant')),
            $semestre ?: null,
        ])->filter()->implode('_').'.pdf';

        return $pdf->download($filename);
    }
}
