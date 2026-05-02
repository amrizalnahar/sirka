@extends('layouts.public')

@section('title', 'Halaman Tidak Ditemukan — ' . config('seo.site_name'))
@section('meta_description', 'Maaf, halaman yang Anda cari tidak dapat ditemukan. Silakan kembali ke beranda atau jelajahi konten kami.')
@section('meta_robots', 'noindex, follow')

@section('content')
<section class="bg-gray-50 py-20 lg:py-32 text-center">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-8xl font-display font-bold text-primary mb-6">404</div>
        <h1 class="font-display text-3xl lg:text-4xl text-dark font-bold mb-4">
            Halaman Tidak Ditemukan
        </h1>
        <p class="text-gray-600 text-lg mb-8">
            Maaf, halaman yang Anda cari mungkin telah dipindahkan, dihapus, atau tidak pernah ada.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ url('/') }}" class="bg-primary hover:bg-primary-dark text-white px-8 py-3 rounded-lg font-semibold transition duration-300">
                Kembali ke Beranda
            </a>
            <a href="{{ route('berita.index') }}" class="border-2 border-primary text-primary hover:bg-primary-light px-8 py-3 rounded-lg font-semibold transition duration-300">
                Lihat Berita
            </a>
        </div>
    </div>
</section>
@endsection
