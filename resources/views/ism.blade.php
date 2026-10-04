<?php
/**
 * resources/views/pages/vitrine/index.blade.php
 * Vitrine IUM — mise à jour filières 2026-2027
 */
?>
<!DOCTYPE html>
<html class="no-js" lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Institut Universitaire la Majestueuse de Ndazoa</title>
    <meta name="description" content="L'Institut Universitaire la Majestueuse de NDAZOA">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
    <link rel="icon" type="image/png" href="{{ asset('asset_vitrine/assets/img/logo.png') }}">
    <meta name="theme-color" content="#1e3a8a">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('asset_vitrine/assets/css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset_vitrine/assets/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <link rel="stylesheet" href="{{ asset('asset_vitrine/assets/css/style.css') }}">

    <style>
    :root{--blue:#1e3a8a;--blue-l:#2f54a4;--red:#bf1f2b;--red-h:#9b1822;--white:#ffffff;--off-white:#f8fafc;--g100:#f1f5f9;--g200:#e2e8f0;--g500:#64748b;--g700:#334155;--g900:#0f172a;--r-sm:6px;--r-md:10px;--r-lg:16px;--sh-sm:0 1px 4px rgba(0,0,0,.08);--sh-md:0 4px 14px rgba(0,0,0,.10);--sh-lg:0 10px 32px rgba(0,0,0,.13);--t:.24s ease;}
    *,*::before,*::after{box-sizing:border-box;}
    body{font-family:'Poppins',sans-serif;color:var(--g700);line-height:1.7;overflow-x:hidden;margin:0;}
    img{max-width:100%;height:auto;display:block;}
    a{text-decoration:none;}
    p,li,span,.sec-text,.cta-text,.about-text,.hero-text,.student-text,.admission-card_text,.testi-list_text,.contact-feature_link,.info-box_text,.blog-text,.checklist li{text-align:left!important;}
    .text-center{text-align:center!important;}
    h1,h2,h3,h4,h5,h6{font-family:'Poppins',sans-serif;line-height:1.25;color:var(--g900);}
    i[class*="fa"]{visibility:visible!important;display:inline-block!important;}

    /* PRELOADER */
    .magnificent-preloader{position:fixed;inset:0;background:linear-gradient(135deg,var(--blue),#101d3f 55%,var(--red));background-size:300% 300%;animation:gradBg 7s ease infinite;display:flex;flex-direction:column;align-items:center;justify-content:center;z-index:9999;gap:20px;}
    @keyframes gradBg{0%,100%{background-position:0 50%}50%{background-position:100% 50%}}
    .pre-logo{width:108px;height:108px;border-radius:50%;background:var(--white);padding:16px;animation:floatY 3s ease-in-out infinite;box-shadow:0 0 40px rgba(255,255,255,.22);}
    .pre-logo img{width:100%;height:100%;object-fit:contain;}
    @keyframes floatY{0%,100%{transform:translateY(0)}50%{transform:translateY(-14px)}}
    .pre-label{color:rgba(255,255,255,.9);font-size:clamp(.82rem,2vw,1rem);text-align:center;}
    .pre-bar-wrap{width:min(270px,80vw);height:5px;background:rgba(255,255,255,.2);border-radius:99px;overflow:hidden;}
    .pre-bar{height:100%;width:0;background:var(--white);border-radius:99px;animation:barLoad 3s ease forwards;box-shadow:0 0 12px rgba(255,255,255,.5);}
    @keyframes barLoad{to{width:100%}}
    .pre-spinner{width:38px;height:38px;border:3px solid rgba(255,255,255,.22);border-top-color:var(--white);border-radius:50%;animation:spin 1s linear infinite;}
    @keyframes spin{to{transform:rotate(360deg)}}
    .pre-fadeout{animation:fadeOut .8s ease forwards;}
    @keyframes fadeOut{to{opacity:0;visibility:hidden}}

    /* HEADER */
    .header-top{background:var(--blue);padding:6px 0;}
    .header-top .container{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;}
    .htop-info{display:flex;align-items:center;gap:18px;list-style:none;margin:0;padding:0;flex-wrap:wrap;}
    .htop-info li{display:flex;align-items:center;gap:6px;font-size:.77rem;color:rgba(255,255,255,.85);}
    .htop-info li i{color:rgba(255,255,255,.7)!important;font-size:.75rem;}
    .htop-info li a{color:rgba(255,255,255,.85);transition:color var(--t);}
    .htop-info li a:hover{color:var(--white);}
    .h-social{display:flex;gap:7px;}
    .h-social a{width:25px;height:25px;border-radius:50%;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;color:var(--white)!important;font-size:.68rem;transition:background var(--t);}
    .h-social a i{color:var(--white)!important;}
    .h-social a:hover{background:rgba(255,255,255,.28);}
    .menu-area{background:var(--white);box-shadow:0 2px 14px rgba(0,0,0,.08);position:relative;z-index:100;}
    .menu-area .container{display:flex;align-items:center;justify-content:space-between;height:64px;}
    .header-logo img{height:48px!important;width:auto!important;}
    .main-menu{display:none;}
    .main-menu ul{display:flex;align-items:center;gap:2px;list-style:none;margin:0;padding:0;}
    .main-menu ul li a{display:block;padding:7px 13px;font-size:.86rem;font-weight:500;color:var(--g700);border-radius:var(--r-sm);transition:color var(--t),background var(--t);}
    .main-menu ul li a:hover{color:var(--blue);background:var(--g100);}
    .sticky-active{transition:box-shadow .3s;}
    .sticky-active.fixed-top{position:fixed;top:0;left:0;right:0;z-index:999;animation:slideDown .3s ease;}
    @keyframes slideDown{from{transform:translateY(-100%)}to{transform:translateY(0)}}
    .th-menu-toggle{background:none;border:1.5px solid var(--g200);border-radius:var(--r-sm);padding:7px 10px;cursor:pointer;color:var(--blue);font-size:1.05rem;line-height:1;display:flex;align-items:center;}
    .th-menu-toggle i{color:var(--blue)!important;}

    /* BOUTONS */
    .th-btn{display:inline-flex;align-items:center;gap:7px;padding:11px 22px;font-family:'Poppins',sans-serif;font-size:.875rem;font-weight:600;border-radius:var(--r-sm);border:2px solid transparent;cursor:pointer;transition:all var(--t);white-space:nowrap;text-decoration:none;line-height:1;}
    .th-btn:not(.style4){background:var(--blue);color:var(--white)!important;border-color:var(--blue);}
    .th-btn:not(.style4):hover{background:var(--blue-l);border-color:var(--blue-l);transform:translateY(-2px);box-shadow:0 6px 18px rgba(30,58,138,.25);color:var(--white)!important;}
    .th-btn.style4{background:transparent;color:var(--blue)!important;border-color:var(--blue);}
    .th-btn.style4:hover{background:var(--blue);color:var(--white)!important;transform:translateY(-2px);}
    .th-btn i{color:inherit!important;font-size:.82rem;}
    .btn-brochure{display:inline-flex;align-items:center;justify-content:center;gap:7px;width:100%;padding:9px 14px;font-size:.8rem;font-weight:600;border-radius:var(--r-sm);border:1.5px solid var(--blue);background:transparent;color:var(--blue)!important;cursor:pointer;transition:all var(--t);text-decoration:none;}
    .btn-brochure i{color:var(--blue)!important;font-size:.77rem;}
    .btn-brochure:hover{background:var(--blue);color:var(--white)!important;}
    .btn-brochure:hover i{color:var(--white)!important;}
    .btn-dl-doc{display:inline-flex;align-items:center;gap:5px;background:var(--blue);color:var(--white)!important;padding:4px 11px;border-radius:20px;font-size:.72rem;font-weight:600;border:none;cursor:pointer;transition:all var(--t);text-decoration:none;}
    .btn-dl-doc i{color:var(--white)!important;font-size:.7rem;}
    .btn-dl-doc:hover{background:var(--blue-l);transform:scale(1.04);color:var(--white)!important;}
    .btn-voir-plus{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:10px 14px;background:transparent;color:var(--blue)!important;border:1.5px solid var(--blue);border-radius:8px;font-size:.85rem;font-weight:500;text-decoration:none;transition:all .3s ease;}
    .btn-voir-plus:hover{background:var(--blue);color:#fff!important;transform:translateY(-2px);box-shadow:0 4px 12px rgba(26,115,232,.25);}
    .btn-voir-plus i{color:inherit!important;}

    /* HERO */
    .th-hero-wrapper{background:linear-gradient(135deg,#1a73e8,#34c759);color:#fff;}

    /* CTA */
    .bg-smoke-half{padding:52px 0;}
    .cta-box{border-radius:var(--r-lg);padding:36px 30px;background-size:cover;background-position:center;position:relative;overflow:hidden;height:100%;}
    .cta-box::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(14,30,80,.87),rgba(14,30,80,.65));}
    .cta-box>*{position:relative;z-index:1;}
    .cta-title{font-size:clamp(1.2rem,2.5vw,1.55rem);font-weight:700;color:var(--white)!important;margin-bottom:10px;}
    .cta-text{color:rgba(255,255,255,.88)!important;margin-bottom:20px;font-size:.9rem;}

    /* ADMISSION */
    .admission-info{background:linear-gradient(135deg,var(--blue-l),#101d3f);padding:64px 24px;}
    .admission-container{max-width:1160px;margin:0 auto;}
    .admission-info h2{font-size:clamp(1.5rem,4vw,2.3rem);color:var(--white)!important;text-align:center;margin-bottom:10px;}
    .admission-intro{text-align:center;margin-bottom:40px;color:rgba(255,255,255,.88)!important;font-size:.94rem;}
    .admission-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:18px;}
    .admission-grid .admission-card{background:rgba(255,255,255,.10);backdrop-filter:blur(8px);padding:22px 18px;border-radius:var(--r-md);border:1px solid rgba(255,255,255,.18);transition:transform var(--t),box-shadow var(--t);}
    .admission-grid .admission-card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.2);}
    .admission-grid .admission-card h3{font-size:.97rem;color:var(--white)!important;margin-bottom:12px;display:flex;align-items:flex-start;gap:10px;line-height:1.3;}
    .admission-grid .admission-card h3 i{font-size:1.35rem;color:#fbbf24!important;flex-shrink:0;margin-top:1px;}
    .admission-grid .admission-card p{color:rgba(255,255,255,.85)!important;font-size:.88rem;margin:5px 0;}
    .admission-grid .admission-card strong{color:var(--white)!important;font-weight:600;}

    /* SECTIONS */
    .video-area-1{padding:56px 0;}
    .sec-title{font-size:clamp(1.35rem,3vw,2rem);font-weight:700;color:var(--blue)!important;margin-bottom:12px;}
    .checklist ul{list-style:none;padding:0;margin:0;}
    .checklist ul li{padding:4px 0 4px 22px;position:relative;font-size:.86rem;color:var(--g700);}
    .checklist ul li::before{content:'\f00c';font-family:'Font Awesome 6 Free';font-weight:900;position:absolute;left:0;top:7px;color:var(--blue);font-size:.72rem;}
    .filiere-badge{display:inline-block;background:var(--blue);color:var(--white)!important;padding:3px 11px;border-radius:4px;font-size:.78rem;font-weight:600;margin-bottom:10px;letter-spacing:.5px;}

    /* FORMATIONS CARDS */
    #formations{background:var(--g100);}
    #formations .admission-card{border-radius:var(--r-md);overflow:hidden;box-shadow:var(--sh-sm);background:var(--white);border:1px solid var(--g200);display:flex;flex-direction:column;height:100%;transition:transform var(--t),box-shadow var(--t);}
    #formations .admission-card:hover{transform:translateY(-5px);box-shadow:var(--sh-lg);}
    #formations .admission-card_img{overflow:hidden;height:175px;position:relative;}
    #formations .admission-card_img img{width:100%;height:100%;object-fit:cover;transition:transform .35s ease;}
    #formations .admission-card:hover .admission-card_img img{transform:scale(1.06);}
    #formations .admission-card_content{padding:16px 15px 18px;flex:1;display:flex;flex-direction:column;gap:9px;}
    .admission-card_title{font-size:.92rem;font-weight:600;color:var(--g900);line-height:1.35;margin:0;min-height:2.7rem;display:flex;align-items:flex-start;}
    .admission-card_text{font-size:.82rem;color:var(--g500);line-height:1.6;flex:1;margin:0;}
    .link-btn{display:inline-flex;align-items:center;gap:6px;font-size:.81rem;font-weight:600;color:var(--blue)!important;transition:gap var(--t),color var(--t);}
    .link-btn i{color:var(--blue)!important;font-size:.74rem;}
    .link-btn:hover{gap:10px;color:var(--red)!important;}
    .link-btn:hover i{color:var(--red)!important;}

    /* INSTITUTS SECTION */
    .inst-header{display:flex;align-items:center;gap:12px;padding:12px 18px;border-radius:10px;margin-bottom:16px;}
    .inst-icon{width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .inst-icon i{color:white!important;font-size:1.1rem;}
    .inst-title{font-size:.95rem;font-weight:700;color:white;margin:0;line-height:1.2;}
    .inst-subtitle{font-size:.76rem;color:rgba(255,255,255,.75);margin:2px 0 0;}
    .filiere-col h5{font-size:.8rem;font-weight:700;color:var(--blue);margin:0 0 6px;padding-bottom:4px;border-bottom:1px solid var(--g200);}
    .filiere-col ul{list-style:none;padding:0;margin:0;}
    .filiere-col ul li{font-size:.76rem;color:var(--g700);padding:2px 0 2px 12px;position:relative;line-height:1.5;}
    .filiere-col ul li::before{content:'•';position:absolute;left:0;color:var(--blue);font-size:.8rem;}
    .epreuves-tag{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);border-radius:6px;padding:6px 12px;font-size:.75rem;font-weight:600;color:white;margin-top:12px;}
    .epreuves-tag i{color:#fbbf24!important;margin-right:5px;}

    /* MODAL */
    .modal{display:none;position:fixed;inset:0;z-index:2000;background:rgba(0,0,0,.52);justify-content:center;align-items:center;padding:16px;}
    .modal-content{background:var(--white);border-radius:var(--r-lg);padding:32px 26px;width:100%;max-width:570px;position:relative;box-shadow:0 24px 60px rgba(0,0,0,.18);max-height:90vh;overflow-y:auto;}
    .modal-content h3{font-size:1.2rem;font-weight:700;color:var(--blue);text-align:center;margin:0 0 22px;}
    .modal-close{position:absolute;top:14px;right:16px;font-size:1.3rem;cursor:pointer;color:var(--g500);line-height:1;transition:color var(--t);background:none;border:none;}
    .modal-close:hover{color:var(--red);}
    .modal-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;}
    .modal-field{display:flex;flex-direction:column;gap:4px;}
    .modal-field label{font-size:.79rem;font-weight:600;color:var(--g700);}
    .modal-field input,.modal-field select{padding:10px 13px;border:1.5px solid var(--g200);border-radius:var(--r-sm);font-family:'Poppins',sans-serif;font-size:.87rem;color:var(--g900);transition:border-color var(--t),box-shadow var(--t);width:100%;}
    .modal-field input:focus,.modal-field select:focus{outline:none;border-color:var(--blue);box-shadow:0 0 0 3px rgba(30,58,138,.1);}
    .modal-field input[readonly]{background:var(--g100);color:var(--g500);cursor:not-allowed;}

    /* TÉMOIGNAGES */
    .testi-list{background:rgba(255,255,255,.09);backdrop-filter:blur(8px);border-radius:var(--r-md);padding:24px;border:1px solid rgba(255,255,255,.14);height:100%;}
    .testi-list_img{display:flex;align-items:flex-start;gap:12px;margin-bottom:14px;}
    .testi-list_img>img{width:54px;height:54px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.35);}
    .testi-list_name{font-size:.95rem;font-weight:600;color:var(--white)!important;margin:0;}
    .testi-list_desig{font-size:.78rem;color:rgba(255,255,255,.68)!important;}
    .testi-list_text{color:rgba(255,255,255,.87)!important;font-size:.88rem;margin-bottom:12px;}
    .testi-list_review i{color:#fbbf24!important;font-size:.83rem;}

    /* GALERIE */
    .gallery-card{border-radius:var(--r-md);overflow:hidden;}
    .gallery-img{position:relative;overflow:hidden;}
    .gallery-img img{width:100%;height:230px;object-fit:cover;transition:transform .38s ease;}
    .gallery-card:hover .gallery-img img{transform:scale(1.05);}
    .gallery-btn{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(30,58,138,.55);opacity:0;transition:opacity var(--t);color:var(--white)!important;font-size:1.35rem;}
    .gallery-btn i{color:var(--white)!important;}
    .gallery-card:hover .gallery-btn{opacity:1;}

    /* BLOG */
    .blog-recent{background:var(--white);border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--sh-sm);border:1px solid var(--g200);transition:box-shadow var(--t),transform var(--t);height:100%;display:flex;flex-direction:column;}
    .blog-recent:hover{box-shadow:var(--sh-lg);transform:translateY(-4px);}
    .blog-img{overflow:hidden;position:relative;flex-shrink:0;}
    .blog-img img{transition:transform .35s ease;}
    .blog-recent:hover .blog-img>img{transform:scale(1.04);}
    .blog-content{padding:20px 20px 24px;flex:1;display:flex;flex-direction:column;}
    .blog-meta{margin-bottom:7px;}
    .blog-meta a{font-size:.78rem;color:var(--g500)!important;display:inline-flex;align-items:center;gap:5px;}
    .blog-meta a i{color:var(--blue)!important;}
    .blog-title{font-size:.97rem;font-weight:600;color:var(--g900);margin-bottom:7px;line-height:1.35;}
    .blog-text{font-size:.86rem;color:var(--g500);margin-bottom:14px;flex:1;}
    .badge-photos{position:absolute;top:10px;right:10px;background:rgba(0,0,0,.62);color:var(--white)!important;padding:4px 10px;border-radius:20px;font-size:.7rem;display:flex;align-items:center;gap:5px;z-index:2;}
    .badge-photos i{color:var(--white)!important;}
    .article-docs{margin:12px 0;padding:12px 14px;background:var(--g100);border-radius:var(--r-sm);border-left:3px solid var(--blue);}
    .article-docs-title{font-size:.8rem;font-weight:600;color:var(--g700);margin:0 0 7px;display:flex;align-items:center;gap:6px;}
    .article-docs-title i{color:var(--blue)!important;}
    .article-doc-link{display:flex;align-items:center;gap:7px;font-size:.8rem;color:var(--blue)!important;padding:4px 5px;border-radius:4px;transition:background var(--t);text-decoration:none;}
    .article-doc-link .icon-pdf{color:var(--red)!important;}
    .article-doc-link .icon-dl{margin-left:auto;color:var(--g500)!important;font-size:.7rem;}
    .article-doc-link:hover{background:var(--g200);}

    /* CONTACT */
    .contact-feature{display:flex;gap:14px;margin-bottom:20px;align-items:flex-start;}
    .contact-feature-icon{width:42px;height:42px;min-width:42px;border-radius:50%;background:#1e3a8a!important;display:flex;align-items:center;justify-content:center;color:#ffffff!important;flex-shrink:0;align-self:flex-start;}
    .contact-feature-icon i{color:#ffffff!important;font-size:.95rem;}
    .contact-feature_label{font-size:.76rem;font-weight:600;color:var(--blue);text-transform:uppercase;letter-spacing:.6px;margin:0 0 3px;}
    .contact-feature_link{font-size:.87rem;color:var(--g700);display:block;line-height:1.55;}
    .contact-form-wrap{background:var(--white);border-radius:var(--r-lg);padding:30px 26px;box-shadow:var(--sh-md);border:1px solid var(--g200);}
    .form-control.style-white{width:100%;padding:10px 14px;border:1.5px solid var(--g200);border-radius:var(--r-sm);font-family:'Poppins',sans-serif;font-size:.87rem;color:var(--g900);background:var(--off-white);transition:border-color var(--t),box-shadow var(--t);appearance:auto;}
    .form-control.style-white:focus{outline:none;border-color:var(--blue);box-shadow:0 0 0 3px rgba(30,58,138,.1);background:var(--white);}
    textarea.form-control.style-white{resize:vertical;min-height:105px;}
    .form-group{position:relative;margin-bottom:15px;}
    .form-group>i{position:absolute;right:13px;top:50%;transform:translateY(-50%);color:var(--g500)!important;font-size:.85rem;pointer-events:none;}
    .form-group textarea~i{top:18px;transform:none;}
    .border-title{font-size:clamp(1.25rem,3vw,1.85rem);font-weight:700;color:var(--g900);margin-bottom:13px;padding-left:13px;border-left:4px solid var(--blue);}
    .sub-title{display:inline-block;font-size:.76rem;font-weight:600;text-transform:uppercase;letter-spacing:1.5px;color:var(--blue);margin-bottom:7px;}

    /* NEWSLETTER & FOOTER */
    .newsletter-wrap{background:var(--blue);border-radius:var(--r-lg);padding:34px 30px;display:flex;flex-wrap:wrap;align-items:center;gap:26px;}
    .nl-col-form{flex:1;min-width:240px;}
    .nl-col-social{flex:0 0 auto;}
    .nl-col-form h3,.nl-col-social h3{font-size:1.05rem;font-weight:700;color:var(--white)!important;margin-bottom:13px;}
    .nl-form{display:flex;gap:9px;flex-wrap:wrap;}
    .nl-form input{flex:1;min-width:180px;padding:10px 15px;border-radius:var(--r-sm);border:none;font-family:'Poppins',sans-serif;font-size:.87rem;}
    .nl-form input:focus{outline:2px solid rgba(255,255,255,.55);}
    .nl-form .th-btn{background:var(--red);border-color:var(--red);}
    .nl-form .th-btn:hover{background:var(--red-h);border-color:var(--red-h);}
    .th-social{display:flex;gap:9px;}
    .th-social a{width:35px;height:35px;border-radius:50%;background:rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;color:var(--white)!important;font-size:.83rem;transition:background var(--t),transform var(--t);}
    .th-social a i{color:var(--white)!important;}
    .th-social a:hover{background:rgba(255,255,255,.32);transform:translateY(-3px);}
    .footer-wrapper{background:#0d1b3e;padding-top:52px;}
    .widget_title{font-size:.97rem;font-weight:600;color:var(--white)!important;margin-bottom:18px;padding-bottom:9px;border-bottom:2px solid rgba(255,255,255,.09);}
    .footer-menu{list-style:none;padding:0;margin:0;}
    .footer-menu li{padding:3px 0;}
    .footer-menu a{color:rgba(255,255,255,.68);font-size:.85rem;display:block;padding:3px 0;transition:color var(--t),padding-left var(--t);}
    .footer-menu a:hover{color:var(--white);padding-left:6px;}
    .info-box{display:flex;gap:11px;margin-bottom:13px;align-items:flex-start;}
    .info-box_icon{color:#fbbf24!important;font-size:.95rem;margin-top:3px;min-width:17px;flex-shrink:0;}
    .info-box_icon i{color:#fbbf24!important;}
    .info-box_text{font-size:.82rem;color:rgba(255,255,255,.68)!important;line-height:1.6;margin:0;}
    .info-box_text a{color:rgba(255,255,255,.68);transition:color var(--t);}
    .info-box_text a:hover{color:var(--white);}
    .copyright{border-top:1px solid rgba(255,255,255,.07);padding:17px 0;margin-top:38px;}
    .copyright-text{font-size:.8rem;color:rgba(255,255,255,.5)!important;margin:0;}
    .copyright-text a{color:rgba(255,255,255,.75);}
    .footer-links ul{list-style:none;padding:0;margin:0;display:flex;gap:14px;justify-content:flex-end;flex-wrap:wrap;}
    .footer-links ul li a{font-size:.8rem;color:rgba(255,255,255,.5);transition:color var(--t);}
    .footer-links ul li a:hover{color:var(--white);}

    /* FLOATING */
    .float-btn{position:fixed;right:18px;z-index:1000;width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.25rem;text-decoration:none;box-shadow:0 4px 14px rgba(0,0,0,.25);transition:transform var(--t),box-shadow var(--t);}
    .float-btn:hover{transform:scale(1.1);box-shadow:0 6px 20px rgba(0,0,0,.3);}
    .float-btn.wa{bottom:20px;background:#25D366;color:var(--white)!important;}
    .float-btn.wa i{color:var(--white)!important;}
    .float-btn.fb{bottom:78px;background:#0084FF;color:var(--white)!important;}
    .float-btn.fb i{color:var(--white)!important;}

    /* UTILITAIRES */
    .space{padding:60px 0;}
    .space-bottom{padding-bottom:60px;}
    .mt-30{margin-top:30px!important;}
    .mb-30{margin-bottom:30px!important;}
    .mt-40{margin-top:40px!important;}
    .mt-4{margin-top:1rem!important;}
    .mt-3{margin-top:.75rem!important;}
    .ms-2{margin-left:.5rem!important;}
    .bg-smoke{background:var(--g100);}
    .gy-4>*{padding-top:1rem;padding-bottom:1rem;}
    #toggleMore{background:none;border:none;font-family:'Poppins',sans-serif;font-size:.84rem;font-weight:600;color:var(--blue);cursor:pointer;display:inline-flex;align-items:center;gap:6px;padding:5px 0;transition:color var(--t);}
    #toggleMore i{color:var(--blue)!important;}
    #toggleMore:hover{color:var(--red);}

    /* RESPONSIVE */
    @media(max-width:480px){
        .header-top{display:none!important;}
        .header-logo img{height:40px!important;}
        .menu-area .container{height:56px;}
        .admission-grid{grid-template-columns:1fr;}
        .modal-row{grid-template-columns:1fr;}
        .float-btn{width:42px;height:42px;font-size:1.1rem;right:12px;}
        .float-btn.wa{bottom:14px;}
        .float-btn.fb{bottom:66px;}
        .space{padding:44px 0;}
        .bg-smoke-half{padding:36px 0;}
    }
    @media(min-width:481px) and (max-width:767px){
        .header-logo img{height:44px!important;}
        .admission-grid{grid-template-columns:1fr 1fr;}
        .nl-form{flex-direction:column;}
    }
    @media(min-width:768px) and (max-width:1023px){
        .header-logo img{height:48px!important;}
        .admission-grid{grid-template-columns:1fr 1fr;}
    }
    @media(min-width:1024px){
        .main-menu{display:inline-block;}
        .th-menu-toggle{display:none!important;}
        .header-logo img{height:50px!important;}
        .hero-title{font-size:3.2rem;}
        .admission-grid{grid-template-columns:repeat(4,1fr);}
    }
    @media(min-width:1280px){.hero-title{font-size:3.6rem;}}
    </style>
</head>
<body>

<!-- PRELOADER -->
<div class="magnificent-preloader" id="magnificentPreloader">
    <div class="pre-logo"><img src="{{ asset('images/logo.png') }}" alt="IUM"></div>
    <p class="pre-label">Institut Universitaire la Majestueuse</p>
    <div class="pre-bar-wrap"><div class="pre-bar"></div></div>
    <p class="pre-label" style="font-size:.77rem;opacity:.72;">Chargement en cours…</p>
    <div class="pre-spinner"></div>
</div>

<!-- MOBILE MENU -->
<div class="th-menu-wrapper">
    <div class="th-menu-area" style="padding:20px 16px;">
        <button class="th-menu-toggle" style="margin-bottom:16px;"><i class="fas fa-times"></i> Fermer</button>
        <div style="text-align:center;margin-bottom:16px;">
            <img src="{{ asset('images/logo.png') }}" style="height:50px;width:auto;margin:0 auto;" alt="IUM">
        </div>
        <ul style="list-style:none;padding:0;margin:0;">
            <li style="border-bottom:1px solid #eee;"><a href="#" style="display:block;padding:13px 8px;font-size:.9rem;color:#1e3a8a;font-weight:600;">Accueil</a></li>
            <li style="border-bottom:1px solid #eee;"><a href="#about" style="display:block;padding:13px 8px;font-size:.9rem;color:#334155;">À propos</a></li>
            <li style="border-bottom:1px solid #eee;"><a href="#formations" style="display:block;padding:13px 8px;font-size:.9rem;color:#334155;">Formations</a></li>
            <li style="border-bottom:1px solid #eee;"><a href="#actualites" style="display:block;padding:13px 8px;font-size:.9rem;color:#334155;">Actualités</a></li>
            <li><a href="#contact" style="display:block;padding:13px 8px;font-size:.9rem;color:#334155;">Contactez-nous</a></li>
        </ul>
    </div>
</div>

<!-- HEADER -->
<header class="th-header onepage-nav" id="siteHeader">
    <div class="header-top">
        <div class="container">
            <ul class="htop-info">
                <li><i class="fas fa-envelope"></i> info@ium-ndazoa.com</li>
                <li><i class="fas fa-phone"></i> +237 655 34 19 39</li>
            </ul>
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="h-social">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
                <span style="font-size:.77rem;color:rgba(255,255,255,.8);">
                    <i class="fas fa-user" style="color:rgba(255,255,255,.7);margin-right:4px;"></i>
                    <a href="/login" style="color:rgba(255,255,255,.85);">Connexion</a> /
                    <a href="/register" style="color:rgba(255,255,255,.85);">Inscription</a>
                </span>
            </div>
        </div>
    </div>
    <div class="sticky-wrapper">
        <div class="sticky-active" id="mainNav">
            <div class="menu-area">
                <div class="container">
                    <div class="header-logo">
                        <a href="#"><img src="{{ asset('images/logo.png') }}" alt="Institut Universitaire la Majestueuse"></a>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <nav class="main-menu">
                            <ul>
                                <li><a href="#">Accueil</a></li>
                                <li><a href="#about">À propos</a></li>
                                <li><a href="#formations">Formations</a></li>
                                <li><a href="#actualites">Actualités</a></li>
                                <li><a href="#contact">Contactez-nous</a></li>
                            </ul>
                        </nav>
                        <button type="button" class="th-menu-toggle d-lg-none"><i class="far fa-bars"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- FLOATING -->
<a href="https://wa.me/+237655341939?text=Bonjour%2C%20je%20souhaite%20avoir%20plus%20d%27informations%20sur%20les%20formations%20de%20l%27IUM."
   class="float-btn wa" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
<a href="https://www.facebook.com/profile.php?id=61577186635321"
   class="float-btn fb" target="_blank" title="Facebook"><i class="fab fa-facebook-messenger"></i></a>

<!-- HERO -->
<div class="th-hero-wrapper hero-15 th-carousel" data-slide-show="1" data-md-slide-show="1" data-fade="true" id="hero">
    <div class="th-hero-slide" data-aos="fade-up" data-aos-duration="1000">
        <div class="th-hero-bg" data-bg-src="{{ asset('asset_vitrine/assets/img/update1/hero/hero_bg_10_1.jpg') }}"></div>
        <div class="container">
            <div class="hero-style15">
                <h1 class="hero-title" data-aos="slide-down" data-aos-delay="300">Votre avenir commence ici</h1>
                <p class="hero-text" data-aos="fade-up" data-aos-delay="400" style="max-width:700px;margin:0 auto 30px;">
                    L'Institut Universitaire La Majestueuse — 3 instituts, des dizaines de filières, un campus High Tech à Ndazoa. Rejoignez la première cuvée de l'excellence au Cameroun.
                </p>
                <div class="btn-group" data-aos="slide-up" data-aos-delay="300">
                    <a href="{{ route('preinscription.index') }}" class="th-btn">Rejoignez-nous <i class="fas fa-long-arrow-right ms-2"></i></a>
                    <a href="#formations" class="th-btn style4">Nos programmes <i class="fas fa-long-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA BOXES -->
<section class="bg-smoke-half" data-aos="fade-up" data-aos-duration="800">
    <div class="container">
        <div class="row gy-4">
            <div class="col-xl-6" data-aos="fade-right" data-aos-delay="100">
                <div class="cta-box" style="background-image:url('{{ asset('asset_vitrine/assets/img/update1/bg/cta_bg_5.jpg') }}');">
                    <h3 class="cta-title">Inscrivez-vous pour 2026–2027</h3>
                    <p class="cta-text">Rejoignez nos 3 instituts pour acquérir des compétences pratiques et démarrer une carrière prometteuse.</p>
                    <a href="{{ route('preinscription.index') }}" class="th-btn">S'inscrire maintenant <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-xl-6" data-aos="fade-left" data-aos-delay="100">
                <div class="cta-box" style="background-image:url('{{ asset('asset_vitrine/assets/img/update1/bg/cta_bg_6.jpg') }}');">
                    <h3 class="cta-title">Concours d'entrée 2026</h3>
                    <p class="cta-text">4 sessions : 06 Juin · 18 Juillet · 05 Sept. · 26 Sept. Frais : 15 000 FCFA (dossier) ou 10 000 FCFA (écrit).</p>
                    <a href="#concours" class="th-btn">Voir l'avis de concours <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- AVIS CONCOURS -->
<section class="space bg-smoke" id="concours" data-aos="fade-up" data-aos-duration="800">
    <div class="container">
        <div class="text-center" style="margin-bottom:40px;">
            <span class="sub-title" style="color:var(--red);">Officiel · MINESUP 2026</span>
            <h2 class="sec-title" style="text-align:center;color:var(--g900);">Avis de Concours d'Entrée 2026–2027</h2>
            <p style="color:var(--g500);font-size:.92rem;max-width:640px;margin:0 auto;">
                Autorisation N°26-02352/L/MINESUP/SG/DDES/ESUP/SDA/ANAP du 16 mars 2026
            </p>
        </div>
        <div class="row gy-4 align-items-center">
            <div class="col-lg-5" data-aos="fade-right" data-aos-delay="100">
                <div style="border-radius:16px;overflow:hidden;box-shadow:0 16px 48px rgba(0,0,0,.16);position:relative;">
                    <img src="{{ asset('images/avis-concours-2026.jpeg') }}" alt="Avis Concours IUM 2026-2027" style="width:100%;height:auto;display:block;">
                    <div style="position:absolute;top:14px;left:14px;">
                        <span style="background:var(--red);color:#fff;font-size:.72rem;font-weight:700;padding:5px 13px;border-radius:20px;letter-spacing:.6px;text-transform:uppercase;">Officiel</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-7" data-aos="fade-left" data-aos-delay="150">
                <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:24px;">
                    @foreach(['Capacité en Droit','BTS','Licence','Master'] as $cycle)
                        <span style="background:var(--blue);color:#fff;padding:6px 16px;border-radius:6px;font-size:.83rem;font-weight:700;">{{ $cycle }}</span>
                    @endforeach
                </div>
                <h4 style="font-size:.9rem;font-weight:700;color:var(--blue);margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px;">
                    <i class="fas fa-calendar-alt" style="margin-right:6px;"></i> Dates des Concours 2026
                </h4>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:24px;">
                    @foreach([['06','Juin'],['18','Juillet'],['05','Sept.'],['26','Sept.']] as $d)
                        <div style="background:#fff;border:2px solid var(--blue);border-radius:10px;padding:10px 6px;text-align:center;box-shadow:0 3px 10px rgba(30,58,138,.1);">
                            <div style="font-size:1.6rem;font-weight:800;color:var(--blue);line-height:1;">{{ $d[0] }}</div>
                            <div style="font-size:.75rem;font-weight:600;color:var(--g500);margin-top:3px;">{{ $d[1] }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="row gy-3">
                    <div class="col-md-6">
                        <div style="background:#fff;border:1px solid var(--g200);border-radius:12px;padding:16px 18px;height:100%;">
                            <p style="font-size:.82rem;font-weight:700;color:var(--blue);margin-bottom:10px;"><i class="fas fa-list-check" style="margin-right:6px;"></i> Épreuves</p>
                            @foreach(['Culture Générale','Français ou Anglais','Sciences','Physique / Chimie','Mathématiques'] as $ep)
                                <div style="display:flex;align-items:center;gap:8px;padding:4px 0;border-bottom:1px solid #f1f5f9;font-size:.83rem;color:var(--g700);">
                                    <span style="width:8px;height:8px;background:var(--red);border-radius:2px;flex-shrink:0;"></span> {{ $ep }}
                                </div>
                            @endforeach
                            <p style="font-size:.74rem;color:var(--g500);margin-top:8px;font-style:italic;">Selon la filière choisie</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="background:#fff;border:1px solid var(--g200);border-radius:12px;padding:16px 18px;height:100%;">
                            <p style="font-size:.82rem;font-weight:700;color:var(--blue);margin-bottom:10px;"><i class="fas fa-file-invoice-dollar" style="margin-right:6px;"></i> Modalités & Frais</p>
                            <div style="padding:8px 12px;background:#f0f4ff;border-radius:8px;margin-bottom:8px;">
                                <p style="font-size:.82rem;color:var(--blue);font-weight:600;margin:0;"><i class="fas fa-folder-open" style="margin-right:6px;"></i> Étude de dossier</p>
                                <p style="font-size:1.1rem;font-weight:800;color:var(--blue);margin:3px 0 0;">15 000 FCFA</p>
                            </div>
                            <div style="padding:8px 12px;background:#fff8f0;border-radius:8px;margin-bottom:8px;">
                                <p style="font-size:.82rem;color:#c05621;font-weight:600;margin:0;"><i class="fas fa-pen-to-square" style="margin-right:6px;color:#c05621;"></i> Concours écrit</p>
                                <p style="font-size:1.1rem;font-weight:800;color:#c05621;margin:3px 0 0;">10 000 FCFA</p>
                            </div>
                            <p style="font-size:.74rem;color:var(--red);font-weight:600;margin-top:6px;"><i class="fas fa-circle-exclamation" style="margin-right:4px;"></i> Les 2 modalités ne sont pas cumulables</p>
                        </div>
                    </div>
                </div>
                <div style="margin-top:20px;background:#f8fafc;border:1px solid var(--g200);border-radius:12px;padding:14px 18px;">
                    <p style="font-size:.82rem;font-weight:700;color:var(--g700);margin-bottom:8px;"><i class="fas fa-paperclip" style="color:var(--blue);margin-right:6px;"></i> Composition du dossier</p>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;">
                        @foreach(["Fiche d'inscription remplie",'Relevé de notes du Bac','Photocopie acte de naissance','Certificat médical (-3 mois)','4 photos identité 4×4','Dépôt possible en numérique'] as $doc)
                            <div style="font-size:.78rem;color:var(--g700);display:flex;align-items:flex-start;gap:5px;padding:3px 0;">
                                <i class="fas fa-star" style="color:#fbbf24;font-size:.58rem;margin-top:4px;flex-shrink:0;"></i> {{ $doc }}
                            </div>
                        @endforeach
                    </div>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:22px;">
                    <a href="{{ route('preinscription.index') }}" class="th-btn"><i class="fas fa-user-plus"></i> S'inscrire au concours</a>
                    <a href="{{ asset('images/nos-instituts-filieres.jpeg') }}" download="Filieres-IUM-2026.jpg" class="th-btn style4"><i class="fas fa-download"></i> Télécharger l'affiche</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ADMISSION INFO -->
<section class="admission-info" id="admission">
    <div class="admission-container">
        <h2>Informations d'Admission</h2>
        <div class="admission-intro">
            <p><strong style="color:#fff;">Chers étudiants, apprenants et professionnels,</strong></p>
            <p>Vous bénéficiez d'un campus High Tech, d'enseignants qualifiés et de laboratoires très bien équipés.</p>
        </div>
        <div class="admission-grid">
            <div class="admission-card"><h3><i class="fas fa-graduation-cap"></i> Conditions d'admission</h3><p><strong>BTS/HND :</strong> BAC, GCE ou équivalent</p><p><strong>Licence & Master :</strong> Diplômes requis selon niveau</p></div>
            <div class="admission-card"><h3><i class="fas fa-file-invoice-dollar"></i> Frais d'inscription</h3><p><strong>50 000 FCFA</strong></p><p>Frais uniques lors de l'inscription</p></div>
            <div class="admission-card"><h3><i class="fas fa-book"></i> Scolarité complète</h3><p><strong></strong></p><p>Payable en 3 tranches</p><p style="font-size:.82rem;margin-top:7px;color:rgba(255,255,255,.8);">Inclut : scolarité, transport, assurances, tenues et kit matériel</p></div>
            <div class="admission-card"><h3><i class="fas fa-building"></i> Cité Universitaire</h3><p><strong>50 000 FCFA / mois</strong></p><p>10 mois d'avance + 2 mois de caution remboursables</p></div>
        </div>
    </div>
</section>


<!-- POURQUOI L'IUM — stats + valeurs -->
<section class="space" style="background:#fff;" data-aos="fade-up" data-aos-duration="800" id="about">
    <div class="container">
        <div class="text-center" style="margin-bottom:44px;">
            <span class="sub-title">Notre engagement</span>
            <h2 class="sec-title" style="text-align:center;">Pourquoi choisir l'IUM ?</h2>
            <p style="color:var(--g500);font-size:.92rem;max-width:680px;margin:10px auto 0;">
                Un campus High Tech, des enseignants qualifiés, des laboratoires équipés et une formation reconnue par l'État du Cameroun.
            </p>
        </div>
        <div class="row gy-4">
            {{-- Stats --}}
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="80">
                <div style="text-align:center;padding:24px 16px;border-radius:12px;background:var(--g100);border:1px solid var(--g200);">
                    <div style="font-size:2.4rem;font-weight:800;color:var(--blue);line-height:1;">3</div>
                    <div style="font-size:.8rem;font-weight:600;color:var(--g700);margin-top:6px;">Instituts spécialisés</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="120">
                <div style="text-align:center;padding:24px 16px;border-radius:12px;background:var(--g100);border:1px solid var(--g200);">
                    <div style="font-size:2.4rem;font-weight:800;color:var(--blue);line-height:1;">30+</div>
                    <div style="font-size:.8rem;font-weight:600;color:var(--g700);margin-top:6px;">Filières & spécialités</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="160">
                <div style="text-align:center;padding:24px 16px;border-radius:12px;background:var(--g100);border:1px solid var(--g200);">
                    <div style="font-size:2.4rem;font-weight:800;color:var(--blue);line-height:1;">BTS→M</div>
                    <div style="font-size:.8rem;font-weight:600;color:var(--g700);margin-top:6px;">BTS, Licence & Master</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div style="text-align:center;padding:24px 16px;border-radius:12px;background:var(--g100);border:1px solid var(--g200);">
                    <div style="font-size:2.4rem;font-weight:800;color:var(--blue);line-height:1;">100%</div>
                    <div style="font-size:.8rem;font-weight:600;color:var(--g700);margin-top:6px;">Agréé MINESUP</div>
                </div>
            </div>
            {{-- Avantages --}}
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div style="padding:22px;border-radius:12px;border:1px solid var(--g200);height:100%;">
                    <div style="width:44px;height:44px;border-radius:10px;background:var(--blue);display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                        <i class="fas fa-flask" style="color:#fff!important;font-size:1.1rem;"></i>
                    </div>
                    <h4 style="font-size:.95rem;font-weight:700;color:var(--g900);margin-bottom:8px;">Laboratoires & Ateliers</h4>
                    <p style="font-size:.84rem;color:var(--g500);margin:0;">Équipements professionnels dans chaque filière : salles informatiques, laboratoires biomédicaux, ateliers mécaniques et cuisines pédagogiques.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="150">
                <div style="padding:22px;border-radius:12px;border:1px solid var(--g200);height:100%;">
                    <div style="width:44px;height:44px;border-radius:10px;background:var(--blue);display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                        <i class="fas fa-bus" style="color:#fff!important;font-size:1.1rem;"></i>
                    </div>
                    <h4 style="font-size:.95rem;font-weight:700;color:var(--g900);margin-bottom:8px;">Scolarité tout inclus</h4>
                    <p style="font-size:.84rem;color:var(--g500);margin:0;">Pension, transport, assurance, uniforme, kit matériel professionnel et restaurant universitaire.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div style="padding:22px;border-radius:12px;border:1px solid var(--g200);height:100%;">
                    <div style="width:44px;height:44px;border-radius:10px;background:var(--blue);display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                        <i class="fas fa-user-tie" style="color:#fff!important;font-size:1.1rem;"></i>
                    </div>
                    <h4 style="font-size:.95rem;font-weight:700;color:var(--g900);margin-bottom:8px;">Coordinateurs qualifiés</h4>
                    <p style="font-size:.84rem;color:var(--g500);margin:0;">Chaque filière est encadrée par un coordinateur expert : Dr Arlette BUGUE (Droit), Mme NOA Joséphine (THR), KEDE MELONO (Gestion).</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- NOS INSTITUTS & FILIÈRES (basé sur l'affiche) -->
<section class="space" id="instituts" data-aos="fade-up" data-aos-duration="800">
    <div class="container">
        <div class="text-center" style="margin-bottom:48px;">
            <span class="sub-title">3 instituts spécialisés</span>
            <h2 class="sec-title" style="text-align:center;">Nos Instituts & Filières</h2>
            <p style="color:var(--g500);font-size:.92rem;max-width:700px;margin:12px auto 0;">
                L'IUM regroupe trois instituts d'excellence pour une formation complète, du BTS au Master.
            </p>
        </div>

        <div class="row gy-5">

            {{-- ── ISGMM ── --}}
            <div class="col-12" data-aos="fade-up" data-aos-delay="80">
                <div style="border-radius:16px;overflow:hidden;box-shadow:var(--sh-md);border:1px solid var(--g200);">
                    <div class="inst-header" style="background:linear-gradient(135deg,#1e3a8a,#2f54a4);">
                        <div class="inst-icon" style="background:rgba(255,255,255,.15);"><i class="fas fa-briefcase"></i></div>
                        <div>
                            <h3 class="inst-title">Institut Supérieur de Gestion et de Management (ISGMM)</h3>
                            <p class="inst-subtitle">Épreuves : Culture Générale · Français ou Anglais · Mathématiques</p>
                        </div>
                    </div>
                    <div style="padding:24px;">
                        <div class="row gy-4">
                            <div class="col-md-3">
                                <div class="filiere-col">
                                    <h5><i class="fas fa-chart-line" style="color:var(--blue);margin-right:5px;"></i> Filière Gestion</h5>
                                    <ul>
                                        <li>Assurance</li>
                                        <li>Gestion des CTD</li>
                                        <li>Banque & Finance</li>
                                        <li>Gestion de la Qualité</li>
                                        <li>Comptabilité & Audit</li>
                                        <li>Transport & Logistique</li>
                                        <li>Gestion des Ressources Humaines</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="filiere-col">
                                    <h5><i class="fas fa-store" style="color:var(--blue);margin-right:5px;"></i> Commerce et Vente</h5>
                                    <ul>
                                        <li>Marketing, Commerce et Vente</li>
                                        <li>Commerce International</li>
                                    </ul>
                                    <h5 style="margin-top:14px;"><i class="fas fa-utensils" style="color:var(--blue);margin-right:5px;"></i> Tourisme / Hôtellerie</h5>
                                    <ul>
                                        <li>Tourisme et Gestion Hôtelière</li>
                                        <li>Restauration — Génie Culinaire</li>
                                        <li>Industrie Habillement & Textiles</li>
                                        <li>Management & hébergement</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="filiere-col">
                                    <h5><i class="fas fa-scale-balanced" style="color:var(--blue);margin-right:5px;"></i> Carrières Juridiques</h5>
                                    <ul>
                                        <li>Droit des Affaires</li>
                                        <li>Douane et Transit</li>
                                        <li>Droit Foncier et Domanial</li>
                                        <li>Droit Public Interne et International</li>
                                        <li>Sciences Politiques</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-3" style="display:flex;align-items:center;justify-content:center;">
                                <div style="text-align:center;">
                                    <a href="{{ route('preinscription.index') }}" class="th-btn" style="font-size:.82rem;padding:9px 18px;margin-bottom:10px;width:100%;justify-content:center;">
                                        <i class="fas fa-user-plus"></i> S'inscrire
                                    </a>
                                    <a href="#formations" class="th-btn style4" style="font-size:.82rem;padding:9px 18px;width:100%;justify-content:center;">
                                        <i class="fas fa-eye"></i> Voir formations
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── ISTIM ── --}}
            <div class="col-12" data-aos="fade-up" data-aos-delay="120">
                <div style="border-radius:16px;overflow:hidden;box-shadow:var(--sh-md);border:1px solid var(--g200);">
                    <div class="inst-header" style="background:linear-gradient(135deg,#b45309,#d97706);">
                        <div class="inst-icon" style="background:rgba(255,255,255,.15);"><i class="fas fa-cogs"></i></div>
                        <div>
                            <h3 class="inst-title">Institut Supérieur de Technologie et d'Ingénierie (ISTIM)</h3>
                            <p class="inst-subtitle">Épreuves : Culture Générale · Français ou Anglais · Mathématiques · Physique-Chimie</p>
                        </div>
                    </div>
                    <div style="padding:24px;">
                        <div class="row gy-4">
                            <div class="col-md-4">
                                <div class="filiere-col">
                                    <h5><i class="fas fa-seedling" style="color:var(--blue);margin-right:5px;"></i> Agriculture & Élevage</h5>
                                    <ul>
                                        <li>Productions Végétales</li>
                                        <li>Productions Animales & Élevage</li>
                                        <li>Agro-industrie</li>
                                        <li>Gestion des Ressources Naturelles</li>
                                    </ul>
                                    <h5 style="margin-top:14px;"><i class="fas fa-bolt" style="color:var(--blue);margin-right:5px;"></i> Génie Électrique</h5>
                                    <ul>
                                        <li>Électrotechnique</li>
                                        <li>Maintenance systèmes électroniques</li>
                                        <li>Énergies Renouvelables</li>
                                        <li>Maintenance appareils biomédicaux</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="filiere-col">
                                    <h5><i class="fas fa-laptop-code" style="color:var(--blue);margin-right:5px;"></i> Génie Informatique</h5>
                                    <ul>
                                        <li>Génie Logiciel</li>
                                        <li>Maintenance systèmes informatiques</li>
                                        <li>Informatique industrielle & automatisme</li>
                                        <li>Commerce et marketing numériques</li>
                                    </ul>
                                    <h5 style="margin-top:14px;"><i class="fas fa-network-wired" style="color:var(--blue);margin-right:5px;"></i> Réseaux / Télécoms</h5>
                                    <ul>
                                        <li>Réseaux et sécurité</li>
                                        <li>Télécommunications</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="filiere-col">
                                    <h5><i class="fas fa-industry" style="color:var(--blue);margin-right:5px;"></i> Génie Mécanique & Productique</h5>
                                    <ul>
                                        <li>Construction métallique</li>
                                        <li>Chaudronnerie & soudure mécanique</li>
                                        <li>Productique & Automatisme</li>
                                        <li>Maintenance systèmes industriels</li>
                                        <li>Technologies marine marchande</li>
                                    </ul>
                                    <h5 style="margin-top:14px;"><i class="fas fa-hard-hat" style="color:var(--blue);margin-right:5px;"></i> Génie Civil</h5>
                                    <ul>
                                        <li>Bâtiment & Travaux Publics</li>
                                        <li>Topographie & Géomètre</li>
                                        <li>Architecture & Urbanisme</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top:16px;display:flex;justify-content:flex-end;">
                            <a href="{{ route('preinscription.index') }}" class="th-btn" style="font-size:.82rem;padding:9px 18px;margin-right:10px;">
                                <i class="fas fa-user-plus"></i> S'inscrire
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── ISSBM ── --}}
            <div class="col-12" data-aos="fade-up" data-aos-delay="160">
                <div style="border-radius:16px;overflow:hidden;box-shadow:var(--sh-md);border:1px solid var(--g200);">
                    <div class="inst-header" style="background:linear-gradient(135deg,#166534,#15803d);">
                        <div class="inst-icon" style="background:rgba(255,255,255,.15);"><i class="fas fa-heartbeat"></i></div>
                        <div>
                            <h3 class="inst-title">Institut Supérieur des Sciences Biomédicales (ISSBM)</h3>
                            <p class="inst-subtitle">Épreuves : Culture Générale · Français ou Anglais · Sciences</p>
                        </div>
                    </div>
                    <div style="padding:24px;">
                        <div class="row gy-4">
                            <div class="col-md-4">
                                <div class="filiere-col">
                                    <h5><i class="fas fa-stethoscope" style="color:var(--blue);margin-right:5px;"></i> Études Médico-Sanitaires</h5>
                                    <ul>
                                        <li>Sciences Infirmières</li>
                                        <li>Sage-femme & Maïeutique</li>
                                        <li>Santé Communautaire</li>
                                        <li>Kinésithérapie & Rééducation</li>
                                        <li>Nutrition & Diététique</li>
                                        <li>Odontostomatologie</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="filiere-col">
                                    <h5><i class="fas fa-microscope" style="color:var(--blue);margin-right:5px;"></i> Sciences Techniques & Biomédicales</h5>
                                    <ul>
                                        <li>Analyses Biomédicales & Biologiques</li>
                                        <li>Pharmacie</li>
                                        <li>Imagerie Médicale & Radiologie</li>
                                        <li>Génie Biomédical</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-4" style="display:flex;align-items:center;justify-content:center;">
                                <div style="text-align:center;">
                                    <a href="{{ route('preinscription.index') }}" class="th-btn" style="font-size:.82rem;padding:9px 18px;margin-bottom:10px;width:100%;justify-content:center;">
                                        <i class="fas fa-user-plus"></i> S'inscrire
                                    </a>
                                    <a href="{{ asset('images/nos-instituts-filieres.jpeg') }}" download="Instituts-Filieres-IUM.jpg" class="th-btn style4" style="font-size:.82rem;padding:9px 18px;width:100%;justify-content:center;">
                                        <i class="fas fa-download"></i> Télécharger l'affiche
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- FILIÈRES (dépliant + liste) -->
<div class="video-area-1" style="background:var(--g100);" data-aos="fade-up" data-aos-duration="800">
    <div class="container">
        <div class="row gy-4 align-items-center">
            <div class="col-lg-7 order-lg-2">
                <img src="{{ asset('images/nos-instituts-filieres.jpeg') }}" alt="Nos Instituts & Filières IUM" style="border-radius:var(--r-lg);box-shadow:var(--sh-lg);width:100%;object-fit:cover;">
            </div>
            <div class="col-lg-5 order-lg-1">
                <h2 class="sec-title">Nos Filières de Formation</h2>
                <p>L'IUM regroupe 3 instituts spécialisés pour vous offrir une formation de qualité reconnue par l'État du Cameroun.</p>
                <div class="row gy-3 mt-3">
                    <div class="col-md-6">
                        <h4 style="color:var(--blue);font-weight:700;font-size:.88rem;margin-bottom:7px;display:flex;align-items:center;gap:6px;">
                            <i class="fas fa-briefcase" style="color:var(--blue);"></i> ISGMM
                        </h4>
                        <div class="checklist">
                            <ul>
                                <li>Gestion & Management</li>
                                <li>Commerce et Vente</li>
                                <li>Tourisme & Hôtellerie</li>
                                <li>Carrières Juridiques</li>
                            </ul>
                        </div>
                        <h4 style="color:var(--blue);font-weight:700;font-size:.88rem;margin:12px 0 7px;display:flex;align-items:center;gap:6px;">
                            <i class="fas fa-cogs" style="color:var(--blue);"></i> ISTIM
                        </h4>
                        <div class="checklist">
                            <ul>
                                <li>Génie Informatique</li>
                                <li>Génie Électrique</li>
                                <li>Génie Mécanique & Productique</li>
                                <li>Génie Civil & Architecture</li>
                                <li class="more-content" style="display:none;">Agriculture & Élevage</li>
                                <li class="more-content" style="display:none;">Réseaux & Télécoms</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4 style="color:var(--blue);font-weight:700;font-size:.88rem;margin-bottom:7px;display:flex;align-items:center;gap:6px;">
                            <i class="fas fa-heartbeat" style="color:var(--blue);"></i> ISSBM
                        </h4>
                        <div class="checklist">
                            <ul>
                                <li>Sciences Infirmières</li>
                                <li>Sage-femme & Maïeutique</li>
                                <li>Kinésithérapie & Rééducation</li>
                                <li>Imagerie Médicale & Radiologie</li>
                                <li class="more-content" style="display:none;">Analyses Biomédicales</li>
                                <li class="more-content" style="display:none;">Pharmacie</li>
                                <li class="more-content" style="display:none;">Génie Biomédical</li>
                                <li class="more-content" style="display:none;">Nutrition & Diététique</li>
                                <li class="more-content" style="display:none;">Santé Communautaire</li>
                                <li class="more-content" style="display:none;">Odontostomatologie</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <button id="toggleMore" onclick="toggleContent()">
                        <i class="fa fa-plus-circle"></i> Voir plus de filières
                    </button>
                </div>
                <div class="text-center mt-3">
                    <em style="color:var(--blue);font-size:1.1rem;font-weight:600;">Notre Challenge, Votre Avenir</em>
                </div>
                <div class="mt-4">
                    <a class="th-btn" href="{{ route('preinscription.index') }}">S'inscrire maintenant <i class="fa fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FORMATIONS CARDS -->
<section class="space bg-smoke" id="formations" data-aos="fade-up" data-aos-duration="800">
    <div class="container">
        <div class="text-center" style="margin-bottom:40px;">
            <span class="sub-title">Nos programmes</span>
            <h2 class="sec-title" style="text-align:center;">Excellence dans la formation professionnelle</h2>
        </div>

        @php
        $isgmm = [
            ['img'=>'gestion.jpg',
             'title'=>'Gestion','slug'=>'gestion','inst'=>'ISGMM','color'=>'#1e3a8a',
             'desc'=>'GRH, comptabilite, banque & finance, marketing, logistique et journalisme — du BTS au Master professionnel.',
             'bts'=>['GRH','Comptabilite (CGE)','Marketing (MCV)','Banque & Finance','Logistique (GLT)','Communication','Journalisme'],
             'debouches'=>['Responsable RH','Analyste financier','Logisticien','Charge de communication','Journaliste'],
             'coord'=>'KEDE MELONO EUGENE BERTRAND — 695 494 299'],
            ['img'=>'vente.jpg',
             'title'=>'Commerce et Vente','slug'=>'commerce-et-vente','inst'=>'ISGMM','color'=>'#1e3a8a',
             'desc'=>'Marketing, commerce international, techniques de vente, merchandising et e-commerce.',
             'bts'=>['Marketing Commerce Vente (MCV)','Commerce International','Assistant Manager','E-Commerce & Marketing Numerique'],
             'debouches'=>['Commercial','Merchandiser','Business Developer','Responsable e-commerce','Attache commercial'],
             'coord'=>'KEDE MELONO EUGENE BERTRAND — 695 494 299'],
            ['img'=>'hotellerie.jpg',
             'title'=>'Tourisme & Hotellerie','slug'=>'tourisme-et-hotellerie','inst'=>'ISGMM','color'=>'#1e3a8a',
             'desc'=>'Management touristique, gestion hoteliere, genie culinaire et industrie de l\'habillement & design de mode.',
             'bts'=>['Management Touristique','Gestion Hoteliere','Genie Culinaire','Industrie Habillement & Design','Commercialisation Restaurant'],
             'debouches'=>['Guide de tourisme','Receptionniste hotelier','Maitre cuisinier','Styliste-modeliste','Gouvernant(e)'],
             'coord'=>'Mme NOA Josephine — 658 877 250'],
            ['img'=>'social.jpg',
             'title'=>'Carrieres Juridiques','slug'=>'carrieres-juridiques','inst'=>'ISGMM','color'=>'#1e3a8a',
             'desc'=>'Droit des affaires, droit public, sciences politiques et capacite en droit — accessible des le BEPC.',
             'bts'=>['BTS Droit des Affaires','BTS Assistant Judiciaire','Capacite en Droit (des le BEPC)','L1 a L3 Droit des Affaires','L1 a L3 Sciences Politiques'],
             'debouches'=>['Avocat / Notaire','Juriste d\'entreprise','Magistrat','Fonctionnaire diplomatique','Greffier'],
             'coord'=>'Dr Arlette BUGUE MAYOUGOUNG — 696 144 712'],
        ];
        $istim = [
            ['img'=>'informatique.jpg',
             'title'=>'Genie Informatique','slug'=>'genie-informatique','inst'=>'ISTIM','color'=>'#b45309',
             'desc'=>'Genie logiciel, maintenance systemes, informatique industrielle, infographie et e-commerce — BTS, Licence ISIR et Master.',
             'bts'=>['Genie Logiciel','Maintenance Systemes Informatiques','Informatique Industrielle & Automatisme','Infographie & Web Design','E-Commerce & Marketing Numerique'],
             'debouches'=>['Developpeur web/mobile','Administrateur systemes','DSI / Responsable IT','Webdesigner / Infographiste'],
             'coord'=>'+237 6 55 34 19 39'],
            ['img'=>'electrique.jpg',
             'title'=>'Genie Electrique','slug'=>'genie-electrique','inst'=>'ISTIM','color'=>'#b45309',
             'desc'=>'Electrotechnique, maintenance electronique, energies renouvelables et maintenance d\'appareils biomedicaux.',
             'bts'=>['Electrotechnique','Maintenance Systemes Electroniques','Energies Renouvelables','Maintenance Appareils Biomedicaux'],
             'debouches'=>['Electrotechnicien','Technicien energies renouvelables','Ingenieur electricien','Maintenancier systemes'],
             'coord'=>'+237 6 55 34 19 39'],
            ['img'=>'productique.jpg',
             'title'=>'Genie Mecanique & Productique','slug'=>'genie-mecanique-et-productique','inst'=>'ISTIM','color'=>'#b45309',
             'desc'=>'Construction metallique, chaudronnerie, soudure mecanique, productique, automatisme et technologies marines marchandes.',
             'bts'=>['Construction Metallique','Chaudronnerie & Soudure','Productique & Automatisme','Maintenance Systemes Industriels','Technologies Marine Marchande'],
             'debouches'=>['Technicien de production','Soudeur-chaudronnier','Maintenancier industriel','Mecanicien de marine'],
             'coord'=>'+237 6 55 34 19 39'],
            ['img'=>'civil.jpg',
             'title'=>'Genie Civil','slug'=>'genie-civil','inst'=>'ISTIM','color'=>'#b45309',
             'desc'=>'Batiment & Travaux Publics, topographie, geometre expert, architecture & urbanisme — du BTS au Master.',
             'bts'=>['Batiment & Travaux Publics','Topographie & Geometre','Architecture & Urbanisme'],
             'debouches'=>['Conducteur de travaux','Topographe / Geometre','Architecte','Ingenieur BTP','Urbaniste'],
             'coord'=>'+237 6 55 34 19 39'],
            ['img'=>'elevage.jpg',
             'title'=>'Agriculture et Elevage','slug'=>'agriculture-et-elevage','inst'=>'ISTIM','color'=>'#b45309',
             'desc'=>'Productions vegetales, productions animales, agro-industrie et gestion des ressources naturelles.',
             'bts'=>['Productions Vegetales','Productions Animales & Elevage','Agro-industrie','Gestion des Ressources Naturelles'],
             'debouches'=>['Agronome','Manager agricole','Agro-industriel','Gestionnaire ressources naturelles'],
             'coord'=>'+237 6 55 34 19 39'],
            ['img'=>'admission_1_2.jpg',
             'title'=>'Reseaux & Telecommunications','slug'=>'reseaux-et-telecommunications','inst'=>'ISTIM','color'=>'#b45309',
             'desc'=>'Reseaux & securite, telecommunications, administration systeme et ingenierie Telecoms — BTS au Master.',
             'bts'=>['Telecoms Reseaux & Securite','Administration Systeme & Reseaux','Ingenierie Telecoms & Reseaux Mobiles','Conception & Developpement Reseaux'],
             'debouches'=>['Admin reseaux & securite','Ingenieur Telecoms','Operateurs (Orange, MTN, Camtel)','Consultant systemes'],
             'coord'=>'+237 6 55 34 19 39'],
        ];
        $issbm = [
            ['img'=>'infirmieres.jpg',
             'title'=>'Sciences Infirmieres','slug'=>'sciences-infirmieres','inst'=>'ISSBM','color'=>'#15803d',
             'desc'=>'Formation complete en soins infirmiers, sante communautaire et assistance medicale — BTS, Licence et Master.',
             'bts'=>['BTS Sciences Infirmieres','Licence Sciences Infirmieres','Master Sciences Infirmieres'],
             'debouches'=>['Infirmier(e) diplome(e)','Cadre infirmier','Infirmier en sante publique','Chef de service soins'],
             'coord'=>'+237 6 55 34 19 39'],
            ['img'=>'maieuticien.jpg',
             'title'=>'Sage-Femme / Maieuticien','slug'=>'sage-femme-maieuticien','inst'=>'ISSBM','color'=>'#15803d',
             'desc'=>'Soins prenatals, accouchement assiste, sante maternelle et neonatale — formation agreee MINESUP.',
             'bts'=>['BTS Sage-femme & Maieutique','Licence Maieutique','Master Maieutique'],
             'debouches'=>['Sage-femme diplomee','Maieuticien(ne)','Cadre sage-femme','Responsable maternite'],
             'coord'=>'+237 6 55 34 19 39'],
            ['img'=>'kinesitherapie.jpg',
             'title'=>'Kinesitherapie','slug'=>'kinesitherapie','inst'=>'ISSBM','color'=>'#15803d',
             'desc'=>'Reeducation physique et fonctionnelle, massage therapeutique, rehabilitation motrice et sport-sante.',
             'bts'=>['BTS Kinesitherapie & Reeducation','Licence Kinesitherapie','Master Kinesitherapie'],
             'debouches'=>['Kinesitherapeute diplome(e)','Reeducateur fonctionnel','Masseur-kinesitherapeute','Therapeute du sport'],
             'coord'=>'+237 6 55 34 19 39'],
            ['img'=>'medicale.jpg',
             'title'=>'Radiologie & Imagerie Medicale','slug'=>'radiologie-et-imagerie-medicale','inst'=>'ISSBM','color'=>'#15803d',
             'desc'=>'Radiographie, scanner, IRM et echographie — techniques avancees d\'imagerie medicale agreees MINESUP.',
             'bts'=>['BTS Radiologie & Imagerie Medicale','Licence Imagerie Medicale','Master Imagerie & Radiologie'],
             'debouches'=>['Technicien en radiologie','Manipulateur imagerie medicale','Technicien IRM/Scanner','Responsable imagerie'],
             'coord'=>'+237 6 55 34 19 39'],
            ['img'=>'biomedicales.jpg',
             'title'=>'Sciences Techniques Biomedicales','slug'=>'sciences-et-techniques-biomedicales','inst'=>'ISSBM','color'=>'#15803d',
             'desc'=>'Analyses biomedicales & biologiques, pharmacie, genie biomedical et maintenance d\'equipements medicaux.',
             'bts'=>['BTS Analyses Biomedicales','BTS Pharmacie','BTS Genie Biomedical','Licence Biomedicale','Master Sciences Biomedicales'],
             'debouches'=>['Biologiste medical','Pharmacien','Ingenieur biomedical','Technicien de laboratoire'],
             'coord'=>'+237 6 55 34 19 39'],
            ['img'=>'sanitaire.jpg',
             'title'=>'Etude Medico-Sanitaire','slug'=>'etude-medico-sanitaire','inst'=>'ISSBM','color'=>'#15803d',
             'desc'=>'Sante communautaire, nutrition & dietetique, odontostomatologie et epidemiologie — sante publique de terrain.',
             'bts'=>['BTS Sante Communautaire','BTS Nutrition & Dietetique','BTS Odontostomatologie','Licence Sante Publique','Master Medico-Sanitaire'],
             'debouches'=>['Agent de sante communautaire','Dieteticien(ne)','Epidemiologiste','Aide-soignant specialise'],
             'coord'=>'+237 6 55 34 19 39'],
        ];
        @endphp

        {{-- ISGMM --}}
        <div class="mt-40" data-aos="fade-up" data-aos-delay="80">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
                <div style="width:36px;height:36px;border-radius:8px;background:#1e3a8a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-briefcase" style="color:white!important;font-size:.9rem;"></i>
                </div>
                <div>
                    <h3 style="font-size:1.1rem;font-weight:700;color:#1e3a8a;margin:0;">ISGMM — Institut Supérieur de Gestion et de Management</h3>
                    <p style="font-size:.78rem;color:var(--g500);margin:0;">Gestion · Commerce · Tourisme · Droit</p>
                </div>
            </div>
            <div class="row gy-4">
                @foreach($isgmm as $c)
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="admission-card" style="position:relative;">
                        <div class="admission-card_img">
                            <img src="{{ asset('asset_vitrine/assets/img/update1/normal/'.$c['img']) }}" alt="{{ $c['title'] }}"
                                 style="width:100%;height:175px;object-fit:cover;">
                        </div>
                        <div class="admission-card_content">
                            <span style="background:{{ $c['color'] }};color:#fff;font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:4px;display:inline-block;margin-bottom:4px;">{{ $c['inst'] }}</span>
                            <h3 class="admission-card_title">{{ $c['title'] }}</h3>
                            <p class="admission-card_text">{{ $c['desc'] }}</p>
                            {{-- Programmes --}}
                            <div style="margin:4px 0 6px;">
                                @foreach(array_slice($c['bts'],0,3) as $prog)
                                <span style="display:inline-block;background:#f1f5f9;color:#334155;font-size:.66rem;padding:1px 7px;border-radius:4px;margin:1px 2px 1px 0;">{{ $prog }}</span>
                                @endforeach
                                @if(count($c['bts']) > 3)
                                <span style="display:inline-block;background:#e2e8f0;color:#64748b;font-size:.66rem;padding:1px 7px;border-radius:4px;margin:1px 0;">+{{ count($c['bts'])-3 }} autres</span>
                                @endif
                            </div>
                            {{-- Débouchés --}}
                            <div style="border-top:1px solid #f1f5f9;padding-top:6px;margin-top:4px;">
                                <p style="font-size:.68rem;font-weight:700;color:#64748b;margin:0 0 4px;text-transform:uppercase;letter-spacing:.5px;"><i class="fas fa-briefcase" style="color:{{ $c['color'] }};margin-right:3px;font-size:.62rem;"></i> Débouchés</p>
                                @foreach(array_slice($c['debouches'],0,2) as $deb)
                                <div style="font-size:.72rem;color:#334155;display:flex;align-items:center;gap:4px;padding:1px 0;">
                                    <span style="width:5px;height:5px;border-radius:50%;background:{{ $c['color'] }};flex-shrink:0;"></span> {{ $deb }}
                                </div>
                                @endforeach
                            </div>
                            <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">
                                <a href="#" class="link-btn register-btn" data-filiere="{{ $c['title'] }}" style="font-size:.78rem;">S'inscrire <i class="fas fa-arrow-right"></i></a>
                                <a href="{{ route('formation.show', $c['slug']) }}" class="btn-voir-plus" style="padding:6px 10px;font-size:.78rem;"><i class="fas fa-eye"></i> Voir plus</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ISTIM --}}
        <div class="mt-40" data-aos="fade-up" data-aos-delay="100">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
                <div style="width:36px;height:36px;border-radius:8px;background:#b45309;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-cogs" style="color:white!important;font-size:.9rem;"></i>
                </div>
                <div>
                    <h3 style="font-size:1.1rem;font-weight:700;color:#b45309;margin:0;">ISTIM — Institut Supérieur de Technologie et d'Ingénierie</h3>
                    <p style="font-size:.78rem;color:var(--g500);margin:0;">Informatique · Électrique · Mécanique · Génie Civil · Agriculture · Réseaux</p>
                </div>
            </div>
            <div class="row gy-4">
                @foreach($istim as $c)
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="admission-card" style="position:relative;">
                        <div class="admission-card_img">
                            <img src="{{ asset('asset_vitrine/assets/img/update1/normal/'.$c['img']) }}" alt="{{ $c['title'] }}"
                                 style="width:100%;height:175px;object-fit:cover;">
                        </div>
                        <div class="admission-card_content">
                            <span style="background:{{ $c['color'] }};color:#fff;font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:4px;display:inline-block;margin-bottom:4px;">{{ $c['inst'] }}</span>
                            <h3 class="admission-card_title">{{ $c['title'] }}</h3>
                            <p class="admission-card_text">{{ $c['desc'] }}</p>
                            {{-- Programmes --}}
                            <div style="margin:4px 0 6px;">
                                @foreach(array_slice($c['bts'],0,3) as $prog)
                                <span style="display:inline-block;background:#f1f5f9;color:#334155;font-size:.66rem;padding:1px 7px;border-radius:4px;margin:1px 2px 1px 0;">{{ $prog }}</span>
                                @endforeach
                                @if(count($c['bts']) > 3)
                                <span style="display:inline-block;background:#e2e8f0;color:#64748b;font-size:.66rem;padding:1px 7px;border-radius:4px;margin:1px 0;">+{{ count($c['bts'])-3 }} autres</span>
                                @endif
                            </div>
                            {{-- Débouchés --}}
                            <div style="border-top:1px solid #f1f5f9;padding-top:6px;margin-top:4px;">
                                <p style="font-size:.68rem;font-weight:700;color:#64748b;margin:0 0 4px;text-transform:uppercase;letter-spacing:.5px;"><i class="fas fa-briefcase" style="color:{{ $c['color'] }};margin-right:3px;font-size:.62rem;"></i> Débouchés</p>
                                @foreach(array_slice($c['debouches'],0,2) as $deb)
                                <div style="font-size:.72rem;color:#334155;display:flex;align-items:center;gap:4px;padding:1px 0;">
                                    <span style="width:5px;height:5px;border-radius:50%;background:{{ $c['color'] }};flex-shrink:0;"></span> {{ $deb }}
                                </div>
                                @endforeach
                            </div>
                            <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">
                                <a href="#" class="link-btn register-btn" data-filiere="{{ $c['title'] }}" style="font-size:.78rem;">S'inscrire <i class="fas fa-arrow-right"></i></a>
                                <a href="{{ route('formation.show', $c['slug']) }}" class="btn-voir-plus" style="padding:6px 10px;font-size:.78rem;"><i class="fas fa-eye"></i> Voir plus</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ISSBM --}}
        <div class="mt-40" data-aos="fade-up" data-aos-delay="120">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
                <div style="width:36px;height:36px;border-radius:8px;background:#15803d;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-heartbeat" style="color:white!important;font-size:.9rem;"></i>
                </div>
                <div>
                    <h3 style="font-size:1.1rem;font-weight:700;color:#15803d;margin:0;">ISSBM — Institut Supérieur des Sciences Biomédicales</h3>
                    <p style="font-size:.78rem;color:var(--g500);margin:0;">Soins infirmiers · Maïeutique · Kinésithérapie · Radiologie · Pharmacie</p>
                </div>
            </div>
            <div class="row gy-4">
                @foreach($issbm as $c)
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="admission-card" style="position:relative;">
                        <div class="admission-card_img">
                            <img src="{{ asset('asset_vitrine/assets/img/update1/normal/'.$c['img']) }}" alt="{{ $c['title'] }}"
                                 style="width:100%;height:175px;object-fit:cover;">
                        </div>
                        <div class="admission-card_content">
                            <span style="background:{{ $c['color'] }};color:#fff;font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:4px;display:inline-block;margin-bottom:4px;">{{ $c['inst'] }}</span>
                            <h3 class="admission-card_title">{{ $c['title'] }}</h3>
                            <p class="admission-card_text">{{ $c['desc'] }}</p>
                            {{-- Programmes --}}
                            <div style="margin:4px 0 6px;">
                                @foreach(array_slice($c['bts'],0,3) as $prog)
                                <span style="display:inline-block;background:#f1f5f9;color:#334155;font-size:.66rem;padding:1px 7px;border-radius:4px;margin:1px 2px 1px 0;">{{ $prog }}</span>
                                @endforeach
                                @if(count($c['bts']) > 3)
                                <span style="display:inline-block;background:#e2e8f0;color:#64748b;font-size:.66rem;padding:1px 7px;border-radius:4px;margin:1px 0;">+{{ count($c['bts'])-3 }} autres</span>
                                @endif
                            </div>
                            {{-- Débouchés --}}
                            <div style="border-top:1px solid #f1f5f9;padding-top:6px;margin-top:4px;">
                                <p style="font-size:.68rem;font-weight:700;color:#64748b;margin:0 0 4px;text-transform:uppercase;letter-spacing:.5px;"><i class="fas fa-briefcase" style="color:{{ $c['color'] }};margin-right:3px;font-size:.62rem;"></i> Débouchés</p>
                                @foreach(array_slice($c['debouches'],0,2) as $deb)
                                <div style="font-size:.72rem;color:#334155;display:flex;align-items:center;gap:4px;padding:1px 0;">
                                    <span style="width:5px;height:5px;border-radius:50%;background:{{ $c['color'] }};flex-shrink:0;"></span> {{ $deb }}
                                </div>
                                @endforeach
                            </div>
                            <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">
                                <a href="#" class="link-btn register-btn" data-filiere="{{ $c['title'] }}" style="font-size:.78rem;">S'inscrire <i class="fas fa-arrow-right"></i></a>
                                <a href="{{ route('formation.show', $c['slug']) }}" class="btn-voir-plus" style="padding:6px 10px;font-size:.78rem;"><i class="fas fa-eye"></i> Voir plus</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</section>

<!-- MODAL INSCRIPTION -->
<div id="registerModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" id="closeModal" type="button">&#10005;</button>
        <h3>Inscription à une formation</h3>
        <form id="registerForm">
            <div style="margin-bottom:14px;"><div class="modal-field"><label for="filiere">Filière choisie</label><input type="text" id="filiere" name="filiere" readonly></div></div>
            <div class="modal-row">
                <div class="modal-field"><label for="nom">Nom *</label><input type="text" id="nom" name="nom" placeholder="Votre nom" required></div>
                <div class="modal-field"><label for="prenom">Prénom *</label><input type="text" id="prenom" name="prenom" placeholder="Votre prénom" required></div>
            </div>
            <div class="modal-row">
                <div class="modal-field"><label for="contact">Téléphone *</label><input type="tel" id="contact" name="contact" placeholder="+237 6XX XXX XXX" required></div>
                <div class="modal-field"><label for="email">E-mail *</label><input type="email" id="email" name="email" placeholder="email@exemple.com" required></div>
            </div>
            <div class="modal-row">
                <div class="modal-field"><label for="pays">Pays *</label><input type="text" id="pays" name="pays" placeholder="Votre pays" required></div>
                <div class="modal-field">
                    <label for="typeFormation">Mode *</label>
                    <select id="typeFormation" name="typeFormation" required>
                        <option value="" disabled selected>Choisissez…</option>
                        <option value="presentiel">Présentiel</option>
                        <option value="en_ligne">En ligne</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="th-btn" style="width:100%;justify-content:center;margin-top:8px;">
                Soumettre ma candidature <i class="fas fa-arrow-right"></i>
            </button>
        </form>
        <p id="formMessage" style="color:var(--red);display:none;text-align:center;margin-top:10px;font-size:.84rem;"></p>
    </div>
</div>


<!-- GUIDES À TÉLÉCHARGER (depuis les docs Word) -->
<section class="space" style="background:linear-gradient(135deg,#0f172a,#1e3a8a);" data-aos="fade-up" data-aos-duration="800">
    <div class="container">
        <div class="text-center" style="margin-bottom:40px;">
            <span class="sub-title" style="color:rgba(255,255,255,.6);">Documents officiels</span>
            <h2 style="font-size:clamp(1.4rem,3vw,2rem);font-weight:700;color:#fff;margin:6px 0 8px;">Guides Étudiants des Filières</h2>
            <p style="color:rgba(255,255,255,.7);font-size:.9rem;max-width:600px;margin:0 auto;">
                Téléchargez le guide détaillé de votre filière : programmes, conditions d'admission, débouchés et contacts.
            </p>
        </div>
        <div class="row gy-3">
            @php
            $guides = [
                ['icon'=>'fa-chart-line','label'=>'Filière Gestion','detail'=>'GRH · Comptabilité · Marketing · Banque · Logistique · Journalisme','color'=>'#1e3a8a','inst'=>'ISGMM'],
                ['icon'=>'fa-scale-balanced','label'=>'Filière Droit','detail'=>'Droit des affaires · Droit public · Sciences politiques · Capacité en Droit','color'=>'#1e3a8a','inst'=>'ISGMM'],
                ['icon'=>'fa-utensils','label'=>'Tourisme, Hôtellerie & Restauration','detail'=>'Management touristique · Génie culinaire · Gestion hôtelière · THR','color'=>'#1e3a8a','inst'=>'ISGMM'],
                ['icon'=>'fa-scissors','label'=>'Industrie Habillement & Design','detail'=>'BTS Habillement · Licence Haute Couture & Design de Mode','color'=>'#1e3a8a','inst'=>'ISGMM'],
                ['icon'=>'fa-laptop-code','label'=>'Génie Informatique & Réseaux','detail'=>'Génie Logiciel · MSI · Réseaux & Sécurité · Télécommunications · E-Commerce','color'=>'#b45309','inst'=>'ISTIM'],
            ];
            @endphp
            @foreach($guides as $g)
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $loop->index * 80 }}">
                <div style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:18px 20px;display:flex;align-items:center;gap:14px;transition:transform .25s,background .25s;cursor:default;"
                     onmouseenter="this.style.background='rgba(255,255,255,.13)';this.style.transform='translateY(-3px)'"
                     onmouseleave="this.style.background='rgba(255,255,255,.07)';this.style.transform='none'">
                    <div style="width:46px;height:46px;border-radius:10px;background:{{ $g['color'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas {{ $g['icon'] }}" style="color:#fff!important;font-size:1.1rem;"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px;">
                            <span style="font-size:.65rem;font-weight:700;background:{{ $g['color'] }};color:#fff;padding:1px 7px;border-radius:3px;">{{ $g['inst'] }}</span>
                        </div>
                        <p style="font-size:.87rem;font-weight:600;color:#fff;margin:0 0 3px;line-height:1.3;">{{ $g['label'] }}</p>
                        <p style="font-size:.72rem;color:rgba(255,255,255,.6);margin:0;line-height:1.4;">{{ $g['detail'] }}</p>
                    </div>
                    <a href="{{ route('preinscription.index') }}" class="th-btn" style="font-size:.72rem;padding:7px 13px;white-space:nowrap;flex-shrink:0;" title="S'inscrire">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- TÉMOIGNAGES -->
<section class="space" style="background-image:url('{{ asset('asset_vitrine/assets/img/update1/bg/testi_bg_5.jpg') }}');background-size:cover;background-position:center;position:relative;" data-aos="fade-up" data-aos-duration="800">
    <div style="position:absolute;inset:0;background:rgba(14,30,80,.83);"></div>
    <div class="container" style="position:relative;z-index:2;">
        <div style="margin-bottom:32px;">
            <span class="sub-title" style="color:rgba(255,255,255,.65);">Ce que disent nos futurs étudiants</span>
            <h2 style="font-size:clamp(1.4rem,3vw,2rem);font-weight:700;color:#fff;margin:6px 0 0;">Témoignages</h2>
        </div>
        <div class="row gy-4">
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                <div class="testi-list">
                    <div class="testi-list_img">
                        <img src="{{ asset('asset_vitrine/assets/img/update1/testimonial/testi_2_1.jpg') }}" alt="Emmanuel Ndongo">
                        <div><h3 class="testi-list_name">Emmanuel Ndongo</h3><span class="testi-list_desig">Futur étudiant en mécanique — ISTIM</span></div>
                    </div>
                    <p class="testi-list_text">J'espère que la formation en mécanique me permettra d'ouvrir mon propre garage. Les cours pratiques semblent vraiment prometteurs !</p>
                    <div class="testi-list_review"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                <div class="testi-list">
                    <div class="testi-list_img">
                        <img src="{{ asset('asset_vitrine/assets/img/update1/testimonial/testi_2_2.jpg') }}" alt="Clara Essomba">
                        <div><h3 class="testi-list_name">Clara Essomba</h3><span class="testi-list_desig">Future étudiante en soins infirmiers — ISSBM</span></div>
                    </div>
                    <p class="testi-list_text">Je suis enthousiaste à l'idée de devenir infirmière. La formation à l'ISSBM combine théorie solide et pratique clinique intensive !</p>
                    <div class="testi-list_review"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- GALERIE -->
<section class="space" data-aos="fade-up" data-aos-duration="800">
    <div class="container">
        <div class="text-center" style="margin-bottom:36px;">
            <span class="sub-title">Vie à l'école</span>
            <h2 class="sec-title" style="text-align:center;">Découvrez notre campus</h2>
        </div>
        <div class="row gy-4">
            @foreach(['gallery_2_1.jpg','gallery_2_2.jpg','gallery_2_3.jpg','gallery_2_6.jpg','gallery_2_4.jpg','gallery_2_5.jpg'] as $gi => $gimg)
            <div class="col-sm-6 col-lg-4" data-aos="zoom-in" data-aos-delay="{{ ($gi+1)*75 }}">
                <div class="gallery-card">
                    <div class="gallery-img">
                        <img src="{{ asset('asset_vitrine/assets/img/update1/gallery/'.$gimg) }}" alt="Campus IUM">
                        <a href="#" class="gallery-btn popup-image"><i class="fas fa-eye"></i></a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ESPACE ÉTUDIANTS -->
<div class="video-area-1" data-aos="fade-up" data-aos-duration="800">
    <div class="container">
        <div class="row gy-4 align-items-center">
            <div class="col-lg-7 order-lg-2">
                {{-- Mini-galerie : 1 grande image + 2 petites empilées --}}
                <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:12px;">
                    <img src="{{ asset('asset_vitrine/assets/img/update1/normal/espace_etudiant_1.jpeg') }}"
                         alt="Espace étudiant - salle de cours"
                         style="border-radius:var(--r-lg);box-shadow:var(--sh-lg);width:100%;height:100%;min-height:280px;object-fit:cover;">
                    <div style="display:grid;grid-template-rows:1fr 1fr;gap:12px;">
                        <img src="{{ asset('asset_vitrine/assets/img/update1/normal/espace_etudiant_2.jpeg') }}"
                             alt="Espace étudiant - bibliothèque"
                             style="border-radius:var(--r-md);box-shadow:var(--sh-md);width:100%;height:100%;min-height:134px;object-fit:cover;">
                        <img src="{{ asset('asset_vitrine/assets/img/update1/normal/espace_etudiant_3.jpeg') }}"
                             alt="Espace étudiant - espace détente"
                             style="border-radius:var(--r-md);box-shadow:var(--sh-md);width:100%;height:100%;min-height:134px;object-fit:cover;">
                    </div>
                </div>
            </div>
            <div class="col-lg-5 order-lg-1">
                <h2 class="sec-title">Espace et cadre propice dédiés aux étudiants</h2>
                <p>Nous offrons un environnement stimulant et favorable à l'apprentissage pour les étudiants et leurs encadrants.</p>
                <div class="checklist" style="margin:18px 0 26px;">
                    <ul>
                        <li>Sécurité et confort garantis</li>
                        <li>Réussite favorisée par un environnement adéquat</li>
                        <li>Cadre convivial pour un soutien académique et social</li>
                        <li>Espace conçu pour la concentration et la productivité</li>
                    </ul>
                </div>
                <a class="th-btn" href="{{ route('preinscription.index') }}">Réserver dès maintenant <i class="fa fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- TRANSPORT -->
<div class="video-area-1" style="background:var(--g100);" data-aos="fade-up" data-aos-duration="800">
    <div class="container">
        <div class="row gy-4 align-items-center">
            <div class="col-lg-5 order-lg-2">
                <h2 class="sec-title">Transport dédié pour les étudiants</h2>
                <p>Nous mettons à disposition des bus et véhicules modernes pour faciliter vos déplacements.</p>
                <div class="checklist" style="margin:18px 0 26px;">
                    <ul>
                        <li>Véhicules confortables et sécurisés</li>
                        <li>Horaires adaptés aux besoins académiques</li>
                        <li>Service fiable pour une expérience sans stress</li>
                        <li>Couverture vers les campus et lieux de stage</li>
                    </ul>
                </div>
                <a class="th-btn" href="#">En savoir plus <i class="fa fa-arrow-right"></i></a>
            </div>
            <div class="col-lg-7 order-lg-1">
                <img src="{{ asset('asset_vitrine/assets/img/normal/video2.png') }}" alt="Transport IUM" style="border-radius:var(--r-lg);box-shadow:var(--sh-lg);width:100%;">
            </div>
        </div>
    </div>
</div>

<!-- ACTUALITÉS -->
<section class="space" id="actualites" data-aos="fade-up" data-aos-duration="800">
    <div class="container">
        <div class="text-center" style="margin-bottom:36px;">
            <span class="sub-title">Nos articles</span>
            <h2 class="sec-title" style="text-align:center;">Actualités IUM</h2>
        </div>
        <div class="row gy-4">
            @forelse($articles as $article)
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 + 100 }}">
                <div class="blog-recent">
                    <div class="blog-img">
                        @if($article->images->count() > 0)
                            @php $cnt = $article->images->count(); @endphp
                            @if($cnt == 1)
                                <img src="{{ $article->images->first()->path }}" alt="{{ $article->title }}" style="width:100%;height:255px;object-fit:cover;">
                            @elseif($cnt == 2)
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:2px;">
                                    @foreach($article->images->take(2) as $img)
                                    <img src="{{ $img->path }}" alt="Photo" style="width:100%;height:195px;object-fit:cover;">
                                    @endforeach
                                </div>
                            @elseif($cnt == 3)
                                <div style="display:grid;grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr;gap:2px;height:255px;">
                                    <div style="grid-row:span 2;"><img src="{{ $article->images->first()->path }}" alt="Photo" style="width:100%;height:100%;object-fit:cover;"></div>
                                    @foreach($article->images->skip(1)->take(2) as $img)
                                    <img src="{{ $img->path }}" alt="Photo" style="width:100%;height:126px;object-fit:cover;">
                                    @endforeach
                                </div>
                            @elseif($cnt == 4)
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:2px;">
                                    @foreach($article->images->take(4) as $img)
                                    <img src="{{ $img->path }}" alt="Photo" style="width:100%;height:127px;object-fit:cover;">
                                    @endforeach
                                </div>
                            @else
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:2px;">
                                    <div style="grid-column:span 2;"><img src="{{ $article->images->first()->path }}" alt="Photo" style="width:100%;height:155px;object-fit:cover;"></div>
                                    @foreach($article->images->skip(1)->take(3) as $img)
                                        @if($loop->last && $cnt > 5)
                                        <div style="position:relative;">
                                            <img src="{{ $img->path }}" alt="Photo" style="width:100%;height:88px;object-fit:cover;">
                                            <div style="position:absolute;inset:0;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem;font-weight:700;">+{{ $cnt-4 }}</div>
                                        </div>
                                        @else
                                        <img src="{{ $img->path }}" alt="Photo" style="width:100%;height:88px;object-fit:cover;">
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                            <span class="badge-photos"><i class="fas fa-images"></i> {{ $cnt }} photo{{ $cnt>1?'s':'' }}</span>
                        @else
                            <img src="{{ asset('images/placeholder.jpg') }}" alt="Image par défaut" style="width:100%;height:255px;object-fit:cover;">
                        @endif
                        @if($article->documents->count() > 0)
                        <div style="position:absolute;top:10px;left:10px;display:flex;gap:6px;flex-wrap:wrap;z-index:3;">
                            <span style="background:rgba(30,58,138,.9);color:#fff;padding:4px 10px;border-radius:20px;font-size:.7rem;display:flex;align-items:center;gap:5px;">
                                <i class="fas fa-file-pdf"></i> {{ $article->documents->count() }} doc{{ $article->documents->count()>1?'s':'' }}
                            </span>
                            <button onclick="dlDocs{{ $article->id }}(event)" class="btn-dl-doc"><i class="fas fa-download"></i> Télécharger</button>
                        </div>
                        <script>
                        function dlDocs{{ $article->id }}(e){e.preventDefault();e.stopPropagation();var docs=@json($article->documents->pluck('path'));docs.forEach(function(u,i){setTimeout(function(){var a=document.createElement('a');a.href=u;a.download=u.split('/').pop();a.target='_blank';document.body.appendChild(a);a.click();document.body.removeChild(a);},i*800);});if(docs.length>1)alert('Téléchargement de '+docs.length+' documents en cours…');}
                        </script>
                        @endif
                    </div>
                    <div class="blog-content">
                        <div class="blog-meta"><a href="#"><i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($article->published_at)->format('d M Y') }}</a></div>
                        <h3 class="blog-title">{{ Str::limit($article->title, 60) }}</h3>
                        <p class="blog-text">{{ Str::limit($article->content, 120) }}</p>
                        @if($article->documents->count() > 0)
                        <div class="article-docs">
                            <p class="article-docs-title"><i class="fas fa-paperclip"></i> Documents joints</p>
                            @foreach($article->documents as $doc)
                            <a href="{{ $doc->path }}" target="_blank" download class="article-doc-link">
                                <i class="fas fa-file-pdf icon-pdf"></i><span>{{ basename($doc->path) }}</span><i class="fas fa-download icon-dl"></i>
                            </a>
                            @endforeach
                        </div>
                        @endif
                        <a href="{{ route('articles.show', $article->id) }}" class="th-btn style4" style="font-size:.82rem;padding:8px 16px;margin-top:auto;">Lire la suite <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center"><p style="color:var(--g500);">Aucun article disponible.</p></div>
            @endforelse
        </div>
    </div>
