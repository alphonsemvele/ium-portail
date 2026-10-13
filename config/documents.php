<?php

/*
|--------------------------------------------------------------------------
| Documents demandes au personnel
|--------------------------------------------------------------------------
*/

return [

    // Extensions acceptees par defaut, quand la demande n'en impose pas.
    'formats' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png'],

    // Taille maximale d'une piece, en kilo-octets.
    'taille_max' => 10240,

    // Dossier de rangement, hors du dossier public : ces pieces sont internes.
    'dossier' => 'documents-demandes',

];
