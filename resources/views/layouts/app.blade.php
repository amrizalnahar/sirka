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
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireScriptConfig
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
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