</section>

<!-- CONTACT -->
<section class="space-bottom" id="contact" data-aos="fade-up" data-aos-duration="800">
    <div class="container">
        <div class="row gy-4">
            <div class="col-xl-5" data-aos="fade-right" data-aos-delay="150">
                <div style="padding-top:16px;">
                    <h2 class="border-title">Une question ?</h2>
                    <p style="margin-bottom:26px;color:var(--g500);font-size:.9rem;">Notre équipe répond rapidement à toutes vos demandes.</p>
                    <div class="contact-feature">
                        <div class="contact-feature-icon" style="background:#1e3a8a;color:#fff;"><i class="fas fa-location-dot"></i></div>
                        <div>
                            <p class="contact-feature_label">Notre adresse</p>
                            <span class="contact-feature_link">Ndazoa, 7 km de Mbankomo — station Green Oil, route nationale Yaoundé-Douala, entrée à droite (panneau IUM).</span>
                        </div>
                    </div>
                    <div class="contact-feature">
                        <div class="contact-feature-icon" style="background:#1e3a8a;color:#fff;"><i class="fas fa-phone"></i></div>
                        <div>
                            <p class="contact-feature_label">Téléphone</p>
                            <span class="contact-feature_link">+237 655 34 19 39</span>
                            <span class="contact-feature_link">+237 695 830 031</span>
                        </div>
                    </div>
                    <div class="contact-feature">
                        <div class="contact-feature-icon" style="background:#1e3a8a;color:#fff;"><i class="fas fa-clock"></i></div>
                        <div>
                            <p class="contact-feature_label">Horaires d'ouverture</p>
                            <span class="contact-feature_link">Lundi – Vendredi : 08h00 – 17h00</span>
                            <span class="contact-feature_link">Samedi : 09h00 – 13h00</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-7" data-aos="fade-left" data-aos-delay="150">
                <div class="contact-form-wrap">
                    <span class="sub-title">Contactez-nous !</span>
                    <h2 class="border-title">Prenez contact</h2>
                    <p style="margin-bottom:22px;color:var(--g500);font-size:.88rem;">Nous sommes là pour répondre à toutes vos questions.</p>
                    <form>
                        <div class="row">
                            <div class="col-md-6"><div class="form-group"><input type="text" class="form-control style-white" name="name" placeholder="Votre nom *"><i class="fas fa-user"></i></div></div>
                            <div class="col-md-6"><div class="form-group"><input type="email" class="form-control style-white" name="email" placeholder="Adresse e-mail *"><i class="fas fa-envelope"></i></div></div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <select name="subject" class="form-control style-white">
                                        <option value="" disabled selected hidden>Sujet *</option>
                                        <option>ISGMM — Gestion</option>
                                        <option>ISTIM — Informatique</option>
                                        <option>ISSBM — Soins infirmiers</option>
                                        <option>Inscription générale</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6"><div class="form-group"><input type="tel" class="form-control style-white" name="number" placeholder="Téléphone *"><i class="fas fa-phone"></i></div></div>
                            <div class="col-12"><div class="form-group"><textarea name="message" rows="4" class="form-control style-white" placeholder="Votre message *"></textarea><i class="fas fa-pen"></i></div></div>
                            <div class="col-12"><button type="submit" class="th-btn">Envoyer le message <i class="fas fa-arrow-right"></i></button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- NEWSLETTER -->
