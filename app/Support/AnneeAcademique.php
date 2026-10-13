<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * L'annee academique, telle qu'elle est ecrite sur les documents officiels de
 * l'institut : « 2026/2027 ».
 *
 * Elle commence en septembre. Le 13 octobre 2026 on est donc en 2026/2027, et
 * le 13 juin 2027 encore. ETABLISSEMENT_ANNEE la force si l'institut decale
 * sa rentree une annee donnee.
 */
class AnneeAcademique
{
    /** Mois de la rentree : a partir de la, on bascule sur l'annee suivante. */
    private const RENTREE = 9;

    public static function courante(): string
    {
        if (filled($forcee = config('etablissement.annee_academique'))) {
            return (string) $forcee;
        }

        $aujourdhui = CarbonImmutable::now();
        $debut = $aujourdhui->month >= self::RENTREE ? $aujourdhui->year : $aujourdhui->year - 1;

        return $debut.'/'.($debut + 1);
    }

    /**
     * Les annees proposees dans les listes deroulantes : la courante, les deux
     * precedentes et la suivante — on prepare la rentree avant qu'elle arrive.
     *
     * @return array<int, string>
     */
    public static function options(): array
    {
        $debut = (int) strtok(self::courante(), '/');

        return collect(range($debut - 2, $debut + 1))
            ->map(fn (int $an) => $an.'/'.($an + 1))
            ->reverse()
            ->values()
            ->all();
    }
}
