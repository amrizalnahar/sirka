<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \App\Models\SiteSetting::getValue('site_name', config('app.name', 'Laravel')) }}</title>
        @if($favicon = \App\Models\SiteSetting::getValue('site_favicon'))
            <link rel="icon" type="image/x-icon" href="{{ \Illuminate\Support\Facades\Storage::url($favicon) }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <style>
            body { font-family: 'Nunito', system-ui, -apple-system, sans-serif; }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0" style="background-color: #E8F4FB;">
            <div>
                <a href="/" wire:navigate>
                    @if($logo = \App\Models\SiteSetting::getValue('site_logo'))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}" alt="Logo" class="w-20 h-20 object-contain rounded-full">
                    @else
                        <div class="w-20 h-20 flex items-center justify-center rounded-full text-white text-2xl font-bold" style="background-color: #1A6FAA;">
                            {{ strtoupper(substr(\App\Models\SiteSetting::getValue('site_name', config('app.name')), 0, 2)) }}
                        </div>
                    @endif
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
        @livewireScripts
    <script>
        /* Scroll-to-error: otomatis scroll ke input yang gagal validasi */
        (function () {
            function scrollToFirstError() {
                const firstError = document.querySelector('.text-red-500, .text-red-600, .border-red-500');
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
    </body>
</html>
