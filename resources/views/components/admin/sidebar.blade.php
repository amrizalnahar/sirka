@php
$siteName = \App\Models\SiteSetting::getValue('site_name', config('app.name', 'Laravel'));
$user = auth()->user();

$menuSections = [];
$addSection = function($id, $label, $alwaysOpen, $items) use (&$menuSections) {
    $filtered = array_values(array_filter($items));
    if (count($filtered) > 0) {
        $menuSections[] = [
            'id' => $id,
            'label' => $label,
            'always_open' => $alwaysOpen,
            'items' => $filtered,
        ];
    }
};

$addSection('utama', 'Utama', true, [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard'), 'icon' => 'squares-2x2', 'active' => request()->routeIs('admin.dashboard')],
]);

$addSection('konten-publikasi', 'Konten Publikasi', false, [
    $user->can('posts-list') ? ['label' => 'Berita', 'url' => route('admin.berita'), 'icon' => 'newspaper', 'active' => request()->routeIs('admin.berita*')] : null,
    $user->can('pic-configs-list') ? ['label' => 'Konfigurasi PIC', 'url' => route('admin.konfigurasi-pic'), 'icon' => 'users', 'active' => request()->routeIs('admin.konfigurasi-pic')] : null,
]);

$addSection('laporan', 'Laporan', false, [
    $user->can('laporan-list') ? ['label' => 'Daftar Laporan', 'url' => route('admin.laporan'), 'icon' => 'document-text', 'active' => request()->routeIs('admin.laporan')] : null,
    $user->can('laporan-create') ? ['label' => 'Import Laporan', 'url' => route('admin.laporan.import'), 'icon' => 'arrow-up-tray', 'active' => request()->routeIs('admin.laporan.import')] : null,
    $user->can('laporan-approve') ? ['label' => 'Antrian Approval', 'url' => route('admin.laporan.approval-queue'), 'icon' => 'clipboard-document-check', 'active' => request()->routeIs('admin.laporan.approval-queue')] : null,
]);

$addSection('master-data', 'Master Data', false, [
    $user->can('categories-list') ? ['label' => 'Kategori', 'url' => route('admin.kategori'), 'icon' => 'tag', 'active' => request()->routeIs('admin.kategori')] : null,
    $user->can('tags-list') ? ['label' => 'Tags', 'url' => route('admin.tags'), 'icon' => 'bookmark-square', 'active' => request()->routeIs('admin.tags')] : null,
    $user->can('departements-list') ? ['label' => 'Departemen', 'url' => route('admin.departemen'), 'icon' => 'building-office', 'active' => request()->routeIs('admin.departemen')] : null,
    $user->can('jenis-laporan-list') ? ['label' => 'Jenis Laporan', 'url' => route('admin.jenis-laporan'), 'icon' => 'document-duplicate', 'active' => request()->routeIs('admin.jenis-laporan')] : null,
    $user->can('master-akun-list') ? ['label' => 'Master Akun', 'url' => route('admin.master-akun'), 'icon' => 'calculator', 'active' => request()->routeIs('admin.master-akun')] : null,
    $user->can('master-kategori-list') ? ['label' => 'Master Kategori', 'url' => route('admin.master-kategori'), 'icon' => 'folder-open', 'active' => request()->routeIs('admin.master-kategori')] : null,
    $user->can('approval-chain-list') ? ['label' => 'Approval Chain', 'url' => route('admin.approval-chain'), 'icon' => 'link', 'active' => request()->routeIs('admin.approval-chain')] : null,
]);

$addSection('manajemen-user', 'Manajemen User', false, [
    $user->can('users-list') ? ['label' => 'Users', 'url' => route('admin.users'), 'icon' => 'users', 'active' => request()->routeIs('admin.users*')] : null,
    $user->can('roles-list') ? ['label' => 'Roles', 'url' => route('admin.roles'), 'icon' => 'shield-check', 'active' => request()->routeIs('admin.roles')] : null,
]);

$addSection('konfigurasi', 'Konfigurasi', false, [
    $user->can('settings-list') ? ['label' => 'Pengaturan', 'url' => route('admin.pengaturan'), 'icon' => 'cog-6-tooth', 'active' => request()->routeIs('admin.pengaturan')] : null,
    $user->can('moderation-manage') ? ['label' => 'Moderasi Konten', 'url' => route('admin.moderasi'), 'icon' => 'shield-exclamation', 'active' => request()->routeIs('admin.moderasi')] : null,
]);

