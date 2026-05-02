@extends('layouts.public')

@section('title', $seo['title'] ?? $post->title)
@section('meta_description', $seo['description'] ?? '')
@section('meta_keywords', $seo['keywords'] ?? '')
@section('og_type', 'article')
@section('og_image', $seo['og_image'] ?? config('seo.default_image'))
@section('canonical_url', $seo['canonical_url'] ?? route('berita.show', $post->slug))
@section('meta_author', $seo['meta_author'] ?? config('seo.author'))

@section('content')

<div class="bg-gray-50 pb-16" x-data="{ toast: false, toastMessage: '' }">
    <article class="max-w-3xl mx-auto px-4 sm:px-6 py-12 bg-white mt-8 rounded-2xl shadow-sm border border-gray-100">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['name' => 'Beranda', 'url' => url('/')],
            ['name' => 'Berita', 'url' => route('berita.index')],
            ['name' => $post->title],
        ]" />

        {{-- Header Artikel --}}
        <header>
            @if($post->category)
                <span class="inline-block bg-primary-light text-primary text-xs font-bold rounded-full px-3 py-1 mb-4 uppercase tracking-wider">{{ $post->category->name }}</span>
            @endif

            <h1 class="font-display text-3xl lg:text-4xl text-dark font-bold leading-tight mb-4">
                {{ $post->title }}
            </h1>

            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-400 font-medium mb-6 pb-6 border-b border-gray-100">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    {{ $post->published_at?->format('d M Y') }}
                </div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    {{ $post->author?->name ?? 'Admin Desa' }}
                </div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    {{ $post->views ?? 0 }} views
                </div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    {{ ceil(str_word_count(strip_tags($post->content)) / 200) }} menit estimasi baca
                </div>
            </div>
        </header>

        {{-- Gambar Utama --}}
        @if($post->thumbnail)
            <figure class="mb-8">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($post->thumbnail) }}"
                     alt="{{ $post->title }}"
                     class="w-full rounded-xl shadow-md object-cover">
                <figcaption class="text-xs text-center text-gray-400 mt-3 italic">Ilustrasi kegiatan terkait.</figcaption>
            </figure>
        @endif

        {{-- Konten Artikel --}}
        <div class="prose max-w-none font-body text-base lg:text-lg text-gray-600 leading-[1.9]">
            {!! $post->content !!}
        </div>

        {{-- Footer Artikel (Tags & Share) --}}
        <footer class="mt-10 pt-6 border-t border-gray-100">
            @if($post->tags->count() > 0)
                <div class="flex flex-wrap items-center gap-2 mb-6">
                    <span class="text-sm font-bold text-dark mr-2">Tags:</span>
                    @foreach($post->tags as $tag)
                        <span class="border border-gray-200 rounded-full px-3 py-1 text-xs text-gray-500 hover:bg-gray-50 hover:text-primary transition-colors">#{{ $tag->name }}</span>
                    @endforeach
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-3">
                <span class="text-sm font-bold text-dark w-full sm:w-auto mb-2 sm:mb-0">Bagikan:</span>
                <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . request()->fullUrl()) }}"
                   target="_blank"
                   class="bg-[#25D366] text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-[#20b958] transition-colors flex items-center shadow-sm">
                    <svg class="w-4 h-4 mr-2 fill-current" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"></path>
                    </svg>
                    WhatsApp
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}"
                   target="_blank"
                   class="bg-[#1877F2] text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-[#166fe5] transition-colors flex items-center shadow-sm">
                    <svg class="w-4 h-4 mr-2 fill-current" viewBox="0 0 24 24">
                        <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"></path>
                    </svg>
                    Facebook
                </a>
                <button @click="navigator.clipboard.writeText('{{ request()->fullUrl() }}'); toastMessage = 'Link berhasil disalin!'; toast = true; setTimeout(() => toast = false, 2500)"
                        class="bg-gray-100 text-gray-600 rounded-lg px-4 py-2 text-sm font-medium hover:bg-gray-200 transition-colors flex items-center shadow-sm border border-gray-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Salin Link
                </button>
            </div>
        </footer>
    </article>

    {{-- Berita Terkait --}}
    @if($relatedPosts->count() > 0)
        <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-16">
            <h2 class="font-display font-bold text-2xl text-dark mb-6 relative inline-block">
                Berita Terkait
                <div class="absolute -bottom-2 left-0 w-12 h-1 bg-accent"></div>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($relatedPosts as $related)
                    <article class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 border border-gray-100 flex flex-col h-full overflow-hidden group">
                        <div class="relative overflow-hidden aspect-video">
                            <a href="{{ route('berita.show', $related->slug) }}">
                                <img src="{{ $related->thumbnail ? \Illuminate\Support\Facades\Storage::url($related->thumbnail) : 'https://placehold.co/400x225/1A6FAA/FFFFFF?text=Berita' }}"
                                     alt="{{ $related->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                     loading="lazy">
                            </a>
                            @if($related->category)
                                <div class="absolute top-3 right-3">
                                    <span class="bg-white/90 backdrop-blur-sm text-primary text-xs font-bold rounded-full px-2.5 py-1 shadow-sm">{{ $related->category->name }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="p-5 flex flex-col flex-grow">
                            <h3 class="font-body font-bold text-base text-dark mb-2 line-clamp-2 group-hover:text-primary transition-colors">
                                <a href="{{ route('berita.show', $related->slug) }}">{{ $related->title }}</a>
                            </h3>
                            <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-xs text-gray-400 font-medium">{{ $related->published_at?->format('d M Y') }}</span>
                                <a href="{{ route('berita.show', $related->slug) }}"
                                   class="text-xs font-bold text-primary hover:text-primary-dark">Baca →</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Toast Notification -->
    <div x-show="toast"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-4 sm:bottom-6 left-1/2 transform -translate-x-1/2 z-50 w-[calc(100%-2rem)] sm:w-auto sm:max-w-md"
         style="display: none;">
        <div class="bg-[#1C2B39] text-white px-4 sm:px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3 border border-white/10">
            <div class="w-8 h-8 bg-[#2E7D52] rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold" x-text="toastMessage"></p>
                <p class="text-xs text-white/60">Link telah disalin ke clipboard</p>
            </div>
        </div>
    </div>
</div>

@push('jsonld')
    <x-schema-org type="NewsArticle" :data="[
        'headline' => $post->title,
        'description' => \App\Helpers\SeoHelper::metaDescription($post->content),
        'image' => $post->thumbnail ? [\App\Helpers\SeoHelper::ogImage($post->thumbnail)] : [config('seo.default_image')],
        'datePublished' => $post->published_at?->toIso8601String(),
        'dateModified' => $post->updated_at->toIso8601String(),
        'author' => [
            '@type' => 'Person',
            'name' => $post->author?->name ?? config('seo.author'),
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => config('seo.site_name'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => config('seo.default_image'),
            ],
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => route('berita.show', $post->slug),
        ],
    ]" />
    <x-breadcrumb-schema :items="[
        ['name' => 'Beranda', 'url' => url('/')],
        ['name' => 'Berita', 'url' => route('berita.index')],
        ['name' => $post->title],
    ]" />
@endpush
@endsection
