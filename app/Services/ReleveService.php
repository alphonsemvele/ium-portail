<?php

namespace App\Services;

use App\Models\Note;
use App\Models\User;

class ReleveService
{
    /**
     * Agrège les notes d'un étudiant par UE (compensation : les crédits d'une UE
     * sont acquis en bloc dès lors que sa moyenne pondérée atteint 10/20) et
     * renvoie [$sessions, $globalStats] tel qu'attendu par la vue pdf.releve-etudiant.
     */
    public static function build(User $etudiant, ?int $examenId = null): array
    {
        $notesQuery = Note::where('etudiant_id', $etudiant->id)->with(['cours.ue', 'examen']);
        if ($examenId) {
            $notesQuery->where('examen_id', $examenId);
        }
        $notes = $notesQuery->get();

        $sessions = [];

        foreach ($notes as $note) {
            $cours = $note->cours;
            if (!$cours) continue;

            $examFinal = ($note->rattrapage !== null && $note->rattrapage > ($note->exam ?? 0))
                ? $note->rattrapage : $note->exam;
            $moy = ($note->cc !== null && $examFinal !== null)
                ? round(0.3 * $note->cc + 0.7 * $examFinal, 2)
                : ($note->valeur !== null ? (float) $note->valeur : null);

            $credit = (int) ($cours->credit ?? 0);

            $sk = $note->examen->id ?? 0;
            $st = $note->examen->titre ?? 'Session non définie';
            if (!isset($sessions[$sk])) {
                $sessions[$sk] = ['titre' => $st, 'ues' => []];
            }

            $uk = $cours->ue->id ?? 0;
            $un = $cours->ue->name ?? 'Autres matières';
            $uc = $cours->ue->code ?? '';
            if (!isset($sessions[$sk]['ues'][$uk])) {
                $sessions[$sk]['ues'][$uk] = ['name' => $un, 'code' => $uc, 'lignes' => []];
            }

            $sessions[$sk]['ues'][$uk]['lignes'][] = [
                'code' => $cours->code ?? '',
                'nom' => $cours->name,
                'credit' => $credit,
                'moy' => $moy,
                'session_date' => $note->examen->date ?? null,
            ];
        }

        $totCredits = 0; $totValides = 0; $sumPonderee = 0; $matieres = 0;

        foreach ($sessions as $sk => &$session) {
            $sessionCredits = 0; $sessionValides = 0;

            foreach ($session['ues'] as $uk => &$ue) {
                $ueCredits = 0; $ueSomme = 0; $ueDate = null;

                foreach ($ue['lignes'] as $l) {
                    $ueCredits += $l['credit'];
                    if ($l['moy'] !== null) {
                        $ueSomme += $l['moy'] * $l['credit'];
                        $matieres++;
                    }
                    if ($l['session_date'] && (!$ueDate || $l['session_date'] > $ueDate)) {
                        $ueDate = $l['session_date'];
                    }
                }

                $ueMoy = $ueCredits > 0 ? round($ueSomme / $ueCredits, 2) : null;
                $ueValide = $ueMoy !== null && $ueMoy >= 10;
                $ueCreditsValides = $ueValide ? $ueCredits : 0;

                foreach ($ue['lignes'] as &$l) {
                    $l['credits_valides'] = $ueValide ? $l['credit'] : 0;
                    $l['session'] = $l['session_date'] ? mb_strtoupper($l['session_date']->translatedFormat('F Y')) : '—';
                    unset($l['session_date']);
                }
                unset($l);

                $ue['credits'] = $ueCredits;
                $ue['moy'] = $ueMoy;
                $ue['credits_valides'] = $ueCreditsValides;
                $ue['cote'] = self::cote($ueMoy);
                $ue['session'] = $ueDate ? mb_strtoupper($ueDate->translatedFormat('F Y')) : '—';

                $sessionCredits += $ueCredits;
                $sessionValides += $ueCreditsValides;

                $sumPonderee += $ueMoy !== null ? $ueMoy * $ueCredits : 0;
                $totCredits += $ueCredits;
                $totValides += $ueCreditsValides;
            }
            unset($ue);

            $session['credits'] = $sessionCredits;
            $session['valides'] = $sessionValides;
        }
        unset($session);

        $globalStats = [
            'moy' => $totCredits ? round($sumPonderee / $totCredits, 2) : null,
            'credits' => $totCredits,
            'valides' => $totValides,
            'reste' => max($totCredits - $totValides, 0),
            'matieres' => $matieres,
        ];

        return [$sessions, $globalStats];
    }

    /**
     * Cote lettre sur une moyenne /20 (barème IUM Ndazoa).
     */
    public static function cote(?float $moy): string
    {
        if ($moy === null) return '—';
        return match (true) {
            $moy < 6  => 'F',
            $moy < 8  => 'D',
            $moy < 9  => 'D+',
            $moy < 10 => 'C-',
            $moy < 11 => 'C',
            $moy < 12 => 'C+',
            $moy < 13 => 'B-',
            $moy < 14 => 'B',
            $moy < 15 => 'B+',
            $moy < 16 => 'A-',
            default   => 'A',
        };
    }
}
