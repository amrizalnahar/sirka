<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ \App\Models\SiteSetting::getValue('site_name', config('app.name', 'Laravel')) }} - Admin</title>
    @if($favicon = \App\Models\SiteSetting::getValue('site_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ \Illuminate\Support\Facades\Storage::url($favicon) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireScriptConfig
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js" data-navigate-once></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js" data-navigate-once></script>
    <script>
        (function () {
            var collapsed = JSON.parse(localStorage.getItem('sidebar_collapsed') || 'false');
            document.documentElement.style.setProperty('--sidebar-width', collapsed ? '64px' : '256px');
        })();
    </script>
    <style>
        trix-editor { min-height: 300px; }
        .trix-button-group--file-tools { display: none !important; }
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    <script>
        /* Scroll-to-error: otomatis scroll ke input pertama yang gagal validasi */
        (function () {
            let lastScrollTime = 0;

            function scrollToFirstError() {
                const firstError = document.querySelector('.text-red-600, .text-red-500, .border-red-500');
                if (!firstError) return;

                const now = Date.now();
                if (now - lastScrollTime < 1200) return;
                lastScrollTime = now;

                // Hitung posisi scroll agar error berada di tengah layar
                const rect = firstError.getBoundingClientRect();
                const offset = 120; // ruang untuk sticky header
                const scrollTop = window.pageYOffset + rect.top - offset;

                window.scrollTo({ top: scrollTop, behavior: 'smooth' });

                // Fokus ke input terdekat
                const wrapper = firstError.closest('div, section, form');
                if (wrapper) {
                    const input = wrapper.querySelector('input:not([type="hidden"]), textarea, select, trix-editor');
                    if (input && typeof input.focus === 'function') {
                        setTimeout(() => input.focus({ preventScroll: true }), 400);
                    }
                }
            }

            // 1. Deteksi via MutationObserver saat DOM berubah
            const observer = new MutationObserver(() => {
                setTimeout(scrollToFirstError, 400);
            });

            function startObserver() {
                if (document.body) {
                    observer.observe(document.body, { childList: true, subtree: true });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', startObserver);
            } else {
                startObserver();
            }

            // 2. Fallback saat Livewire selesai render
            document.addEventListener('livewire:updated', () => {
                setTimeout(scrollToFirstError, 500);
            });

            // 3. Fallback saat tombol submit diklik (polling berkala)
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('button[type="submit"], input[type="submit"]');
                if (!btn) return;
                [300, 700, 1200, 1800].forEach(d => setTimeout(scrollToFirstError, d));
            });

            // 4. Scroll saat halaman pertama kali load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => setTimeout(scrollToFirstError, 800));
            } else {
                setTimeout(scrollToFirstError, 800);
            }
        })();
    </script>
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .sidebar-bg { background-color: #1C2B39; }
        .sidebar-hover:hover { background-color: #243647; }
        .sidebar-active { background-color: #1A6FAA; }
        .content-bg { background-color: #F8FAFC; }

        /* Sortable drag styles */
        .sortable-ghost {
            opacity: 0.35;
            background-color: #E0F2FE !important;
            border: 2px dashed #1A6FAA !important;
            box-shadow: none !important;
        }
        .sortable-drag {
            opacity: 0.95;
            background-color: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 4px 10px -5px rgba(0, 0, 0, 0.1);
            transform: scale(1.02);
            border-radius: 0.75rem;
            cursor: grabbing;
        }
        .sortable-item {
            cursor: grab;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .sortable-item:active {
            cursor: grabbing;
        }
        .sortable-item:hover {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        /* Sidebar collapsed state - controlled via parent class, not Alpine on <a> */
        .sidebar-is-collapsed .menu-link { justify-content: center !important; padding-left: 0 !important; padding-right: 0 !important; }
        .sidebar-is-collapsed .menu-icon-wrap { margin-right: 0 !important; }
        .sidebar-is-collapsed .menu-label { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <x-admin.sidebar />

        <!-- Mobile sidebar overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-10 hidden lg:hidden" onclick="document.getElementById('sidebar').classList.add('hidden');document.getElementById('sidebar').classList.remove('flex');document.getElementById('sidebar-overlay').classList.add('hidden');"></div>

        <!-- Main Content -->
        <div class="flex-1 lg:ml-[var(--sidebar-width)] transition-[margin] duration-300">
            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 sticky top-0 z-10">
                <div class="flex items-center">
                    <button class="lg:hidden mr-4 text-gray-600" onclick="document.getElementById('sidebar').classList.remove('hidden');document.getElementById('sidebar').classList.add('flex');document.getElementById('sidebar-overlay').classList.remove('hidden');">
                        &#9776;
                    </button>
                    <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                </div>
                <div class="flex items-center space-x-4" x-data="{ open: false }">
                    <div class="relative" @click.outside="open = false">
                        <button @click="open = !open" class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 focus:outline-none">
                            @if(auth()->user()->avatar)
                                <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover border border-gray-200">
                            @else
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold" style="background-color: #1A6FAA;">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                            <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden z-50"
                             style="display: none;"
                             @click="open = false">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('admin.profil-pengguna') }}" wire:navigate class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Profil
                                </a>
                                <form method="POST" action="{{ route('logout') }}" class="block">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors text-left">
                                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="content-bg min-h-[calc(100vh-64px)] p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