$addSection('monitoring', 'Monitoring', false, [
    $user->can('audit-logs-list') ? ['label' => 'Audit Log', 'url' => route('admin.audit-logs'), 'icon' => 'clipboard-document-check', 'active' => request()->routeIs('admin.audit-logs')] : null,
    $user->can('system-logs-list') ? ['label' => 'System Logs', 'url' => route('admin.system-logs'), 'icon' => 'document-text', 'active' => request()->routeIs('admin.system-logs')] : null,
    $user->can('system-email-tester') ? ['label' => 'Email Tester', 'url' => route('admin.email-tester'), 'icon' => 'envelope', 'active' => request()->routeIs('admin.email-tester')] : null,
    $user->can('system-queue-monitor') ? ['label' => 'Queue Monitor', 'url' => route('admin.queue-monitor'), 'icon' => 'queue-list', 'active' => request()->routeIs('admin.queue-monitor')] : null,
    $user->can('schedule-tasks-list') ? ['label' => 'Schedule Tasks', 'url' => route('admin.schedule-tasks'), 'icon' => 'clock', 'active' => request()->routeIs('admin.schedule-tasks')] : null,
]);
@endphp

<aside
    x-data="{
        collapsed: JSON.parse(localStorage.getItem('sidebar_collapsed') || 'false'),
        expandedSections: JSON.parse(localStorage.getItem('sidebar_expanded') || '[]'),
        search: '',
        searchFocused: false,

        init() {
            document.documentElement.style.setProperty('--sidebar-width', this.collapsed ? '64px' : '256px');

            document.addEventListener('keydown', (e) => {
                if (e.key === '/' && !this.searchFocused && !['INPUT', 'TEXTAREA', 'TRIX-EDITOR'].includes(document.activeElement.tagName)) {
                    e.preventDefault();
                    this.$refs.searchInput.focus();
                }
            });
        },

        toggleCollapse() {
            this.collapsed = !this.collapsed;
            document.documentElement.style.setProperty('--sidebar-width', this.collapsed ? '64px' : '256px');
            localStorage.setItem('sidebar_collapsed', JSON.stringify(this.collapsed));
        },

        toggleSection(id) {
            if (this.expandedSections.includes(id)) {
                this.expandedSections = this.expandedSections.filter(s => s !== id);
            } else {
                this.expandedSections.push(id);
            }
            localStorage.setItem('sidebar_expanded', JSON.stringify(this.expandedSections));
        },

        isExpanded(id) {
            if (this.search) return true;
            return this.expandedSections.includes(id);
        },

        isSectionActive(items) {
            return items.some(item => item.active);
        },

        get hasResults() {
            if (!this.search) return true;
            const items = document.querySelectorAll('[data-menu-item]');
            for (let item of items) {
                if (!item.classList.contains('hidden')) return true;
            }
            return false;
        }
    }"
    x-bind:class="{ 'sidebar-is-collapsed': collapsed }"
    class="sidebar-bg fixed h-full z-20 hidden lg:flex flex-col transition-all duration-300"
    style="width: var(--sidebar-width);"
    id="sidebar"
