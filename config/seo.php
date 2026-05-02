<?php

return [
    // === Existing (keep) ===
    'site_name' => env('SEO_SITE_NAME', 'Kampanye Calon Kepala Desa Klegen'),
    'description' => env('SEO_DESCRIPTION', 'Website resmi kampanye calon Kepala Desa Klegen. Visi, misi, program kerja, dan transparansi untuk kemajuan desa.'),
    'keywords' => [
        'calon kepala desa',
        'kampanye desa',
        'Klegen',
        'pemilu desa',
        'program kerja desa',
        'transparansi desa',
    ],
    'default_image' => env('SEO_DEFAULT_IMAGE', '/images/og-default.jpg'),
    'twitter_handle' => env('SEO_TWITTER_HANDLE', '@calondesa'),
    'facebook_app_id' => env('SEO_FB_APP_ID', ''),
    'locale' => env('SEO_OG_LOCALE', 'id_ID'),
    'author' => env('SEO_AUTHOR', 'Tim Kampanye Desa Klegen'),

    // === NEW: Sitemap ===
    'sitemap' => [
        'home' => [
            'priority' => env('SEO_SITEMAP_HOME_PRIORITY', '1.0'),
            'changefreq' => env('SEO_SITEMAP_HOME_FREQ', 'daily'),
        ],
        'list' => [
            'priority' => env('SEO_SITEMAP_LIST_PRIORITY', '0.9'),
            'changefreq' => env('SEO_SITEMAP_LIST_FREQ', 'daily'),
        ],
        'static' => [
            'priority' => env('SEO_SITEMAP_STATIC_PRIORITY', '0.8'),
            'changefreq' => env('SEO_SITEMAP_STATIC_FREQ', 'weekly'),
        ],
        'detail' => [
            'priority' => env('SEO_SITEMAP_DETAIL_PRIORITY', '0.7'),
            'changefreq' => env('SEO_SITEMAP_DETAIL_FREQ', 'weekly'),
        ],
        'low' => [
            'priority' => env('SEO_SITEMAP_LOW_PRIORITY', '0.6'),
            'changefreq' => env('SEO_SITEMAP_LOW_FREQ', 'monthly'),
        ],
    ],

    // === NEW: Robots.txt ===
    'robots' => [
        'crawl_delay' => env('SEO_ROBOTS_CRAWL_DELAY', 10),
        'disallow' => array_filter(explode(',', env('SEO_ROBOTS_DISALLOW', '/admin/,/login/'))),
    ],

    // === NEW: Open Graph / Twitter ===
    'og' => [
        'image_width' => env('SEO_OG_IMAGE_WIDTH', 1200),
        'image_height' => env('SEO_OG_IMAGE_HEIGHT', 630),
        'locale' => env('SEO_OG_LOCALE', 'id_ID'),
    ],
    'twitter' => [
        'card_type' => env('SEO_TWITTER_CARD_TYPE', 'summary_large_image'),
    ],

    // === NEW: Meta Robots Default ===
    'default_robots' => env('SEO_DEFAULT_ROBOTS', 'index, follow'),

    // === NEW: Helper Defaults ===
    'meta_desc_length' => env('SEO_META_DESC_LENGTH', 160),
    'og_storage_path' => env('SEO_OG_STORAGE_PATH', 'storage/'),

    // === NEW: CDN / Assets ===
    'google_fonts_url' => env('SEO_GOOGLE_FONTS_URL', 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Nunito:wght@400;500;600;700&display=swap'),
    'alpinejs_cdn_url' => env('ALPINEJS_CDN_URL', 'https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js'),

    // === NEW: Page-level SEO for list pages ===
    'pages' => [
        'beranda' => [
            'title' => env('SEO_PAGE_BERANDA_TITLE', 'Beranda'),
            'description' => env('SEO_PAGE_BERANDA_DESCRIPTION', null),
            'keywords' => env('SEO_PAGE_BERANDA_KEYWORDS', null),
        ],
        'berita' => [
            'title' => env('SEO_PAGE_BERITA_TITLE', 'Berita & Kegiatan'),
            'description' => env('SEO_PAGE_BERITA_DESCRIPTION', 'Berita terkini, kegiatan, dan informasi dari calon Kepala Desa Klegen.'),
            'keywords' => env('SEO_PAGE_BERITA_KEYWORDS', 'berita desa, kegiatan desa, informasi desa, Klegen'),
        ],
        'catatan' => [
            'title' => env('SEO_PAGE_CATATAN_TITLE', 'Catatan Harian'),
            'description' => env('SEO_PAGE_CATATAN_DESCRIPTION', 'Catatan harian dan refleksi perjalanan kampanye calon Kepala Desa Klegen.'),
            'keywords' => env('SEO_PAGE_CATATAN_KEYWORDS', 'catatan harian, refleksi kampanye, perjalanan desa'),
        ],
        'profil' => [
            'title' => env('SEO_PAGE_PROFIL_TITLE', 'Profil Calon'),
            'description' => env('SEO_PAGE_PROFIL_DESCRIPTION', 'Profil lengkap calon Kepala Desa Klegen. Latar belakang, visi, dan komitmen untuk memajukan desa.'),
            'keywords' => env('SEO_PAGE_PROFIL_KEYWORDS', 'profil calon, calon kepala desa, biografi, Klegen'),
        ],
        'visi_misi' => [
            'title' => env('SEO_PAGE_VISI_MISI_TITLE', 'Visi & Misi'),
            'description' => env('SEO_PAGE_VISI_MISI_DESCRIPTION', 'Visi dan misi calon Kepala Desa Klegen untuk pembangunan desa yang berkelanjutan.'),
            'keywords' => env('SEO_PAGE_VISI_MISI_KEYWORDS', 'visi misi, program desa, pembangunan desa, Klegen'),
        ],
        'program_kerja' => [
            'title' => env('SEO_PAGE_PROGRAM_TITLE', 'Program Kerja'),
            'description' => env('SEO_PAGE_PROGRAM_DESCRIPTION', 'Program kerja dan rencana pembangunan Desa Klegen untuk periode mendatang.'),
            'keywords' => env('SEO_PAGE_PROGRAM_KEYWORDS', 'program kerja, rencana pembangunan, program desa, Klegen'),
        ],
        'transparansi' => [
            'title' => env('SEO_PAGE_TRANSPARANSI_TITLE', 'Transparansi & Laporan'),
            'description' => env('SEO_PAGE_TRANSPARANSI_DESCRIPTION', 'Laporan transparansi penggunaan dana dan kegiatan Desa Klegen.'),
            'keywords' => env('SEO_PAGE_TRANSPARANSI_KEYWORDS', 'transparansi desa, laporan dana, akuntabilitas, Klegen'),
        ],
        'galeri' => [
            'title' => env('SEO_PAGE_GALERI_TITLE', 'Galeri Kegiatan'),
            'description' => env('SEO_PAGE_GALERI_DESCRIPTION', 'Dokumentasi foto dan galeri kegiatan kampanye calon Kepala Desa Klegen.'),
            'keywords' => env('SEO_PAGE_GALERI_KEYWORDS', 'galeri desa, foto kegiatan, dokumentasi, Klegen'),
        ],
        'aspirasi' => [
            'title' => env('SEO_PAGE_ASPIRASI_TITLE', 'Aspirasi Warga'),
            'description' => env('SEO_PAGE_ASPIRASI_DESCRIPTION', 'Sampaikan aspirasi, masukan, dan harapan Anda untuk kemajuan Desa Klegen.'),
            'keywords' => env('SEO_PAGE_ASPIRASI_KEYWORDS', 'aspirasi warga, masukan masyarakat, harapan desa, Klegen'),
        ],
    ],
];
