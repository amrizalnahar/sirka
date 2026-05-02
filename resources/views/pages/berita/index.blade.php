@extends('layouts.public')

@section('title', $seo['title'] ?? 'Berita & Kegiatan')
@section('meta_description', $seo['description'] ?? '')
@section('meta_keywords', $seo['keywords'] ?? '')

@section('content')

{{-- Page Header --}}
<section class="bg-gradient-to-r from-dark to-primary py-20 text-white text-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-sm text-white/50 mb-4 uppercase tracking-wider">
            <a href="{{ url('/') }}" class="hover:text-white transition duration-300">Beranda</a>
            <span class="mx-2">&gt;</span>
            <span class="text-white">Berita & Kegiatan</span>
        </div>
        <h1 class="font-display text-4xl lg:text-5xl font-bold mb-4">Berita & Kegiatan</h1>
        <p class="font-body text-lg text-primary-light">Informasi terkini dari kepala desa untuk warga Desa {{ \App\Models\SiteSetting::getValue('village_name', 'Desa Kita') }}</p>
    </div>
</section>

{{-- Search & Filter --}}
<div x-data="{
    searchQuery: '',
    activeCategory: 'semua',
    activeYear: '',
    years: @json($years, JSON_HEX_QUOT),
    visibleCount: 9,
    loading: false,
    filteredCount: 0,
    isFiltered() { return this.searchQuery !== '' || this.activeCategory !== 'semua' || this.activeYear !== ''; },
    shouldShow(index) { return this.isFiltered() || index < this.visibleCount; },
    updateFilteredCount() {
        setTimeout(() => {
            this.filteredCount = [...$refs.postGrid.querySelectorAll('article')].filter(el => el.offsetParent !== null).length;
        }, 350);
    },
    loadMore() {
        this.loading = true;
        setTimeout(() => { this.visibleCount += 6; this.loading = false; }, 800);
    }
}" x-init="$watch('searchQuery', () => updateFilteredCount()); $watch('activeCategory', () => updateFilteredCount()); $watch('activeYear', () => updateFilteredCount()); $nextTick(() => updateFilteredCount());">
    <section class="bg-white border-b border-gray-200 py-6 md:sticky md:top-20 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Search Bar -->
            <div class="max-w-3xl mx-auto mb-4 relative group">
                <input type="text" x-model="searchQuery"
                       placeholder="Cari berita atau kegiatan..."
                       class="w-full pl-12 pr-12 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white focus:border-transparent transition-all shadow-sm">

                <!-- Search icon -->
                <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none group-focus-within:text-primary transition-colors"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>

                <!-- Clear search -->
                <div class="absolute right-3 top-1/2 -translate-y-1/2">
                    <button x-show="searchQuery !== ''" @click="searchQuery = ''"
                            class="text-gray-400 hover:text-red-500 transition-colors p-1"
                            title="Hapus pencarian">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Filter Chips & Year -->
            <div class="flex flex-wrap gap-2 justify-center items-center">
                <button @click="activeCategory = 'semua'"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition-all shadow-sm focus:outline-none"
                        :class="activeCategory === 'semua' ? 'bg-primary text-white' : 'bg-gray-50 border border-gray-200 text-gray-600 hover:bg-gray-100 hover:text-primary'">
                    Semua
                </button>
                @foreach($categories as $category)
                    <button @click="activeCategory = '{{ $category->id }}'"
                            class="rounded-full px-4 py-2 text-sm font-semibold transition-all shadow-sm focus:outline-none"
                            :class="activeCategory === '{{ $category->id }}' ? 'bg-primary text-white' : 'bg-gray-50 border border-gray-200 text-gray-600 hover:bg-gray-100 hover:text-primary'">
                        {{ $category->name }}
                    </button>
                @endforeach

                <!-- Year Filter -->
                <div class="relative ml-2">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <select x-model="activeYear"
                            class="bg-gray-50 border border-gray-200 text-gray-600 text-sm rounded-full pl-9 pr-8 py-2 focus:ring-2 focus:ring-primary focus:border-transparent focus:outline-none shadow-sm cursor-pointer appearance-none">
                        <option value="">Semua Tahun</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                    <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>

                <button x-show="activeYear !== ''" @click="activeYear = ''"
                        class="text-sm text-gray-500 hover:text-red-500 transition-colors p-1"
                        title="Hapus filter tahun">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </section>

    {{-- Featured Post --}}
    @if($featured)
    <section class="bg-gray-50 pt-8 pb-4" x-show="searchQuery === '' && activeCategory === 'semua' && activeYear === ''" x-cloak>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('berita.show', $featured->slug) }}" class="block">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center bg-white rounded-2xl p-4 lg:p-6 shadow-sm border border-gray-100 group hover:shadow-md transition-all">
                    <!-- Image -->
                    <div class="relative overflow-hidden rounded-xl h-64 lg:h-full w-full">
                        <img src="{{ $featured->thumbnail ? \Illuminate\Support\Facades\Storage::url($featured->thumbnail) : 'https://placehold.co/700x394/1A6FAA/FFFFFF?text=Berita' }}"
                             alt="{{ $featured->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <span class="absolute top-4 left-4 bg-accent text-white text-xs font-bold px-3 py-1 rounded-full shadow-md uppercase tracking-wider">Berita Utama</span>
                    </div>

                    <!-- Content -->
                    <div class="py-2 lg:py-6 lg:pr-6 flex flex-col h-full justify-center">
                        <div>
                            @if($featured->category)
                                <span class="bg-primary-light text-primary text-xs font-bold rounded-full px-3 py-1 mb-4 inline-block">{{ $featured->category->name }}</span>
                            @endif
                            <h2 class="font-display text-2xl lg:text-3xl font-bold text-dark mb-4 group-hover:text-primary transition-colors line-clamp-3">
                                {{ $featured->title }}
                            </h2>
                            <p class="text-gray-600 text-base leading-relaxed mb-6 line-clamp-3">
                                {{ Str::limit(strip_tags($featured->content), 200) }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-100">
                            <span class="text-sm text-gray-500 font-medium flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ $featured->published_at?->format('d M Y') }} · {{ ceil(str_word_count(strip_tags($featured->content)) / 200) }} menit estimasi baca
                            </span>
                            <span class="bg-primary text-white px-5 py-2.5 rounded-lg font-medium transition-colors shadow-sm inline-flex items-center group-hover:bg-primary-dark">
                                Baca Selengkapnya <span class="ml-2 group-hover:translate-x-1 transition-transform">→</span>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </section>
    @endif

    {{-- Grid Berita --}}
    <section class="bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" x-ref="postGrid">
                @forelse($posts as $post)
                    <article class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 border border-gray-100 flex flex-col h-full overflow-hidden group"
                             x-show="shouldShow({{ $loop->index }}) && (activeCategory === 'semua' || activeCategory == '{{ $post->category_id ?? '' }}') && (activeYear === '' || activeYear == '{{ $post->published_at?->format('Y') }}') && (searchQuery === '' || {{ json_encode(preg_replace('/\s+/', ' ', strtolower($post->title . ' ' . strip_tags($post->content))), JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) }}.includes(searchQuery.toLowerCase()))"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95">
                        <div class="relative overflow-hidden aspect-video">
                            <a href="{{ route('berita.show', $post->slug) }}">
                                <img src="{{ $post->thumbnail ? \Illuminate\Support\Facades\Storage::url($post->thumbnail) : 'https://placehold.co/400x225/1A6FAA/FFFFFF?text=Berita' }}"
                                     alt="{{ $post->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                     loading="lazy">
                            </a>
                            @if($post->category)
                                <div class="absolute top-3 right-3">
                                    <span class="bg-white/90 backdrop-blur-sm text-primary text-xs font-bold rounded-full px-2.5 py-1 shadow-sm">{{ $post->category->name }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="p-6 flex flex-col flex-grow">
                            <h3 class="font-body font-bold text-lg text-dark mb-2 line-clamp-2 group-hover:text-primary transition-colors">
                                <a href="{{ route('berita.show', $post->slug) }}">{{ $post->title }}</a>
                            </h3>
                            <p class="text-gray-600 text-sm leading-relaxed mb-4 line-clamp-3">
                                {{ Str::limit(strip_tags($post->content), 120) }}
                            </p>
                            <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-xs text-gray-400 font-medium">{{ $post->published_at?->format('d M Y') }} · {{ ceil(str_word_count(strip_tags($post->content)) / 200) }} menit estimasi baca</span>
                                <a href="{{ route('berita.show', $post->slug) }}"
                                   class="text-sm font-bold text-primary hover:text-primary-dark flex items-center group/link">
                                    Baca <span class="ml-1 group-hover/link:translate-x-1 transition-transform">→</span>
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full text-center py-16">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-4">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-display font-bold text-dark mb-2">Belum ada berita</h3>
                        <p class="text-gray-500">Berita dan kegiatan akan segera ditambahkan.</p>
                    </div>
                @endforelse
            </div>

            <!-- Empty state for filtered results -->
            <div x-show="isFiltered() && filteredCount === 0" x-transition class="col-span-full text-center py-16">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-display font-bold text-dark mb-2">Tidak ada berita</h3>
                <p class="text-gray-500 mb-4">Tidak ada berita yang cocok dengan filter Anda.</p>
                <button @click="searchQuery = ''; activeCategory = 'semua'; activeYear = ''" class="text-primary font-semibold hover:underline">Reset filter</button>
            </div>

            <!-- Skeleton Loaders -->
            <template x-if="loading">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-8">
                    @for ($i = 0; $i < 6; $i++)
                        <div class="animate-pulse bg-white rounded-xl border border-gray-100 flex flex-col h-full overflow-hidden">
                            <div class="aspect-video bg-gray-200"></div>
                            <div class="p-6 flex flex-col flex-grow">
                                <div class="w-3/4 h-5 bg-gray-200 rounded mb-3"></div>
                                <div class="w-full h-4 bg-gray-200 rounded mb-2"></div>
                                <div class="w-2/3 h-4 bg-gray-200 rounded mb-4"></div>
                                <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                                    <div class="w-24 h-3 bg-gray-200 rounded"></div>
                                    <div class="w-12 h-3 bg-gray-200 rounded"></div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
            </template>

            <!-- Load More -->
            <div x-show="!isFiltered() && !loading && visibleCount < {{ $posts->count() }}"
                 class="text-center mt-10"
                 x-transition>
                <button @click="loadMore()"
                        :disabled="loading"
                        class="bg-white border-2 border-primary text-primary font-semibold rounded-lg px-8 py-3 hover:bg-primary hover:text-white transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 flex items-center justify-center mx-auto min-w-[200px] disabled:opacity-80 disabled:cursor-not-allowed">
                    <span x-show="!loading">Muat Lebih Banyak</span>
                    <span x-show="loading" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memuat...
                    </span>
                </button>
            </div>
        </div>
    </section>
</div>

@endsection
