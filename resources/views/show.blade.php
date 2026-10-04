<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $article->title }} - ISM NDAZOA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .article-content {
            line-height: 1.8;
            font-size: 1.125rem;
        }

        .article-content p {
            margin-bottom: 1.5rem;
        }

        .image-container {
            position: relative;
            overflow: hidden;
        }

        .image-container::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, transparent 60%, rgba(0,0,0,0.1));
            pointer-events: none;
        }

        .share-btn {
            transition: all 0.3s ease;
        }

        .share-btn:hover {
            transform: translateY(-2px);
        }

        /* Style Facebook pour les images multiples */
        .fb-image-grid {
            display: grid;
            gap: 2px;
            margin: 20px 0;
            border-radius: 12px;
            overflow: hidden;
        }

        /* 1 image */
        .fb-image-grid.grid-1 {
            grid-template-columns: 1fr;
        }

        .fb-image-grid.grid-1 .fb-image-item img {
            max-height: 600px;
        }

        /* 2 images */
        .fb-image-grid.grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .fb-image-grid.grid-2 .fb-image-item img {
            height: 400px;
        }

        /* 3 images */
        .fb-image-grid.grid-3 {
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
        }

        .fb-image-grid.grid-3 .fb-image-item:first-child {
            grid-row: span 2;
        }

        .fb-image-grid.grid-3 .fb-image-item:first-child img {
            height: 100%;
            min-height: 400px;
        }

        .fb-image-grid.grid-3 .fb-image-item:not(:first-child) img {
            height: 199px;
        }

        /* 4 images */
        .fb-image-grid.grid-4 {
            grid-template-columns: repeat(2, 1fr);
        }

        .fb-image-grid.grid-4 .fb-image-item img {
            height: 300px;
        }

        /* 5+ images */
        .fb-image-grid.grid-5-plus {
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
        }

        .fb-image-grid.grid-5-plus .fb-image-item:first-child {
            grid-column: span 2;
        }

        .fb-image-grid.grid-5-plus .fb-image-item:first-child img {
            height: 400px;
        }

        .fb-image-grid.grid-5-plus .fb-image-item:not(:first-child) img {
            height: 200px;
        }

        .fb-image-item {
            position: relative;
            overflow: hidden;
            background: #f0f2f5;
            cursor: pointer;
        }

        .fb-image-item img {
            width: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
        }

        .fb-image-item:hover img {
            transform: scale(1.05);
        }

        /* Overlay pour "+X photos" */
        .fb-more-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 600;
        }

        /* Lightbox */
        .lightbox {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.95);
            justify-content: center;
            align-items: center;
        }

        .lightbox.active {
            display: flex;
        }

        .lightbox-content {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 40px;
            cursor: pointer;
            z-index: 10000;
        }

        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 20px;
            cursor: pointer;
            font-size: 30px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .lightbox-nav:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .lightbox-prev {
            left: 20px;
        }

        .lightbox-next {
            right: 20px;
        }

        @media (max-width: 768px) {
            .fb-image-grid.grid-2 .fb-image-item img,
            .fb-image-grid.grid-4 .fb-image-item img {
                height: 200px;
            }

            .fb-image-grid.grid-3 .fb-image-item:first-child img {
                min-height: 200px;
            }

            .fb-image-grid.grid-5-plus .fb-image-item:first-child img {
                height: 250px;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Header avec gradient -->
    <div class="gradient-bg py-8 mb-12">
        <div class="container mx-auto px-4">
            <a href="/#actualites" class="inline-flex items-center text-white hover:text-gray-200 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                <span class="font-medium">Retour aux actualités</span>
            </a>
        </div>
    </div>

    <!-- Contenu principal -->
    <article class="container mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        <div class="max-w-4xl mx-auto">
            <!-- Badge catégorie -->
            <div class="mb-6" data-aos="fade-down">
                <span class="inline-block px-4 py-2 bg-indigo-100 text-indigo-700 rounded-full text-sm font-semibold">
                    Actualités
                </span>
            </div>

            <!-- Titre -->
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight" data-aos="fade-up">
                {{ $article->title }}
            </h1>

            <!-- Métadonnées -->
            <div class="flex flex-wrap items-center gap-6 mb-10 text-gray-600" data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center gap-2">
                    <i class="far fa-calendar text-indigo-600"></i>
                    <span class="text-sm">{{ \Carbon\Carbon::parse($article->published_at)->format('d M Y') }}</span>
                </div>
                @if($article->images->count() > 0)
                    <div class="flex items-center gap-2">
                        <i class="far fa-images text-indigo-600"></i>
                        <span class="text-sm">{{ $article->images->count() }} {{ $article->images->count() > 1 ? 'photos' : 'photo' }}</span>
                    </div>
                @endif
                @if($article->documents->count() > 0)
                    <div class="flex items-center gap-2">
                        <i class="far fa-file-pdf text-indigo-600"></i>
                        <span class="text-sm">{{ $article->documents->count() }} {{ $article->documents->count() > 1 ? 'documents' : 'document' }}</span>
                    </div>
                @endif
                <div class="flex items-center gap-2">
                    <i class="far fa-user text-indigo-600"></i>
                    <span class="text-sm">ISM NDAZOA</span>
                </div>
            </div>

            <!-- Galerie d'images style Facebook -->
            @if($article->images->count() > 0)
                <div class="mb-12" data-aos="zoom-in" data-aos-delay="200">
                    @php
                        $imageCount = $article->images->count();
                        $gridClass = 'grid-1';
                        if ($imageCount == 2) $gridClass = 'grid-2';
                        elseif ($imageCount == 3) $gridClass = 'grid-3';
                        elseif ($imageCount == 4) $gridClass = 'grid-4';
                        elseif ($imageCount >= 5) $gridClass = 'grid-5-plus';
                    @endphp

                    <div class="fb-image-grid {{ $gridClass }}">
                        @foreach($article->images as $index => $image)
                            @if($imageCount >= 5 && $index >= 4)
                                @if($index == 4)
                                    <div class="fb-image-item" onclick="openLightbox({{ $index }})">
                                        <img src="{{ $image->path }}" alt="Image {{ $index + 1 }}">
                                        @if($imageCount > 5)
                                            <div class="fb-more-overlay">
                                                +{{ $imageCount - 4 }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @else
                                <div class="fb-image-item" onclick="openLightbox({{ $index }})">
                                    <img src="{{ $image->path }}" alt="Image {{ $index + 1 }}">
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Boutons de partage -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-12" data-aos="fade-up" data-aos-delay="300">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <p class="text-gray-700 font-semibold">Partager cet article :</p>
                    <div class="flex gap-3">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="share-btn bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 shadow-md">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($article->title) }}" target="_blank" class="share-btn bg-sky-500 text-white p-3 rounded-lg hover:bg-sky-600 shadow-md">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($article->title . ' ' . url()->current()) }}" target="_blank" class="share-btn bg-green-600 text-white p-3 rounded-lg hover:bg-green-700 shadow-md">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url()->current()) }}&title={{ urlencode($article->title) }}" target="_blank" class="share-btn bg-blue-700 text-white p-3 rounded-lg hover:bg-blue-800 shadow-md">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="mailto:?subject={{ urlencode($article->title) }}&body={{ urlencode(url()->current()) }}" class="share-btn bg-gray-600 text-white p-3 rounded-lg hover:bg-gray-700 shadow-md">
                            <i class="fas fa-envelope"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contenu de l'article -->
            <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12 mb-12" data-aos="fade-up" data-aos-delay="400">
                <div class="article-content text-gray-700 prose prose-lg max-w-none">
                    <p class="text-xl leading-relaxed first-letter:text-6xl first-letter:font-bold first-letter:text-indigo-600 first-letter:mr-2 first-letter:float-left">
                        {{ $article->content }}
                    </p>
                </div>
            </div>

            <!-- Section Documents -->
            @if($article->documents->count() > 0)
                <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12 mb-12" data-aos="fade-up" data-aos-delay="500">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                        <i class="fas fa-file-download text-indigo-600"></i>
                        {{ $article->documents->count() > 1 ? 'Documents à télécharger' : 'Document à télécharger' }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($article->documents as $document)
                            <div class="border border-gray-200 rounded-xl p-4 hover:shadow-lg transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-file-pdf text-red-600 text-xl"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ basename($document->path) }}</p>
                                            <p class="text-sm text-gray-500">Document {{ $loop->iteration }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ $document->path }}" target="_blank" download class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2">
                                        <i class="fas fa-download"></i>
                                        <span class="hidden sm:inline">Télécharger</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Call to action -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-8 md:p-12 text-center text-white" data-aos="fade-up" data-aos-delay="600">
                <h2 class="text-3xl font-bold mb-4">Restez informé</h2>
                <p class="text-lg mb-6 text-indigo-100">Ne manquez aucune actualité de l'ISM NDAZOA</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/#actualites" class="inline-flex items-center justify-center px-8 py-4 bg-white text-indigo-600 rounded-xl font-semibold hover:bg-gray-100 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <i class="fas fa-newspaper mr-2"></i>
                        Voir toutes les actualités
                    </a>
                </div>
            </div>
        </div>
    </article>

    <!-- Lightbox -->
    <div class="lightbox" id="lightbox">
        <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
        @if($article->images->count() > 1)
            <button class="lightbox-nav lightbox-prev" onclick="changeImage(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="lightbox-nav lightbox-next" onclick="changeImage(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
        @endif
        <img class="lightbox-content" id="lightbox-img">
    </div>

    <!-- Footer -->
    <footer class="gradient-bg text-white py-8 mt-20">
        <div class="container mx-auto px-4 text-center">
            <p class="text-sm text-gray-200">© 2025 ISM NDAZOA - Tous droits réservés</p>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        const images = @json($article->images->pluck('path'));
        let currentImageIndex = 0;

        function openLightbox(index) {
            currentImageIndex = index;
            document.getElementById('lightbox').classList.add('active');
            document.getElementById('lightbox-img').src = images[currentImageIndex];
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function changeImage(direction) {
            currentImageIndex += direction;
            if (currentImageIndex < 0) currentImageIndex = images.length - 1;
            if (currentImageIndex >= images.length) currentImageIndex = 0;
            document.getElementById('lightbox-img').src = images[currentImageIndex];
        }

        // Fermer avec Échap
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') closeLightbox();
            if (event.key === 'ArrowLeft') changeImage(-1);
            if (event.key === 'ArrowRight') changeImage(1);
        });

        // Empêcher la propagation du clic sur l'image
        document.getElementById('lightbox-img').addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Fermer en cliquant sur le fond
        document.getElementById('lightbox').addEventListener('click', function(e) {
            if (e.target === this) closeLightbox();
        });
    </script>
</body>
</html>