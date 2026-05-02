<div>
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
        <h1 class="text-xl font-bold text-gray-800">Daftar Laporan</h1>
        @can('laporan-create')
        <a href="{{ route('admin.laporan.import') }}"
           class="inline-flex items-center px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Import Laporan Baru
        </a>
        @endcan
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <input wire:model.live="search" type="text" placeholder="Cari kode atau judul..."
                   class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
        </div>
        <div class="sm:w-48">
            <select wire:model.live="statusFilter"
                    class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                <option value="">Semua Status</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:w-32">
            <select wire:model.live="perPage"
                    class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                <option value="10">10 / hal</option>
                <option value="25">25 / hal</option>
                <option value="50">50 / hal</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 font-medium border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3">Kode Laporan</th>
                        <th class="px-5 py-3">Judul</th>
                        <th class="px-5 py-3">Departemen</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($laporans as $laporan)
                        @php
                            $statusBadge = $statuses[$laporan->status] ?? ['Unknown', 'bg-gray-100', 'text-gray-600'];
                            $badgeColor = match($laporan->status) {
                                'draft' => ['bg-gray-100', 'text-gray-700'],
                                'submitted' => ['bg-blue-100', 'text-blue-700'],
                                'revision' => ['bg-amber-100', 'text-amber-700'],
                                'approved_1' => ['bg-purple-100', 'text-purple-700'],
                                'approved_2' => ['bg-indigo-100', 'text-indigo-700'],
                                'archived' => ['bg-green-100', 'text-green-700'],
                                'rejected' => ['bg-red-100', 'text-red-700'],
                                default => ['bg-gray-100', 'text-gray-600'],
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3.5 font-medium text-gray-800">{{ $laporan->kode_laporan }}</td>
                            <td class="px-5 py-3.5 text-gray-700">{{ $laporan->judul_laporan }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $laporan->departemen?->name ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-gray-600">
                                {{ \Carbon\Carbon::create()->month($laporan->periode_bulan)->translatedFormat('F') }} {{ $laporan->periode_tahun }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeColor[0] }} {{ $badgeColor[1] }}">
                                    {{ $statuses[$laporan->status] ?? $laporan->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('admin.laporan.detail', $laporan) }}"
                                   class="inline-flex items-center px-3 py-1.5 text-sm text-[#1A6FAA] hover:bg-blue-50 rounded-md transition-colors border border-[#1A6FAA]">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-500">Tidak ada data laporan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-gray-200">
            {{ $laporans->links() }}
        </div>
    </div>
</div>
