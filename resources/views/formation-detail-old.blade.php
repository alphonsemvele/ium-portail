<!DOCTYPE html>
<html class="no-js" lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ $formation['nom'] }} - Institut Universitaire La Majestueuse</title>
    <meta name="description" content="{{ $formation['short'] }}">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('asset_vitrine/assets/css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset_vitrine/assets/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset_vitrine/assets/css/style.css') }}">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .formation-hero {
            position: relative;
            min-height: 420px;
            display: flex;
            align-items: center;
            color: #fff;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            overflow: hidden;
        }

        .formation-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(26, 115, 232, 0.92), rgba(16, 29, 63, 0.88));
        }

        .formation-hero .container {
            position: relative;
            z-index: 2;
        }

        .formation-hero .breadcrumb-link {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 14px;
        }

        .formation-hero .breadcrumb-link:hover {
            color: #fff;
        }

        .formation-hero h1 {
            font-size: 48px;
            font-weight: 700;
            margin: 18px 0 14px;
            color: #fff;
            line-height: 1.15;
        }

        .formation-hero .hero-sub {
            font-size: 18px;
            max-width: 760px;
            color: rgba(255, 255, 255, 0.92);
        }

        .formation-description {
            padding: 80px 0;
        }

        .formation-description img {
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .formation-description h2 {
            color: #1a73e8;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .formation-description p {
            text-align: justify;
            font-size: 16px;
            color: #555;
        }

        .cycles-section {
            background: #f6f8fb;
            padding: 80px 0;
        }

        .cycles-section .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .cycles-section .section-title span {
            color: #bf1f2b;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 14px;
        }

        .cycles-section .section-title h2 {
            color: #101d3f;
            font-weight: 700;
            margin-top: 8px;
        }

        .cycle-card {
            background: #fff;
            border-radius: 16px;
            padding: 35px 28px;
            height: 100%;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-top: 4px solid #1a73e8;
            display: flex;
            flex-direction: column;
        }

        .cycle-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 38px rgba(26, 115, 232, 0.15);
        }

        .cycle-card.licence { border-top-color: #34c759; }
        .cycle-card.master { border-top-color: #bf1f2b; }

        .cycle-card .cycle-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(26, 115, 232, 0.12);
            color: #1a73e8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 20px;
        }

        .cycle-card.licence .cycle-icon { background: rgba(52, 199, 89, 0.14); color: #34c759; }
        .cycle-card.master .cycle-icon { background: rgba(191, 31, 43, 0.12); color: #bf1f2b; }

        .cycle-card h3 {
            font-size: 24px;
            font-weight: 700;
            color: #101d3f;
            margin-bottom: 6px;
        }

        .cycle-card .cycle-duration {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 18px;
        }

        .cycle-card p {
            color: #555;
            text-align: justify;
            margin-bottom: 22px;
            flex-grow: 1;
        }

        .cycle-card .cycle-list {
            list-style: none;
            padding: 0;
            margin: 0 0 22px;
        }

        .cycle-card .cycle-list li {
            padding: 6px 0;
            color: #444;
            font-size: 14px;
        }

        .cycle-card .cycle-list li i {
            color: #34c759;
            margin-right: 10px;
        }

        .cycle-card .th-btn {
            background: #1a73e8;
            color: #fff;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .cycle-card .th-btn:hover {
            background: #1557b0;
            transform: translateY(-2px);
        }

        .cta-section {
            background: linear-gradient(135deg, #1a73e8, #34c759);
            padding: 70px 0;
            color: #fff;
            text-align: center;
        }

        .cta-section h2 {
            color: #fff;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .cta-section p {
            font-size: 17px;
            max-width: 700px;
            margin: 0 auto 30px;
            color: rgba(255, 255, 255, 0.95);
        }

        .cta-section .th-btn {
            background: #fff;
            color: #1a73e8;
            padding: 14px 34px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .cta-section .th-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #1a73e8;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .back-link:hover {
            color: #1557b0;
        }

        .back-link i {
            margin-right: 8px;
        }

        @media (max-width: 768px) {
            .formation-hero { min-height: 320px; }
            .formation-hero h1 { font-size: 32px; }
            .formation-description, .cycles-section, .cta-section { padding: 50px 0; }
        }
    </style>
</head>

<body>
    {{-- HEADER --}}
    <header class="th-header header-layout14">
        <div class="header-layout10">
            <div class="header-top">
                <div class="container">
                    <div class="row justify-content-center justify-content-lg-between align-items-center gy-2">
                        <div class="col-auto d-none d-lg-block">
                            <div class="header-links">
                                <ul>
                                    <li><i class="fas fa-envelope"></i><b>Contactez-nous : </b>info@ecoledemetiersndazoa.com</li>
                                    <li><i class="fas fa-phone"></i><b>Téléphone : </b>+237 691612145 | 695830031</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="header-links">
                                <ul>
                                    <li><i class="fas fa-user"></i><a href="/login">Connexion / Inscription</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="sticky-wrapper">
            <div class="sticky-active">
                <div class="menu-area">
                    <div class="container">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto">
                                <div class="header-logo">
                                    <a href="/"><img src="{{ asset('images/logo.png') }}"
                                            style="height: 120px; width: 120px" alt="La Majestueuse"></a>
                                </div>
                            </div>
                            <div class="col-auto">
                                <nav class="main-menu d-none d-lg-inline-block">
                                    <ul>
                                        <li><a href="/">Accueil</a></li>
                                        <li><a href="/#about">À propos</a></li>
                                        <li><a href="/#formations">Formations</a></li>
                                        <li><a href="/#contact">Contactez-nous</a></li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- HERO --}}
    <section class="formation-hero" style="background-image: url('{{ asset($formation['image']) }}');">
        <div class="container">
            <a href="/#formations" class="breadcrumb-link">
                <i class="fas fa-arrow-left"></i> Retour aux formations
            </a>
            <h1>{{ $formation['nom'] }}</h1>
            <p class="hero-sub">{{ $formation['short'] }}</p>
        </div>
    </section>

    {{-- DESCRIPTION --}}
    <section class="formation-description">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <img src="{{ asset($formation['image']) }}" alt="{{ $formation['nom'] }}">
                </div>
                <div class="col-lg-6">
                    <span style="color:#bf1f2b; font-weight:600; text-transform:uppercase; letter-spacing:1px; font-size:14px;">Présentation de la filière</span>
                    <h2 class="mt-2">À propos de la formation</h2>
                    <p>{{ $formation['description'] }}</p>
                    <p>Nos programmes sont conçus pour répondre aux exigences du marché du travail, en combinant enseignements théoriques solides, projets pratiques et stages en entreprise. Nos étudiants bénéficient d'un encadrement personnalisé par des experts du domaine.</p>
                </div>
            </div>
        </div>
    </section>


    {{-- CTA --}}
    <section class="cta-section">
        <div class="container">
            <h2>Prêt à rejoindre la filière {{ $formation['nom'] }} ?</h2>
            <p>Les inscriptions pour la rentrée 2025 sont ouvertes. Réservez votre place dès maintenant.</p>
            <a href="/#formations" class="th-btn">S'inscrire maintenant <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="footer-wrapper footer-layout9" data-bg-src="{{ asset('asset_vitrine/assets/img/update1/bg/footer_bg_4.png') }}">
        <div class="widget-area">
            <div class="container">
                <div class="row justify-content-between">
                    <div class="col-md-6 col-xl-3">
                        <div class="widget footer-widget style2">
                            <div class="th-widget-about">
                                <div class="about-logo">
                                    <a href="/"><img src="{{ asset('images/logo_white.png') }}" alt="La Majestueuse"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="widget widget_nav_menu footer-widget">
                            <h3 class="widget_title">Liens rapides</h3>
                            <ul class="menu">
                                <li><a href="/#about">À propos</a></li>
                                <li><a href="/#formations">Nos formations</a></li>
                                <li><a href="/#contact">Contactez-nous</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="widget footer-widget">
                            <h3 class="widget_title">Contactez-nous</h3>
                            <div class="th-widget-contact">
                                <div class="info-box">
                                    <div class="info-box_icon"><i class="fas fa-location-dot"></i></div>
                                    <div class="info-box_content">
                                        <p class="info-box_text">Ndazoa, près de Mbankomo, Yaoundé, Cameroun</p>
                                    </div>
                                </div>
                                <div class="info-box">
                                    <div class="info-box_icon"><i class="fas fa-phone"></i></div>
                                    <div class="info-box_content">
                                        <p class="info-box_text"><a href="tel:+237691612145">+237 691612145</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright">
            <div class="container">
                <p class="copyright-text text-center">© 2025 <a href="/">INSTITUT UNIVERSITAIRE LA MAJESTUEUSE</a>. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="{{ asset('asset_vitrine/assets/js/vendor/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('asset_vitrine/assets/js/vendor/bootstrap.min.js') }}"></script>
    <script src="{{ asset('asset_vitrine/assets/js/main.js') }}"></script>
</body>

</html>
