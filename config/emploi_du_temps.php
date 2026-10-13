<?php

/*
|--------------------------------------------------------------------------
| Emploi du temps
|--------------------------------------------------------------------------
|
| Les creneaux de la journee, tels qu'ils figurent sur les emplois du temps
| officiels de l'institut. Ils servent de proposition a la creation : chaque
| emploi du temps garde ensuite les siens, et peut s'en ecarter.
|
| Une ligne marquee `pause` barre la journee entiere : c'est la pause de midi,
| elle ne recoit pas de seance.
|
*/

return [

    'creneaux' => [
        ['debut' => '08:00', 'fin' => '10:00', 'pause' => false],
        ['debut' => '10:00', 'fin' => '12:00', 'pause' => false],
        ['debut' => '12:00', 'fin' => '13:00', 'pause' => true],
        ['debut' => '13:00', 'fin' => '15:00', 'pause' => false],
        ['debut' => '15:00', 'fin' => '17:00', 'pause' => false],
    ],

    /*
     * Jours ouvres, du lundi au samedi comme sur le modele. Le dimanche n'y
     * figure pas : aucun cours n'y est place.
     */
    'jours' => [
        1 => ['court' => 'Lun', 'long' => 'Lundi', 'anglais' => 'Monday'],
        2 => ['court' => 'Mar', 'long' => 'Mardi', 'anglais' => 'Tuesday'],
        3 => ['court' => 'Mer', 'long' => 'Mercredi', 'anglais' => 'Wednesday'],
        4 => ['court' => 'Jeu', 'long' => 'Jeudi', 'anglais' => 'Thursday'],
        5 => ['court' => 'Ven', 'long' => 'Vendredi', 'anglais' => 'Friday'],
        6 => ['court' => 'Sam', 'long' => 'Samedi', 'anglais' => 'Saturday'],
    ],

    /*
     * Ce qu'une case peut contenir. « Cours » porte une matiere et un
     * enseignant ; les autres n'en ont pas besoin — le modele officiel ecrit
     * simplement « TPE » ou « Activite scolaire ».
     */
    'types' => [
        'cours' => 'Cours',
        'tpe' => 'TPE',
        'activite' => 'Activité scolaire',
        'autre' => 'Autre',
    ],

];
