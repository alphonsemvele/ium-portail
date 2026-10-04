<?php

namespace App\Http\Controllers;

use App\Services\ReleveService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ReleveController extends Controller
{
    /**
     * Télécharge le relevé de notes de l'étudiant connecté (par filière / session).
     */
    public function telecharger()
    {
        $user = Auth::user()->load(['filiere', 'specialite', 'cycle']);

        [$sessions, $globalStats] = ReleveService::build($user);

        $filiere = $user->filiere?->name ?? $user->specialite?->name;

        $pdf = Pdf::loadView('pdf.releve-etudiant', compact('user', 'sessions', 'globalStats', 'filiere'))
            ->setPaper('a4', 'portrait');

        $filename = 'Releve_' . str_replace(' ', '_', strtoupper($user->name ?? 'etudiant')) . '.pdf';

        return $pdf->download($filename);
    }
}
