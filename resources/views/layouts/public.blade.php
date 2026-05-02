<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Title --}}
    <title>@yield('title', config('seo.site_name', \App\Models\SiteSetting::getValue('site_name', config('app.name', 'Laravel'))))</title>

    {{-- Meta Description --}}
    <meta name="description" content="@yield('meta_description', config('seo.description', \App\Models\SiteSetting::getValue('site_description', '')))">

    {{-- Meta Keywords --}}
    <meta name="keywords" content="@yield('meta_keywords', implode(', ', config('seo.keywords', [])))">

    {{-- Canonical URL --}}
    <link rel="canonical" href="@yield('canonical_url', \App\Helpers\SeoHelper::canonicalUrl())">

    {{-- Robots --}}
    <meta name="robots" content="@yield('meta_robots', config('seo.default_robots', 'index, follow'))">

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:title" content="@yield('og_title', trim(View::getSection('title') ?? config('seo.site_name', '')))">
    <meta property="og:description" content="@yield('og_description', trim(View::getSection('meta_description') ?? config('seo.description', '')))">
    <meta property="og:image" content="@yield('og_image', config('seo.default_image'))">
    <meta property="og:image:width" content="{{ config('seo.og.image_width', 1200) }}">
    <meta property="og:image:height" content="{{ config('seo.og.image_height', 630) }}">
    <meta property="og:locale" content="{{ config('seo.og.locale', 'id_ID') }}">
    <meta property="og:site_name" content="{{ config('seo.site_name') }}">
    @if(config('seo.facebook_app_id'))
        <meta property="fb:app_id" content="{{ config('seo.facebook_app_id') }}">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="@yield('twitter_card', config('seo.twitter.card_type', 'summary_large_image'))">
    <meta name="twitter:url" content="@yield('twitter_url', url()->current())">
    <meta name="twitter:title" content="@yield('twitter_title', trim(View::getSection('title') ?? config('seo.site_name', '')))">
    <meta name="twitter:description" content="@yield('twitter_description', trim(View::getSection('meta_description') ?? config('seo.description', '')))">
    <meta name="twitter:image" content="@yield('twitter_image', config('seo.default_image'))">
    @if(config('seo.twitter_handle'))
        <meta name="twitter:site" content="{{ config('seo.twitter_handle') }}">
        <meta name="twitter:creator" content="{{ config('seo.twitter_handle') }}">
    @endif

    {{-- Author --}}
    <meta name="author" content="@yield('meta_author', config('seo.author', ''))">

    {{-- Favicon --}}
    @if($favicon = \App\Models\SiteSetting::getValue('site_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ \Illuminate\Support\Facades\Storage::url($favicon) }}">
    @endif

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ config('seo.google_fonts_url') }}" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>[x-cloak] { display: none !important; }</style>

    @stack('styles')
