<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;

class vitrineController extends Controller
{
    public function index()
    {
        // Récupérer uniquement les articles validés avec leurs images
        $articles = Article::with(['images']) // Charger la première image pour l'aperçu
            ->where('status', 'Success')
            ->latest('published_at') // Trier par date de publication
            ->take(6) // Limiter à 6 articles pour la section
            ->get();

        return view('ism', compact('articles'));
    }

    public function show($id)
    {
        // Récupérer l'article avec toutes ses images et documents
        $article = Article::with(['images', 'documents'])
            ->where('status', 'Success')
            ->findOrFail($id);

        return view('show', compact('article'));
    }

    public function formation($slug)
    {
        $formations = [
            // ── ISGMM ─────────────────────────────────────────────────────
            'gestion' => [
                'nom'   => 'Gestion',
                'image' => 'asset_vitrine/assets/img/update1/normal/gestion.jpg',
                'short' => 'Former les gestionnaires de demain — Bâtir la gouvernance d\'aujourd\'hui.',
                'description' => 'La filière Gestion de l\'IUM forme des professionnels polyvalents capables de gérer les ressources humaines, financières et logistiques des entreprises publiques et privées. Les programmes couvrent la comptabilité (CGE), le marketing (MCV), la banque et finance (BMF), la logistique (GLT) et le journalisme, du BTS au Master. Coordination : KEDE MELONO EUGENE BERTRAND — Tél. 695 494 299.',
            ],
            'commerce-et-vente' => [
                'nom'   => 'Commerce et Vente',
                'image' => 'asset_vitrine/assets/img/update1/normal/vente.jpg',
                'short' => 'Marketing, commerce international et techniques de vente.',
                'description' => 'La filière Commerce et Vente forme des professionnels maîtrisant les techniques commerciales modernes : Marketing Commerce Vente (MCV), Commerce International, E-Commerce et Marketing Numérique. Coordination : KEDE MELONO EUGENE BERTRAND — Tél. 695 494 299.',
            ],
            'tourisme-et-hotellerie' => [
                'nom'   => 'Tourisme, Hôtellerie & Restauration',
                'image' => 'asset_vitrine/assets/img/update1/normal/hotellerie.jpg',
                'short' => 'Former les ambassadeurs du voyage, du bien-être et de la gastronomie.',
                'description' => 'La filière THR forme des professionnels de l\'accueil, de la gestion hôtelière, des arts culinaires (Génie Culinaire) et de l\'industrie de l\'habillement (Haute Couture & Design de Mode). Coordination : Mme NOA Joséphine Didier Natacha, Guide de Tourisme National, DIPET 2ᵉ grade — Tél. 658 877 250.',
            ],
            'carrieres-juridiques' => [
                'nom'   => 'Carrières Juridiques',
                'image' => 'asset_vitrine/assets/img/update1/normal/social.jpg',
                'short' => 'Former les juristes de demain — Bâtir le droit d\'aujourd\'hui.',
                'description' => 'La filière Droit forme des juristes compétents en droit des affaires, droit public, droit privé et sciences politiques, et propose une Capacité en Droit accessible dès le BEPC pour adultes en reconversion. Coordination : Dr Arlette BUGUE MAYOUGOUNG, Maître Assistant CAMES — Tél. 696 144 712.',
            ],

            // ── ISTIM ─────────────────────────────────────────────────────
            'genie-informatique' => [
                'nom'   => 'Génie Informatique',
                'image' => 'asset_vitrine/assets/img/update1/normal/informatique.jpg',
                'short' => 'Former les ingénieurs et techniciens du numérique de demain.',
                'description' => 'La filière Génie Informatique forme des techniciens et ingénieurs maîtrisant le génie logiciel, la maintenance des systèmes (MSI), l\'informatique industrielle, l\'infographie et le e-commerce, du BTS à la Licence ISIR et au Master. Tél. : +237 6 55 34 19 39.',
            ],
            'genie-electrique' => [
                'nom'   => 'Génie Électrique',
                'image' => 'asset_vitrine/assets/img/update1/normal/electrique.jpg',
                'short' => 'Électrotechnique, énergies renouvelables et systèmes électroniques.',
                'description' => 'La filière Génie Électrique forme des techniciens en électrotechnique, maintenance des systèmes électroniques, énergies renouvelables et maintenance d\'appareils biomédicaux. Tél. : +237 6 55 34 19 39.',
            ],
            'genie-mecanique-et-productique' => [
                'nom'   => 'Génie Mécanique et Productique',
                'image' => 'asset_vitrine/assets/img/update1/normal/productique.jpg',
                'short' => 'Construction métallique, chaudronnerie, soudure et maintenance industrielle.',
                'description' => 'La filière Génie Mécanique et Productique forme des techniciens en construction métallique, chaudronnerie et soudure mécanique, productique et automatisme, et technologies marines marchandes. Tél. : +237 6 55 34 19 39.',
            ],
            'genie-civil' => [
                'nom'   => 'Génie Civil',
                'image' => 'asset_vitrine/assets/img/update1/normal/civil.jpg',
                'short' => 'BTP, topographie, architecture et urbanisme.',
                'description' => 'La filière Génie Civil forme des professionnels du Bâtiment et des Travaux Publics, de la topographie et de l\'architecture & urbanisme, du BTS au Master. Tél. : +237 6 55 34 19 39.',
            ],
            'agriculture-et-elevage' => [
                'nom'   => 'Agriculture et Élevage',
                'image' => 'asset_vitrine/assets/img/update1/normal/elevage.jpg',
                'short' => 'Productions végétales, animales, agro-industrie et ressources naturelles.',
                'description' => 'La filière Agriculture et Élevage forme des techniciens agronomes en productions végétales, productions animales, agro-industrie et gestion des ressources naturelles. Tél. : +237 6 55 34 19 39.',
            ],
            'reseaux-et-telecommunications' => [
                'nom'   => 'Réseaux et Télécommunications',
                'image' => 'asset_vitrine/assets/img/update1/normal/admission_1_2.jpg',
                'short' => 'Sécurité des réseaux, télécommunications et réseaux mobiles.',
                'description' => 'La filière Réseaux & Télécommunications forme des experts en administration système et réseaux, ingénierie télécoms et réseaux mobiles. Les diplômés intègrent les opérateurs télécoms (Orange, MTN, Camtel). Tél. : +237 6 55 34 19 39.',
            ],

            // ── ISSBM ─────────────────────────────────────────────────────
            'sciences-infirmieres' => [
                'nom'   => 'Sciences Infirmières',
                'image' => 'asset_vitrine/assets/img/update1/normal/infirmieres.jpg',
                'short' => 'Soins de santé, assistance médicale et santé communautaire.',
                'description' => 'La filière Sciences Infirmières forme des infirmiers compétents pour assurer des soins de qualité en milieu hospitalier et communautaire, avec stages cliniques intensifs. Tél. : +237 6 55 34 19 39.',
            ],
            'sage-femme-maieuticien' => [
                'nom'   => 'Sage-Femme / Maïeuticien',
                'image' => 'asset_vitrine/assets/img/update1/normal/maieuticien.jpg',
                'short' => 'Soins prénatals, accouchement et santé maternelle et néonatale.',
                'description' => 'La filière Sage-Femme & Maïeutique forme des professionnels de la santé maternelle et néonatale, du suivi de grossesse à l\'accouchement et aux soins post-nataux. Tél. : +237 6 55 34 19 39.',
            ],
            'kinesitherapie' => [
                'nom'   => 'Kinésithérapie',
                'image' => 'asset_vitrine/assets/img/update1/normal/kinesitherapie.jpg',
                'short' => 'Rééducation physique, massage thérapeutique et réhabilitation motrice.',
                'description' => 'La filière Kinésithérapie & Rééducation forme des kinésithérapeutes capables d\'évaluer, traiter et prévenir les troubles fonctionnels du mouvement. Tél. : +237 6 55 34 19 39.',
            ],
            'radiologie-et-imagerie-medicale' => [
                'nom'   => 'Radiologie et Imagerie Médicale',
                'image' => 'asset_vitrine/assets/img/update1/normal/medicale.jpg',
                'short' => 'Radiographie, scanner, IRM et échographie médicale.',
                'description' => 'La filière Radiologie & Imagerie Médicale forme des techniciens spécialisés dans les techniques avancées d\'imagerie médicale : radiographie, scanner, IRM, échographie. Tél. : +237 6 55 34 19 39.',
            ],
            'sciences-et-techniques-biomedicales' => [
                'nom'   => 'Sciences et Techniques Biomédicales',
                'image' => 'asset_vitrine/assets/img/update1/normal/biomedicales.jpg',
                'short' => 'Analyses biologiques, pharmacie, génie biomédical et équipements médicaux.',
                'description' => 'La filière Sciences et Techniques Biomédicales forme des professionnels en analyses biomédicales et biologiques, pharmacie et génie biomédical, ainsi qu\'en maintenance des équipements médicaux. Tél. : +237 6 55 34 19 39.',
            ],
            'etude-medico-sanitaire' => [
                'nom'   => 'Étude Médico-Sanitaire',
                'image' => 'asset_vitrine/assets/img/update1/normal/sanitaire.jpg',
                'short' => 'Santé communautaire, nutrition, diététique et odontostomatologie.',
                'description' => 'La filière Étude Médico-Sanitaire forme des professionnels de la santé publique et communautaire : nutrition et diététique, odontostomatologie et épidémiologie de terrain. Tél. : +237 6 55 34 19 39.',
            ],
        ];

        if (! isset($formations[$slug])) {
            abort(404, 'Formation introuvable');
        }

        return view('formation', ['formation' => $formations[$slug]]);
    }
}