<?php

/**
 * config/formations.php
 * Données des filières utilisées par la route /formation/{slug}
 * et la vue resources/views/formation-detail.blade.php
 */

return [

    // ── ISGMM ─────────────────────────────────────────────────────────────
    'gestion' => [
        'nom'   => 'Gestion',
        'image' => 'asset_vitrine/assets/img/update1/normal/gestion.jpg',
        'short' => "Former les gestionnaires de demain — Bâtir la gouvernance d'aujourd'hui.",
        'description' => "La filière Gestion de l'IUM forme des professionnels polyvalents capables de gérer les ressources humaines, financières et logistiques des entreprises publiques et privées. Les programmes couvrent la comptabilité (CGE), le marketing (MCV), la banque et finance (BMF), la logistique (GLT) et le journalisme, du BTS au Master.",
    ],

    'commerce-et-vente' => [
        'nom'   => 'Commerce et Vente',
        'image' => 'asset_vitrine/assets/img/update1/normal/vente.jpg',
        'short' => 'Marketing, commerce international et techniques de vente.',
        'description' => "La filière Commerce et Vente forme des professionnels maîtrisant les techniques commerciales modernes : Marketing Commerce Vente (MCV), Commerce International, E-Commerce et Marketing Numérique.",
    ],

    'tourisme-et-hotellerie' => [
        'nom'   => 'Tourisme, Hôtellerie & Restauration',
        'image' => 'asset_vitrine/assets/img/update1/normal/hotellerie.jpg',
        'short' => 'Former les ambassadeurs du voyage, du bien-être et de la gastronomie.',
        'description' => "La filière THR forme des professionnels de l'accueil, de la gestion hôtelière, des arts culinaires (Génie Culinaire) et de l'industrie de l'habillement (Haute Couture & Design de Mode).",
    ],

    'carrieres-juridiques' => [
        'nom'   => 'Carrières Juridiques',
        'image' => 'asset_vitrine/assets/img/update1/normal/social.jpg',
        'short' => 'Former les juristes de demain — Bâtir le droit d\'aujourd\'hui.',
        'description' => "La filière Droit forme des juristes compétents en droit des affaires, droit public, droit privé et sciences politiques, et propose une Capacité en Droit accessible dès le BEPC pour adultes en reconversion.",
    ],

    // ── ISTIM ─────────────────────────────────────────────────────────────
    'genie-informatique' => [
        'nom'   => 'Génie Informatique',
        'image' => 'asset_vitrine/assets/img/update1/normal/informatique.jpg',
        'short' => 'Former les ingénieurs et techniciens du numérique de demain.',
        'description' => "La filière Génie Informatique forme des techniciens et ingénieurs maîtrisant le génie logiciel, la maintenance des systèmes (MSI), l'informatique industrielle, l'infographie et le e-commerce, du BTS à la Licence ISIR et au Master.",
    ],

    'genie-electrique' => [
        'nom'   => 'Génie Électrique',
        'image' => 'asset_vitrine/assets/img/update1/normal/electrique.jpg',
        'short' => 'Électrotechnique, énergies renouvelables et systèmes électroniques.',
        'description' => "La filière Génie Électrique forme des techniciens en électrotechnique, maintenance des systèmes électroniques, énergies renouvelables et maintenance d'appareils biomédicaux.",
    ],

    'genie-mecanique-et-productique' => [
        'nom'   => 'Génie Mécanique et Productique',
        'image' => 'asset_vitrine/assets/img/update1/normal/productique.jpg',
        'short' => 'Construction métallique, chaudronnerie, soudure et maintenance industrielle.',
        'description' => "La filière Génie Mécanique et Productique forme des techniciens en construction métallique, chaudronnerie et soudure mécanique, productique et automatisme, et technologies marines marchandes.",
    ],

    'genie-civil' => [
        'nom'   => 'Génie Civil',
        'image' => 'asset_vitrine/assets/img/update1/normal/civil.jpg',
        'short' => 'BTP, topographie, architecture et urbanisme.',
        'description' => "La filière Génie Civil forme des professionnels du Bâtiment et des Travaux Publics, de la topographie et de l'architecture & urbanisme, du BTS au Master.",
    ],

    'agriculture-et-elevage' => [
        'nom'   => 'Agriculture et Élevage',
        'image' => 'asset_vitrine/assets/img/update1/normal/elevage.jpg',
        'short' => 'Productions végétales, animales, agro-industrie et ressources naturelles.',
        'description' => "La filière Agriculture et Élevage forme des techniciens agronomes en productions végétales, productions animales, agro-industrie et gestion des ressources naturelles.",
    ],

    'reseaux-et-telecommunications' => [
        'nom'   => 'Réseaux et Télécommunications',
        'image' => 'asset_vitrine/assets/img/update1/normal/admission_1_2.jpg',
        'short' => 'Sécurité des réseaux, télécommunications et réseaux mobiles.',
        'description' => "La filière Réseaux & Télécommunications forme des experts en administration système et réseaux, ingénierie télécoms et réseaux mobiles. Les diplômés intègrent les opérateurs télécoms (Orange, MTN, Camtel).",
    ],

    // ── ISSBM ─────────────────────────────────────────────────────────────
    'sciences-infirmieres' => [
        'nom'   => 'Sciences Infirmières',
        'image' => 'asset_vitrine/assets/img/update1/normal/infirmieres.jpg',
        'short' => 'Soins de santé, assistance médicale et santé communautaire.',
        'description' => "La filière Sciences Infirmières forme des infirmiers compétents pour assurer des soins de qualité en milieu hospitalier et communautaire, avec stages cliniques intensifs.",
    ],

    'sage-femme-maieuticien' => [
        'nom'   => 'Sage-Femme / Maïeuticien',
        'image' => 'asset_vitrine/assets/img/update1/normal/maieuticien.jpg',
        'short' => 'Soins prénatals, accouchement et santé maternelle et néonatale.',
        'description' => "La filière Sage-Femme & Maïeutique forme des professionnels de la santé maternelle et néonatale, du suivi de grossesse à l'accouchement et aux soins post-nataux.",
    ],

    'kinesitherapie' => [
        'nom'   => 'Kinésithérapie',
        'image' => 'asset_vitrine/assets/img/update1/normal/kinesitherapie.jpg',
        'short' => 'Rééducation physique, massage thérapeutique et réhabilitation motrice.',
        'description' => "La filière Kinésithérapie & Rééducation forme des kinésithérapeutes capables d'évaluer, traiter et prévenir les troubles fonctionnels du mouvement.",
    ],

    'radiologie-et-imagerie-medicale' => [
        'nom'   => 'Radiologie et Imagerie Médicale',
        'image' => 'asset_vitrine/assets/img/update1/normal/medicale.jpg',
        'short' => 'Radiographie, scanner, IRM et échographie médicale.',
        'description' => "La filière Radiologie & Imagerie Médicale forme des techniciens spécialisés dans les techniques avancées d'imagerie médicale : radiographie, scanner, IRM, échographie.",
    ],

    'sciences-et-techniques-biomedicales' => [
        'nom'   => 'Sciences et Techniques Biomédicales',
        'image' => 'asset_vitrine/assets/img/update1/normal/biomedicales.jpg',
        'short' => 'Analyses biologiques, pharmacie, génie biomédical et équipements médicaux.',
        'description' => "La filière Sciences et Techniques Biomédicales forme des professionnels en analyses biomédicales et biologiques, pharmacie et génie biomédical, ainsi qu'en maintenance des équipements médicaux.",
    ],

    'etude-medico-sanitaire' => [
        'nom'   => 'Étude Médico-Sanitaire',
        'image' => 'asset_vitrine/assets/img/update1/normal/sanitaire.jpg',
        'short' => 'Santé communautaire, nutrition, diététique et odontostomatologie.',
        'description' => "La filière Étude Médico-Sanitaire forme des professionnels de la santé publique et communautaire : nutrition et diététique, odontostomatologie et épidémiologie de terrain.",
    ],

];