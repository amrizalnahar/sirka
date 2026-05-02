<div x-data @download-log.window="
        const blob = new Blob([$event.detail.content], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'laravel-' + new Date().toISOString().slice(0,10) + '.log';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    ">
    <!-- Toast -->
    <div x-data="{ show: false, message: '', type: 'success' }"
         x-on:notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 3000)"
         x-show="show" x-transition
         class="fixed top-5 right-5 z-50 px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium"
         :class="type === 'success' ? 'bg-green-600' : 'bg-red-600'"
         style="display: none;">
        <span x-text="message"></span>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h1 class="text-xl font-bold text-gray-800">System Logs</h1>
        <div class="flex gap-2">
            @can('system-logs-list')
            <button wire:click="download" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Download Log
            </button>
            <button wire:click="clear" wire:confirm="Yakin ingin mengosongkan log?" class="px-4 py-2 bg-red-50 text-red-600 text-sm font-medium rounded-lg hover:bg-red-100 transition-colors">
                Clear Log
            </button>
            @endcan
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <input wire:model.live="search" type="text" placeholder="Cari teks log..."
                   class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
        </div>
        <div class="sm:w-40">
            <select wire:model.live="levelFilter" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                <option value="">Semua Level</option>
                <option value="ERROR">Error</option>
                <option value="WARNING">Warning</option>
                <option value="INFO">Info</option>
                <option value="DEBUG">Debug</option>
            </select>
        </div>
    </div>

    <!-- Log Entries -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 text-gray-600 font-medium border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 w-40">Waktu</th>
                        <th class="px-4 py-3 w-20">Level</th>
                        <th class="px-4 py-3">Pesan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($entries as $entry)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap font-mono">{{ $entry['datetime'] }}</td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wide
                                    @if($entry['level'] === 'ERROR') bg-red-100 text-red-700
                                    @elseif($entry['level'] === 'WARNING') bg-amber-100 text-amber-700
                                    @elseif($entry['level'] === 'INFO') bg-blue-100 text-blue-700
                                    @elseif($entry['level'] === 'DEBUG') bg-gray-100 text-gray-600
                                    @else bg-gray-100 text-gray-600 @endif">
                                    {{ $entry['level'] }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-gray-700 font-mono whitespace-pre-wrap break-all">{{ $entry['message'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-8 text-center text-gray-500">Tidak ada log.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($totalPages > 1)
            <div class="px-5 py-3 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <span class="text-sm text-gray-500">Halaman {{ $page }} dari {{ $totalPages }} ({{ $total }} baris)</span>
                <div class="flex items-center gap-1.5">
                    <button wire:click="previousPage" @disabled($page <= 1) class="px-2.5 py-1.5 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition-colors disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    @foreach($paginationRange as $p)
                        @if(is_null($p))
                            <span class="px-2 text-sm text-gray-400">...</span>
                        @else
                            <button wire:click="goToPage({{ $p }})" class="px-3 py-1.5 text-sm rounded-lg transition-colors {{ $page === $p ? 'bg-[#1A6FAA] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                {{ $p }}
                            </button>
                        @endif
                    @endforeach

                    <button wire:click="nextPage" @disabled($page >= $totalPages) class="px-2.5 py-1.5 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition-colors disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
