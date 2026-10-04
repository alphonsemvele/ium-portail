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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('asset_vitrine/assets/css/app.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('asset_vitrine/assets/css/style.css') }}">

    <style>
    /* ── Variables ── */
    :root {
        --blue:  #1e3a8a;
        --bluel: #2563eb;
        --red:   #bf1f2b;
        --g50:   #f8fafc;
        --g100:  #f1f5f9;
        --g200:  #e2e8f0;
        --g500:  #64748b;
        --g700:  #334155;
        --g900:  #0f172a;
    }
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: 'Poppins', sans-serif; color: var(--g700); margin: 0; overflow-x: hidden; }
    img  { max-width: 100%; display: block; }

    /* ════════════════════════════════
       HEADER TOP BAR
    ════════════════════════════════ */
    .header-top { background: var(--blue); padding: 7px 0; }
    .header-top .container { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
    .header-links ul { list-style: none; margin: 0; padding: 0; display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
    .header-links ul li { display: flex; align-items: center; gap: 6px; font-size: .76rem; color: rgba(255,255,255,.82); }
    .header-links ul li i { color: rgba(255,255,255,.6) !important; font-size: .72rem; }
    .header-links ul li b { color: rgba(255,255,255,.65); font-weight: 500; }
    .header-links ul li a { color: rgba(255,255,255,.85); text-decoration: none; transition: color .2s; }
    .header-links ul li a:hover { color: #fff; }

    /* ── Nav ── */
    .menu-area { background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
    .menu-area .container { display: flex; align-items: center; justify-content: space-between; height: 68px; padding: 0 20px; }
    .header-logo img { height: 52px !important; width: auto !important; }
    .main-menu ul { display: flex; align-items: center; gap: 4px; list-style: none; margin: 0; padding: 0; }
    .main-menu ul li a { display: block; padding: 7px 14px; font-size: .87rem; font-weight: 500; color: var(--g700); border-radius: 6px; text-decoration: none; transition: color .2s, background .2s; }
    .main-menu ul li a:hover { color: var(--blue); background: var(--g100); }
    .sticky-wrapper .sticky-active .menu-area { position: sticky; top: 0; z-index: 999; }

    /* ── Floating buttons ── */
    .float-btn { position: fixed; right: 18px; z-index: 1000; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 4px 14px rgba(0,0,0,.25); transition: transform .2s; text-decoration: none; }
    .float-btn:hover { transform: scale(1.1); }
    .float-btn.wa { bottom: 20px; background: #25D366; }
    .float-btn.fb { bottom: 78px; background: #0084FF; }
    .float-btn i  { color: #fff !important; }

    /* ════════════════════════════════
       HERO
    ════════════════════════════════ */
    .formation-hero {
        position: relative;
        min-height: 500px;
        display: flex;
        align-items: flex-end;
        background-size: cover;
        background-position: center;
        overflow: hidden;
    }
    .formation-hero::before {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(
            to bottom,
            rgba(15,23,42,.2)  0%,
            rgba(15,23,42,.6)  50%,
            rgba(15,23,42,.88) 100%
        );
    }
    .formation-hero::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0; height: 5px;
        background: linear-gradient(90deg, var(--blue), var(--bluel), #06b6d4);
    }
    .formation-hero .container {
        position: relative; z-index: 2;
        max-width: 1200px; margin: 0 auto;
        padding: 40px 20px 52px;
        width: 100%;
    }
    .formation-hero .breadcrumb-link {
        display: inline-flex; align-items: center; gap: 7px;
        font-size: .8rem; font-weight: 600;
        color: rgba(255,255,255,.78);
        background: rgba(255,255,255,.1);
        backdrop-filter: blur(6px);
        padding: 6px 14px; border-radius: 20px;
        text-decoration: none;
        border: 1px solid rgba(255,255,255,.15);
        transition: all .2s;
        margin-bottom: 20px;
        display: inline-flex;
    }
    .formation-hero .breadcrumb-link:hover { background: rgba(255,255,255,.2); color: #fff; }
    .formation-hero h1 {
        font-size: clamp(2rem, 5vw, 3.2rem);
        font-weight: 800; color: #fff !important;
        line-height: 1.15; margin: 0 0 14px;
        text-shadow: 0 2px 20px rgba(0,0,0,.25);
    }
    .formation-hero .hero-sub {
        font-size: clamp(.9rem, 2vw, 1.05rem);
        color: rgba(255,255,255,.82);
        max-width: 620px; line-height: 1.7;
        margin: 0;
    }

    /* ════════════════════════════════
       DESCRIPTION
    ════════════════════════════════ */
    .formation-description {
        padding: 88px 0;
        background: #fff;
    }
    .formation-description .container {
        max-width: 1200px; margin: 0 auto; padding: 0 20px;
    }
    .formation-description .row {
        display: flex; flex-wrap: wrap; gap: 0;
        align-items: center;
    }
    .formation-description .col-lg-6 {
        width: 100%;
    }
    @media(min-width: 992px) {
        .formation-description .col-lg-6 { width: 50%; }
        .formation-description .col-lg-6:first-child { padding-right: 40px; }
        .formation-description .col-lg-6:last-child  { padding-left: 40px; }
    }
    .formation-description img {
        width: 100%; border-radius: 20px;
        box-shadow: 0 24px 64px rgba(0,0,0,.13);
        object-fit: cover; min-height: 340px;
    }
    .formation-description span {
        display: inline-block;
        font-size: .74rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1.5px;
        color: var(--red);
    }
    .formation-description h2 {
        font-size: clamp(1.6rem, 3.5vw, 2.3rem);
        font-weight: 800; color: var(--g900) !important;
        line-height: 1.2; margin: 8px 0 20px;
    }
    .formation-description p {
        font-size: .93rem; color: var(--g500);
        line-height: 1.85; margin-bottom: 16px;
    }

    /* Checklist points clés */
    .formation-description .points-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 24px 0;
    }
    .formation-description .point-item {
        display: flex; align-items: flex-start; gap: 9px;
        padding: 10px 12px;
        background: var(--g50); border-radius: 10px;
        border: 1px solid var(--g200);
        font-size: .82rem; color: var(--g700); line-height: 1.45;
    }
    .formation-description .point-item i { color: var(--blue) !important; font-size: .8rem; margin-top: 2px; flex-shrink: 0; }

    .btn-desc {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--blue); color: #fff !important;
        padding: 13px 28px; border-radius: 8px;
        font-size: .9rem; font-weight: 700;
        text-decoration: none;
        transition: all .22s;
        box-shadow: 0 4px 16px rgba(30,58,138,.3);
        margin-top: 8px;
    }
    .btn-desc:hover { background: var(--bluel); transform: translateY(-2px); color: #fff !important; }
    .btn-desc i { color: #fff !important; }

    /* ════════════════════════════════
       CTA
    ════════════════════════════════ */
    .cta-section {
        position: relative;
        padding: 96px 0;
        text-align: center;
        overflow: hidden;
    }
    .cta-section::before {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(135deg, var(--blue) 0%, #0f172a 60%, #1e1b4b 100%);
    }
    /* Cercle déco haut-droit */
    .cta-section::after {
        content: '';
        position: absolute;
        width: 480px; height: 480px; border-radius: 50%;
        background: rgba(37,99,235,.12);
        top: -180px; right: -100px;
        pointer-events: none;
    }
    .cta-section .container {
        position: relative; z-index: 1;
        max-width: 760px; margin: 0 auto; padding: 0 20px;
    }
    .cta-section h2 {
        font-size: clamp(1.7rem, 4vw, 2.6rem);
        font-weight: 800; color: #fff !important;
        margin: 0 0 16px; line-height: 1.2;
    }
    .cta-section p {
        font-size: 1rem; color: rgba(255,255,255,.76);
        max-width: 600px; margin: 0 auto 36px; line-height: 1.75;
    }
    .cta-section .th-btn {
        display: inline-flex; align-items: center; gap: 9px;
        background: #fff; color: var(--blue) !important;
        padding: 15px 36px; border-radius: 8px;
        font-size: .92rem; font-weight: 700;
        text-decoration: none;
        transition: all .22s;
        box-shadow: 0 6px 22px rgba(0,0,0,.22);
    }
    .cta-section .th-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 32px rgba(0,0,0,.3);
        color: var(--blue) !important;
    }
    .cta-section .th-btn i { color: var(--blue) !important; }

    /* ════════════════════════════════
       FOOTER
    ════════════════════════════════ */
    .footer-wrapper { background: #0d1b3e; padding-top: 52px; }
    .footer-wrapper .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
    .footer-wrapper .row { display: flex; flex-wrap: wrap; gap: 32px 0; padding-bottom: 40px; }
    .footer-wrapper .col-md-6 { width: 100%; }
    @media(min-width:768px)  { .footer-wrapper .col-md-6  { width: 50%; } }
    @media(min-width:1200px) { .footer-wrapper .col-xl-3  { width: 33.333%; } }
    .widget_title { font-size: .95rem; font-weight: 600; color: #fff !important; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid rgba(255,255,255,.08); }
    .menu { list-style: none; padding: 0; margin: 0; }
    .menu li { padding: 3px 0; }
    .menu a { color: rgba(255,255,255,.6); font-size: .83rem; display: block; padding: 4px 0; text-decoration: none; transition: color .2s, padding-left .2s; }
    .menu a:hover { color: #fff; padding-left: 6px; }
    .info-box { display: flex; gap: 10px; margin-bottom: 12px; align-items: flex-start; }
    .info-box_icon { flex-shrink: 0; margin-top: 3px; }
    .info-box_icon i { color: #fbbf24 !important; font-size: .9rem; }
    .info-box_text { font-size: .82rem; color: rgba(255,255,255,.6); line-height: 1.6; margin: 0; }
    .info-box_text a { color: rgba(255,255,255,.6); text-decoration: none; transition: color .2s; }
    .info-box_text a:hover { color: #fff; }
    .about-logo img { height: 52px; width: auto; margin-bottom: 14px; }
    .footer-wrapper .copyright { border-top: 1px solid rgba(255,255,255,.06); padding: 16px 0; }
    .copyright-text { font-size: .78rem; color: rgba(255,255,255,.42); margin: 0; text-align: center; }
    .copyright-text a { color: rgba(255,255,255,.62); text-decoration: none; }

    /* ── Responsive ── */
    @media(max-width:767px) {
        .formation-hero { min-height: 380px; }
        .d-none.d-lg-block { display: none !important; }
        .header-top .col-auto:first-child { display: none; }
    }
    </style>
</head>

<body>

{{-- Boutons flottants --}}
<a href="https://wa.me/+237655341939" class="float-btn wa" target="_blank" title="WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>
<a href="https://www.facebook.com/profile.php?id=61577186635321" class="float-btn fb" target="_blank" title="Messenger">
    <i class="fab fa-facebook-messenger"></i>
</a>

{{-- HEADER --}}
<header class="th-header">
    <div class="header-top">
        <div class="container">
            <div class="header-links">
                <ul>
                    <li><i class="fas fa-envelope"></i><b>Email : </b>info@ium-ndazoa.com</li>
                    <li><i class="fas fa-phone"></i><b>Tél : </b>+237 655341939</li>
                </ul>
            </div>
            <div class="header-links">
                <ul>
                    <li><i class="fas fa-user"></i><a href="/login">Connexion / Inscription</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="sticky-wrapper">
        <div class="sticky-active">
            <div class="menu-area">
                <div class="container">
                    <div class="header-logo">
                        <a href="/"><img src="{{ asset('images/logo.png') }}" alt="La Majestueuse"></a>
                    </div>
                    <nav class="main-menu d-none d-lg-inline-block">
                        <ul>
                            <li><a href="/">Accueil</a></li>
                            <li><a href="/#about">À propos</a></li>
                            <li><a href="/#formations" style="color:#1e3a8a;font-weight:600;">Formations</a></li>
                            <li><a href="/#actualites">Actualités</a></li>
                            <li><a href="/#contact">Contactez-nous</a></li>
                        </ul>
                    </nav>
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

                <div class="points-grid">
                    <div class="point-item"><i class="fas fa-check-circle"></i> Programme aligné aux standards</div>
                    <div class="point-item"><i class="fas fa-check-circle"></i> Formateurs expérimentés</div>
                    <div class="point-item"><i class="fas fa-check-circle"></i> Stages en entreprise</div>
                    <div class="point-item"><i class="fas fa-check-circle"></i> Diplôme reconnu État</div>
                    <div class="point-item"><i class="fas fa-check-circle"></i> Insertion professionnelle</div>
                    <div class="point-item"><i class="fas fa-check-circle"></i> Encadrement personnalisé</div>
                </div>

                <a href="/#contact" class="btn-desc">
                    <i class="fas fa-paper-plane"></i> Demander des informations
                </a>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="cta-section">
    <div class="container">
        <h2>Prêt à rejoindre la filière {{ $formation['nom'] }} ?</h2>
        <p>Les inscriptions pour la rentrée 2026–2027 sont ouvertes. Réservez votre place dès maintenant.</p>
        <a href="/#formations" class="th-btn">
            S'inscrire maintenant <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>

{{-- FOOTER --}}
<footer class="footer-wrapper">
    <div class="container">
        <div class="row justify-content-between">
            <div class="col-md-6 col-xl-3">
                <div class="about-logo">
                    <a href="/"><img src="{{ asset('images/logo.png') }}" alt="La Majestueuse"></a>
                </div>
                <p style="color:rgba(255,255,255,.55);font-size:.82rem;line-height:1.65;margin-bottom:16px;">Institut Universitaire la Majestueuse de Ndazoa — Excellence, Professionnalisme, Avenir.</p>
            </div>
            <div class="col-md-6 col-xl-3">
                <h3 class="widget_title">Liens rapides</h3>
                <ul class="menu">
                    <li><a href="/#about">À propos</a></li>
                    <li><a href="/#formations">Nos formations</a></li>
                    <li><a href="/#actualites">Actualités</a></li>
                    <li><a href="/#contact">Contactez-nous</a></li>
                    <li><a href="/login">Espace étudiant</a></li>
                </ul>
            </div>
            <div class="col-md-6 col-xl-3">
                <h3 class="widget_title">Contactez-nous</h3>
                <div class="info-box">
                    <div class="info-box_icon"><i class="fas fa-location-dot"></i></div>
                    <p class="info-box_text">Ndazoa, 7 km de Mbankomo, station Green Oil, route Yaoundé-Douala.</p>
                </div>
                <div class="info-box">
                    <div class="info-box_icon"><i class="fas fa-phone"></i></div>
                    <p class="info-box_text"><a href="tel:+237655341939">+237655341939</a></p>
                </div>
                <div class="info-box">
                    <div class="info-box_icon"><i class="fas fa-envelope"></i></div>
                    <p class="info-box_text"><a href="mailto:info@ism-ndazoa.com">info@ium-ndazoa.com</a></p>
                </div>
            </div>
        </div>
    </div>
    <div class="copyright">
        <div class="container">
            <p class="copyright-text">© {{ date('Y') }} <a href="/">Institut Universitaire la Majestueuse</a>. Tous droits réservés.</p>
        </div>
    </div>
</footer>

<script src="{{ asset('asset_vitrine/assets/js/vendor/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('asset_vitrine/assets/js/vendor/bootstrap.min.js') }}"></script>
<script src="{{ asset('asset_vitrine/assets/js/main.js') }}"></script>
</body>
</html>