>
    <!-- Brand -->
    <div class="h-16 flex items-center px-4 border-b border-gray-700 shrink-0 overflow-hidden">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 min-w-0">
            <span class="text-white font-bold text-lg whitespace-nowrap" x-show="!collapsed" x-cloak>{{ $siteName }}</span>
            <span class="text-white font-bold text-lg" x-show="collapsed" x-cloak>{{ strtoupper(substr($siteName, 0, 1)) }}</span>
        </a>
    </div>

    <!-- Search -->
    <div x-show="!collapsed" x-cloak class="px-4 pt-3 pb-1 shrink-0">
        <div class="relative">
            <x-icon name="magnifying-glass" class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-500" />
            <input
                x-ref="searchInput"
                x-model="search"
                @focus="searchFocused = true"
                @blur="searchFocused = false"
                type="text"
                placeholder="Cari menu... (/)"
                class="w-full bg-[#243647] border border-gray-700 rounded-lg pl-8 pr-3 py-1.5 text-sm text-gray-300 placeholder-gray-500 focus:outline-none focus:border-[#1A6FAA] focus:ring-1 focus:ring-[#1A6FAA]"
            >
        </div>
    </div>

    <!-- Menu -->
    <nav class="flex-1 py-3 overflow-y-auto overflow-x-hidden no-scrollbar">
        @foreach($menuSections as $section)
            <div>
                @if($section['always_open'])
                    <div
                        x-show="!collapsed"
                        x-cloak
                        class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase"
                    >{{ $section['label'] }}</div>
                @else
                    @php
                    $sectionActive = collect($section['items'])->contains(fn($item) => $item['active']);
                    @endphp
                    <button
                        @click="toggleSection('{{ $section['id'] }}')"
                        class="w-full flex items-center justify-between px-4 py-2 text-xs font-semibold uppercase transition-colors duration-150 {{ $sectionActive ? 'text-[#1A6FAA]' : 'text-gray-400' }}"
                        x-show="!collapsed"
                        x-cloak
                    >
                        <span>{{ $section['label'] }}</span>
                        <div x-bind:class="{ 'rotate-180': isExpanded('{{ $section['id'] }}') }">
                            <x-icon name="chevron-down" class="w-3.5 h-3.5 transition-transform duration-200" />
                        </div>
                    </button>
                @endif

                <div
                    @if($section['always_open'])
                        class=""
                    @else
                        :class="isExpanded('{{ $section['id'] }}') ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
                        class="grid transition-[grid-template-rows] duration-200 ease-out"
                    @endif
                >
                    <div @class(['overflow-hidden' => !$section['always_open']])>
                        @foreach($section['items'] as $item)
                            <div
                                data-menu-item
                                class="relative group"
                                :class="search && !'{{ strtolower($item['label']) }}'.includes(search.toLowerCase()) ? 'hidden' : ''"
                            >
                                <a
                                    href="{{ $item['url'] }}"
                                    class="menu-link flex items-center py-2.5 text-sm transition-colors duration-150 px-6 {{ $item['active'] ? 'bg-[#1A6FAA] text-white relative before:absolute before:left-0 before:top-0 before:bottom-0 before:w-[3px] before:bg-[#1A6FAA]' : 'text-gray-300 hover:bg-[#243647]' }}"
                                >
                                    <div class="menu-icon-wrap mr-3">
                                        <x-icon name="{{ $item['icon'] }}" class="w-5 h-5" />
                                    </div>
                                    <span class="menu-label">{{ $item['label'] }}</span>
                                </a>
                                <!-- Tooltip (collapsed only) -->
                                <div
                                    x-show="collapsed"
                                    x-cloak
                                    class="absolute left-full top-1/2 -translate-y-1/2 ml-2 px-2.5 py-1.5 bg-gray-800 text-white text-xs rounded-md whitespace-nowrap z-50 opacity-0 group-hover:opacity-100 transition-opacity duration-150 pointer-events-none shadow-lg border border-gray-700"
                                >
                                    {{ $item['label'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach

        <!-- No Results -->
        <div x-show="search && !hasResults" x-cloak class="px-4 py-6 text-center text-sm text-gray-500">
            Tidak ada menu yang cocok
        </div>
    </nav>

    <!-- Bottom -->
    <div class="p-4 border-t border-gray-700 shrink-0">
        <div class="flex items-center" :class="collapsed ? 'justify-center' : 'justify-between'">
            <div x-show="!collapsed" x-cloak class="min-w-0">
                <div class="text-sm text-gray-300 truncate">{{ $user->name }}</div>
                <div class="text-xs text-gray-500 truncate">{{ $user->roles->pluck('name')->implode(', ') }}</div>
            </div>
            <button
                @click="toggleCollapse()"
                class="p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-[#243647] transition-colors"
                title="Toggle sidebar"
            >
                <x-icon name="chevron-left" x-show="!collapsed" x-cloak class="w-4 h-4" />
                <x-icon name="chevron-right" x-show="collapsed" x-cloak class="w-4 h-4" />
            </button>
        </div>
    </div>
</aside>
