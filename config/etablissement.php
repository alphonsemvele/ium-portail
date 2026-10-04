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
    'modules' => [],
];
