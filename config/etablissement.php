<?php

/*
 * Identite de l'etablissement, lue par le gabarit (menu, titres, exports PDF).
 */
return [
    'sigle' => env('ETABLISSEMENT_SIGLE', 'IUM NDAZOA'),
    'nom' => env('ETABLISSEMENT_NOM', 'Institut Universitaire La Majestueuse'),
    'slogan' => env('ETABLISSEMENT_SLOGAN', "Le chemin le plus court vers l'emploi."),
    'adresse' => env('ETABLISSEMENT_ADRESSE', 'Ndazoa, 7 km de Mbankomo, route Yaoundé-Douala'),
    'telephone' => env('ETABLISSEMENT_TELEPHONE', '+237 691 612 145'),
    'email' => env('ETABLISSEMENT_EMAIL', ''),
    'domaine' => env('ETABLISSEMENT_DOMAINE', 'ium-ndazoa.com'),
    'logo' => env('ETABLISSEMENT_LOGO', 'images/logo.png'),

    /*
     * Annee academique en cours, telle qu'elle est ecrite sur les emplois du
     * temps et les attestations. Vide, elle se deduit de la date : la rentree
     * est en septembre (App\Support\AnneeAcademique).
     */
    'annee_academique' => env('ETABLISSEMENT_ANNEE'),

    'modules' => [],
];
