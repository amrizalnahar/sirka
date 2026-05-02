<div>
    <!-- Toast Notification -->
    <div x-data="{ show: false, message: '', type: 'success' }"
         x-on:notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 3000)"
         x-show="show" x-transition
         class="fixed top-5 right-5 z-50 px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium"
         :class="type === 'success' ? 'bg-green-600' : 'bg-red-600'"
         style="display: none;">
        <span x-text="message"></span>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h1 class="text-xl font-bold text-gray-800">Import Laporan Baru</h1>
        <a href="{{ route('admin.laporan.template') }}"
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            Unduh Template
        </a>
    </div>

    <!-- Form Metadata -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Informasi Laporan</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Laporan</label>
                <select wire:model="jenis_laporan_id" class="block w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                    <option value="">Pilih Jenis Laporan</option>
                    @foreach($jenisLaporans as $jl)
                        <option value="{{ $jl->id }}">{{ $jl->nama }}</option>
                    @endforeach
                </select>
                @error('jenis_laporan_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Laporan</label>
                <input type="text" wire:model="judul_laporan"
                       class="block w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm"
                       placeholder="Contoh: Laporan Realisasi Q1 2026">
                @error('judul_laporan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Periode Bulan</label>
                <select wire:model="periode_bulan" class="block w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                    @foreach(range(1, 12) as $bulan)
                        <option value="{{ $bulan }}">{{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }}</option>
                    @endforeach
                </select>
                @error('periode_bulan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Periode Tahun</label>
                <input type="number" wire:model="periode_tahun" min="2020" max="2099"
                       class="block w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm">
                @error('periode_tahun') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan PIC (opsional)</label>
                <textarea wire:model="catatan_pic" rows="2"
                          class="block w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm"
                          placeholder="Catatan pengantar laporan..."></textarea>
            </div>
        </div>
    </div>

    <!-- File Upload -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Unggah File Excel</h2>
        <div class="flex flex-col sm:flex-row gap-4 items-start">
            <div class="flex-1">
                <input type="file" wire:model="file" accept=".xlsx,.csv"
                       class="block w-full text-sm text-gray-500
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-lg file:border-0
                              file:text-sm file:font-medium
                              file:bg-[#1A6FAA] file:text-white
                              hover:file:bg-[#155a8a]
                              cursor-pointer">
                @error('file') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <button type="button" wire:click="parseFile" wire:loading.attr="disabled"
                    class="px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors disabled:opacity-50">
                <span wire:loading.remove wire:target="parseFile">Preview Data</span>
                <span wire:loading wire:target="parseFile">Memproses...</span>
            </button>
        </div>

        @if($parseError)
            <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-700">{{ $parseError }}</p>
            </div>
        @endif
    </div>

    <!-- Preview -->
    @if($showPreview)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                <h2 class="text-sm font-semibold text-gray-700">Preview Data</h2>
                <div class="flex gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Valid: {{ $summary['valid'] }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        Warning: {{ $summary['warnings'] }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Error: {{ $summary['errors'] }}
                    </span>
                </div>
            </div>

            @if($hasErrors)
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-700">Perbaiki {{ $summary['errors'] }} error sebelum melanjutkan.</p>
                </div>
            @elseif($summary['warnings'] > 0)
                <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-700">Terdapat {{ $summary['warnings'] }} warning. Anda tetap dapat menyimpan data.</p>
                </div>
            @endif

            <div class="overflow-x-auto max-h-96">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600 font-medium border-b border-gray-200 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 w-10">#</th>
                            <th class="px-3 py-2">Kode</th>
                            <th class="px-3 py-2">Nama Kegiatan</th>
                            <th class="px-3 py-2">Akun</th>
                            <th class="px-3 py-2">Kategori</th>
                            <th class="px-3 py-2 text-right">Vol Rencana</th>
                            <th class="px-3 py-2 text-right">Vol Realisasi</th>
                            <th class="px-3 py-2 text-right">Pagu</th>
                            <th class="px-3 py-2 text-right">Realisasi</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Indikator</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($preview as $row)
                            @php
                                $rowClass = match($row['status']) {
                                    'error' => 'bg-red-50',
                                    'warning' => 'bg-yellow-50',
                                    default => 'hover:bg-gray-50',
                                };
                                $statusBadge = match($row['status']) {
                                    'error' => ['bg-red-100', 'text-red-700', 'Error'],
                                    'warning' => ['bg-yellow-100', 'text-yellow-700', 'Warning'],
                                    default => ['bg-green-100', 'text-green-700', 'Valid'],
                                };
                            @endphp
                            <tr class="{{ $rowClass }} transition-colors" title="{{ implode('; ', array_merge($row['errors'], $row['warnings'])) }}">
                                <td class="px-3 py-2 text-gray-500">{{ $row['row_num'] }}</td>
                                <td class="px-3 py-2 @if(isset($row['errors']['kode_kegiatan'])) text-red-600 font-medium @endif">{{ $row['data']['kode_kegiatan'] }}</td>
                                <td class="px-3 py-2 @if(isset($row['errors']['nama_kegiatan'])) text-red-600 font-medium @endif">{{ $row['data']['nama_kegiatan'] }}</td>
                                <td class="px-3 py-2 @if(isset($row['errors']['kode_akun'])) text-red-600 font-medium @endif">{{ $row['data']['kode_akun'] }}</td>
                                <td class="px-3 py-2 @if(isset($row['errors']['kode_kategori'])) text-red-600 font-medium @endif">{{ $row['data']['kode_kategori'] }}</td>
                                <td class="px-3 py-2 text-right @if(isset($row['errors']['volume_rencana'])) text-red-600 font-medium @endif">{{ number_format($row['data']['volume_rencana'], 2) }}</td>
                                <td class="px-3 py-2 text-right @if(isset($row['errors']['volume_realisasi']) || isset($row['warnings']['volume_realisasi'])) text-yellow-700 font-medium @endif">{{ number_format($row['data']['volume_realisasi'], 2) }}</td>
                                <td class="px-3 py-2 text-right @if(isset($row['errors']['pagu_anggaran'])) text-red-600 font-medium @endif">{{ number_format($row['data']['pagu_anggaran'], 2) }}</td>
                                <td class="px-3 py-2 text-right @if(isset($row['errors']['realisasi_anggaran']) || isset($row['warnings']['realisasi_anggaran'])) text-yellow-700 font-medium @endif">{{ number_format($row['data']['realisasi_anggaran'], 2) }}</td>
                                <td class="px-3 py-2 @if(isset($row['errors']['status_kegiatan']) || isset($row['warnings']['status_kegiatan'])) text-red-600 font-medium @endif">{{ $row['data']['status_kegiatan'] }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadge[0] }} {{ $statusBadge[1] }}">
                                        {{ $statusBadge[2] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex justify-end gap-3">
                <button type="button" wire:click="$set('showPreview', false)" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Tutup Preview
                </button>
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                        @disabled($hasErrors)
                        class="px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="save">Simpan & Lanjutkan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </div>
    @endif
</div>