</head>
<body class="font-body bg-gray-100 text-gray-600 antialiased">

    <!-- Navbar -->
    <nav class="fixed top-0 left-0 w-full z-50 bg-white shadow-sm transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">

                <!-- KIRI: Logo -->
                <a href="{{ url('/') }}" class="flex-shrink-0 flex items-center group">
                    @if($logo = \App\Models\SiteSetting::getValue('site_logo'))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}"
                             alt="Logo"
                             class="h-12 w-12 rounded-full object-cover border-2 border-primary-light shadow-sm group-hover:border-primary transition duration-300">
                    @else
                        <div class="h-12 w-12 rounded-full bg-primary flex items-center justify-center text-white font-bold text-lg border-2 border-primary-light shadow-sm">
                            {{ substr(\App\Models\SiteSetting::getValue('site_name', config('app.name', 'K')), 0, 1) }}
                        </div>
                    @endif
                    <span class="ml-3 font-body font-bold text-primary text-lg lg:text-xl tracking-tight group-hover:text-primary-dark transition duration-300">
                        {{ \App\Models\SiteSetting::getValue('site_name', config('app.name', 'Admin Panel')) }}
                    </span>
                </a>

                <!-- KANAN (Desktop >= lg) -->
                <div class="hidden lg:flex lg:items-center lg:space-x-6">
                    <a href="{{ route('berita.index') }}"
                       class="nav-link text-gray-600 font-medium hover:text-primary transition duration-150 {{ request()->routeIs('berita.*') ? 'active text-primary font-bold' : '' }}">
                        Berita
                    </a>
                </div>

                <!-- KANAN (Mobile < lg) - Hamburger -->
                <div class="flex items-center lg:hidden">
                    <button type="button" id="mobile-menu-button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-primary hover:bg-primary-light focus:outline-none transition duration-150">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- MOBILE MENU -->
        <div id="mobile-menu" class="hidden lg:hidden bg-white border-t border-gray-100 shadow-lg absolute w-full left-0">
            <div class="px-4 pt-2 pb-6 space-y-1 sm:px-6">
                <a href="{{ route('berita.index') }}"
                   class="mobile-nav-link block px-3 py-3 rounded-md text-base font-medium text-gray-600 transition duration-150 {{ request()->routeIs('berita.*') ? 'active text-primary' : '' }}">
                    Berita
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-20">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-16 pb-6 relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute top-0 left-0 w-full h-full opacity-5 pointer-events-none"
            style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-12 mb-12">

                <!-- Kolom 1: Brand -->
                <div class="lg:col-span-4">
                    <div class="flex items-center mb-6 group">
                        @if($logo = \App\Models\SiteSetting::getValue('site_logo'))
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}"
                                 alt="Logo"
                                 class="h-16 w-16 rounded-full object-cover border-2 border-primary-light shadow-md">
                        @else
                            <div class="h-16 w-16 rounded-full bg-primary flex items-center justify-center text-white font-bold text-2xl border-2 border-primary-light shadow-md">
                                {{ substr(\App\Models\SiteSetting::getValue('site_name', config('app.name', 'K')), 0, 1) }}
                            </div>
                        @endif
                        <div class="ml-4">
                            <h3 class="font-display font-bold text-2xl tracking-wide group-hover:text-primary-light transition-colors duration-300">
                                {{ \App\Models\SiteSetting::getValue('site_name', config('app.name', 'Admin Panel')) }}
                            </h3>
                        </div>
                    </div>
                    <p class="text-gray-300 text-sm leading-relaxed mb-8 pr-4">
                        "Boilerplate admin panel yang reusable, modular, dan siap dikembangkan untuk berbagai kebutuhan aplikasi."
                    </p>
                </div>

                <!-- Kolom 2: Navigasi Cepat -->
                <div class="lg:col-span-2">
                    <h4 class="font-display text-lg font-bold mb-6 text-white uppercase tracking-wider relative inline-block">
                        Navigasi Cepat
                        <span class="absolute bottom-0 left-0 w-1/2 h-0.5 bg-accent transform translate-y-2"></span>
                    </h4>
                    <ul class="space-y-3 mt-2">
                        <li><a href="{{ route('berita.index') }}" class="text-gray-400 hover:text-white hover:translate-x-2 transition-all duration-300 flex items-center"><span class="text-primary mr-2 text-xs">▶</span> Berita</a></li>
                    </ul>
                </div>

                <!-- Kolom 3: Kontak -->
                <div class="lg:col-span-3">
                    <h4 class="font-display text-lg font-bold mb-6 text-white uppercase tracking-wider relative inline-block">
                        Hubungi Kami
                        <span class="absolute bottom-0 left-0 w-1/2 h-0.5 bg-accent transform translate-y-2"></span>
                    </h4>
                    <ul class="space-y-4 mt-2">
                        @if($address = \App\Models\SiteSetting::getValue('contact_address'))
                            <li class="flex items-start group">
                                <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center mr-4 flex-shrink-0 group-hover:bg-primary transition-colors duration-300">
                                    <svg class="w-5 h-5 text-primary group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <span class="text-gray-400 mt-1">{{ $address }}</span>
                            </li>
                        @endif
                        @if($phone = \App\Models\SiteSetting::getValue('contact_phone'))
                            <li class="flex items-center group">
                                <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center mr-4 flex-shrink-0 group-hover:bg-primary transition-colors duration-300">
                                    <svg class="w-5 h-5 text-primary group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </div>
                                <span class="text-gray-400">{{ $phone }}</span>
                            </li>
                        @endif
                        @if($email = \App\Models\SiteSetting::getValue('contact_email'))
                            <li class="flex items-center group">
                                <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center mr-4 flex-shrink-0 group-hover:bg-primary transition-colors duration-300">
                                    <svg class="w-5 h-5 text-primary group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <span class="text-gray-400">{{ $email }}</span>
                            </li>
                        @endif
                    </ul>
                </div>

            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-[#ffffff20] pt-6 pb-2 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-gray-400 text-center md:text-left">
                    &copy; {{ date('Y') }} <span class="font-semibold text-gray-300">{{ \App\Models\SiteSetting::getValue('site_name', config('app.name', 'Admin Panel')) }}</span>. Hak cipta dilindungi.
                </p>
            </div>

        </div>
    </footer>

    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('mobile-menu-button');
            const menu = document.getElementById('mobile-menu');
            const mobileLinks = document.querySelectorAll('.mobile-nav-link');
            const navbar = document.querySelector('nav');

            // Sticky navbar effect on scroll
            if (navbar) {
                window.addEventListener('scroll', () => {
                    if (window.scrollY > 10) {
                        navbar.classList.add('shadow-md');
                        navbar.classList.remove('shadow-sm');
                    } else {
                        navbar.classList.add('shadow-sm');
                        navbar.classList.remove('shadow-md');
                    }
                });
            }

            // Toggle menu saat hamburger diklik
            if (btn && menu) {
                btn.addEventListener('click', function() {
                    menu.classList.toggle('hidden');

                    // Animate hamburger icon
                    const svg = btn.querySelector('svg');
                    if (svg) {
                        if (menu.classList.contains('hidden')) {
                            svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />';
                        } else {
                            svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />';
                        }
                    }
                });
            }

            // Tutup menu saat link diklik (di mobile)
            if (mobileLinks.length > 0 && menu) {
                mobileLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        menu.classList.add('hidden');
                        if (btn) {
                            const svg = btn.querySelector('svg');
                            if (svg) {
                                svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />';
                            }
                        }
                    });
                });
            }
        });
    </script>

    @stack('scripts')
    @livewireScripts

    <script>
        /* Scroll-to-error: otomatis scroll ke input yang gagal validasi */
        (function () {
            function scrollToFirstError() {
                const firstError = document.querySelector('p.text-red-500, .border-red-500');
                if (!firstError) return;

                const wrapper = firstError.closest('div');
                if (wrapper) {
                    wrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    const input = wrapper.querySelector('input:not([type="hidden"]), textarea, select');
                    input?.focus({ preventScroll: true });
                } else {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
            document.addEventListener('DOMContentLoaded', scrollToFirstError);
        })();
    </script>

    @if(config('services.ga4.measurement_id') && app()->environment('production'))
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.ga4.measurement_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ config('services.ga4.measurement_id') }}');
        </script>
    @endif
</body>
</html>