<div style="padding:0 0 52px;" data-aos="fade-up" data-aos-duration="800">
    <div class="container">
        <div class="newsletter-wrap">
            <div class="nl-col-form">
                <h3>Inscrivez-vous à notre newsletter</h3>
                <form action="#" class="nl-form">
                    <input type="email" placeholder="Votre adresse e-mail">
                    <button type="submit" class="th-btn">S'abonner <i class="fas fa-arrow-right"></i></button>
                </form>
            </div>
            <div class="nl-col-social">
                <h3>Restez en contact !</h3>
                <div class="th-social">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-tiktok"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer class="footer-wrapper">
    <div class="container" style="padding-bottom:38px;">
        <div class="row gy-4">
            <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="80">
                <div style="margin-bottom:14px;">
                    <a href="#"><img src="{{ asset('images/logo.png') }}" style="height:58px;width:auto;" alt="IUM"></a>
                </div>
                <p style="color:rgba(255,255,255,.6);font-size:.83rem;line-height:1.65;margin-bottom:16px;">Institut Universitaire La Majestueuse de Ndazoa — Excellence, Professionnalisme, Avenir.</p>
                <div class="th-social">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-tiktok"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="130">
                <h3 class="widget_title">Liens rapides</h3>
                <ul class="footer-menu">
                    <li><a href="#about">À propos de nous</a></li>
                    <li><a href="#instituts">Nos instituts</a></li>
                    <li><a href="#formations">Nos formations</a></li>
                    <li><a href="#concours">Concours d'entrée</a></li>
                    <li><a href="#contact">Contactez-nous</a></li>
                </ul>
            </div>
            <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="180">
                <h3 class="widget_title">Nos Instituts</h3>
                <ul class="footer-menu">
                    <li><a href="#instituts">ISGMM — Gestion & Management</a></li>
                    <li><a href="#instituts">ISTIM — Technologie & Ingénierie</a></li>
                    <li><a href="#instituts">ISSBM — Sciences Biomédicales</a></li>
                </ul>
            </div>
            <div class="col-md-6 col-xl-3" data-aos="fade-up" data-aos-delay="230">
                <h3 class="widget_title">Contactez-nous</h3>
                <div class="info-box"><div class="info-box_icon"><i class="fas fa-location-dot"></i></div><p class="info-box_text">Ndazoa, 7 km de Mbankomo, station Green Oil, route Yaoundé-Douala.</p></div>
                <div class="info-box"><div class="info-box_icon"><i class="fas fa-phone"></i></div><div><p class="info-box_text"><a href="tel:+237655341939">+237 655 34 19 39</a></p></div></div>
                <div class="info-box"><div class="info-box_icon"><i class="fas fa-globe"></i></div><p class="info-box_text"><a href="http://www.ium-ndazoa.com">@ium-ndazoa.com</a></p></div>
            </div>
        </div>
    </div>
    <div class="copyright">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="copyright-text">© 2026 <a href="#">Institut Universitaire la Majestueuse de Ndazoa</a>. Tous droits réservés.</p>
                </div>
                <div class="col-md-6">
                    <div class="footer-links"><ul><li><a href="#">Confidentialité</a></li><li><a href="#">Conditions d'utilisation</a></li></ul></div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- SCRIPTS -->
