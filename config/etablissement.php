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

    /*
     * Le ministere de tutelle, tel qu'il s'imprime en tete des documents
     * officiels. L'IUM releve du MINESUP ; l'IFPM et le GSBM ont le leur.
     */
    'ministere_fr' => env('ETABLISSEMENT_MINISTERE_FR', "MINISTÈRE DE L'ENSEIGNEMENT SUPÉRIEUR"),
    'ministere_sigle' => env('ETABLISSEMENT_MINISTERE_SIGLE', 'MINESUP'),
    'ministere_en' => env('ETABLISSEMENT_MINISTERE_EN', 'MINISTRY OF HIGHER EDUCATION'),

    /*
     * En-tete bilingue des documents officiels (syllabus, attestations) :
     * la Republique a gauche en francais, a droite en anglais, et entre les
     * deux l'agrement de l'institut. Chaque etablissement du groupe a le
     * sien : il passe par l'environnement.
     */
    'entete' => [
        'pays_fr' => env('ETABLISSEMENT_PAYS_FR', 'RÉPUBLIQUE DU CAMEROUN'),
        'devise_pays_fr' => env('ETABLISSEMENT_DEVISE_PAYS_FR', 'Paix – Travail – Patrie'),
        'nom_fr' => env('ETABLISSEMENT_NOM_FR', 'Institut Universitaire La Majestueuse Ndazoa'),
        'agrement_fr' => env('ETABLISSEMENT_AGREMENT_FR',
            'Lettre N° 26-02352/L/MINESUP/DDES/ESUP/SDA/ANAP du 16 mars 2026'),
        'devise_fr' => env('ETABLISSEMENT_DEVISE_FR', 'Devise : Développer les talents, réaliser les rêves'),

        'pays_en' => env('ETABLISSEMENT_PAYS_EN', 'REPUBLIC OF CAMEROON'),
        'devise_pays_en' => env('ETABLISSEMENT_DEVISE_PAYS_EN', 'Peace – Work – Fatherland'),
        'nom_en' => env('ETABLISSEMENT_NOM_EN', 'La Majestueuse Ndazoa University Institute'),
        'agrement_en' => env('ETABLISSEMENT_AGREMENT_EN',
            'Letter N° 26-02352/L/MINESUP/DDES/ESUP/SDA/ANAP of 16th March 2026'),
        'devise_en' => env('ETABLISSEMENT_DEVISE_EN', 'Motto: Nurturing talents, realising dreams'),
    ],

    'modules' => [],
];