<script src="{{ asset('asset_vitrine/assets/js/vendor/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('asset_vitrine/assets/js/app.min.js') }}"></script>
<script src="{{ asset('asset_vitrine/assets/js/main.js') }}"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init({ once:true, duration:700, easing:'ease-out-quad' });
window.addEventListener('load', function(){
    var pre = document.getElementById('magnificentPreloader');
    pre.classList.add('pre-fadeout');
    setTimeout(function(){ pre.style.display='none'; }, 900);
});
window.addEventListener('scroll', function(){
    var nav = document.getElementById('mainNav');
    if(!nav) return;
    if(window.scrollY > 90){ nav.classList.add('fixed-top'); }
    else { nav.classList.remove('fixed-top'); }
});
document.querySelectorAll('.register-btn').forEach(function(btn){
    btn.addEventListener('click', function(e){
        e.preventDefault();
        document.getElementById('filiere').value = this.dataset.filiere || '';
        document.getElementById('registerModal').style.display = 'flex';
    });
});
document.getElementById('closeModal').addEventListener('click', function(){
    document.getElementById('registerModal').style.display = 'none';
});
window.addEventListener('click', function(e){
    var modal = document.getElementById('registerModal');
    if(e.target === modal) modal.style.display = 'none';
});
function toggleContent(){
    var items = document.querySelectorAll('.more-content');
    var btn = document.getElementById('toggleMore');
    var isOpen = items.length && items[0].style.display !== 'none';
    items.forEach(function(el){ el.style.display = isOpen ? 'none' : 'list-item'; });
    btn.innerHTML = isOpen
        ? '<i class="fa fa-plus-circle"></i> Voir plus de filières'
        : '<i class="fa fa-minus-circle"></i> Voir moins';
}
</script>
</body>
</html>