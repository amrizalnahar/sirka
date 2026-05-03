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

    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
            <div>
                <a href="{{ route('admin.laporan') }}" class="text-sm text-gray-500 hover:text-[#1A6FAA]">← Kembali ke Daftar</a>
                <h1 class="text-xl font-bold text-gray-800 mt-1">{{ $laporan->judul_laporan }}</h1>
            </div>
            @php
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
                $statusLabel = $statusLabels[$laporan->status][0] ?? $laporan->status;
            @endphp
            <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium {{ $badgeColor[0] }} {{ $badgeColor[1] }}">
                {{ $statusLabel }}
            </span>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Kode Laporan</span>
                    <p class="font-medium text-gray-800">{{ $laporan->kode_laporan }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Departemen</span>
                    <p class="font-medium text-gray-800">{{ $laporan->departemen?->name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Jenis Laporan</span>
                    <p class="font-medium text-gray-800">{{ $laporan->jenisLaporan?->nama ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Periode</span>
                    <p class="font-medium text-gray-800">{{ \Carbon\Carbon::create()->month($laporan->periode_bulan)->translatedFormat('F') }} {{ $laporan->periode_tahun }}</p>
                </div>
                <div>
                    <span class="text-gray-500">PIC</span>
                    <p class="font-medium text-gray-800">{{ $laporan->creator?->name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Approver Lv.1</span>
                    <p class="font-medium text-gray-800">{{ $chain?->approverLevel1?->name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Approver Lv.2</span>
                    <p class="font-medium text-gray-800">{{ $chain?->approverLevel2?->name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Tanggal Submit</span>
                    <p class="font-medium text-gray-800">{{ $laporan->submitted_at?->translatedFormat('d F Y H:i') ?? '-' }}</p>
                </div>
            </div>

            @if($laporan->catatan_pic)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <span class="text-gray-500 text-sm">Catatan PIC</span>
                    <p class="text-gray-700 text-sm mt-1">{{ $laporan->catatan_pic }}</p>
                </div>
            @endif

            @if($laporan->status === 'revision')
                @php
                    $lastRevision = $laporan->approvalLogs->firstWhere('action', 'request_revision');
                @endphp
                @if($lastRevision)
                    <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-amber-800">Permintaan Revisi</p>
                                <p class="text-sm text-amber-700 mt-0.5">Dari: {{ $lastRevision->user?->name ?? '-' }} ({{ $lastRevision->created_at->translatedFormat('d F Y H:i') }})</p>
                                <p class="text-sm text-amber-700 mt-1">{{ $lastRevision->catatan }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap gap-2 mb-6">
        @if($isPic && in_array($laporan->status, ['draft', 'revision']))
            <button type="button" wire:click="openSubmitModal" class="px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors">
                {{ $laporan->status === 'revision' ? 'Ajukan Kembali' : 'Ajukan untuk Approval' }}
            </button>
        @endif

        @if($isApprover && $laporan->status === 'submitted')
            <button type="button" wire:click="openApproveModal(1)" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Setujui (Lv.1)
            </button>
            <button type="button" wire:click="openRevisionModal" class="px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-600 transition-colors">
                Minta Revisi
            </button>
            <button type="button" wire:click="openRejectModal" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                Tolak Laporan
            </button>
        @endif

        @if($isApprover && $laporan->status === 'approved_1')
            <button type="button" wire:click="openApproveModal(2)" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Setujui Final (Lv.2)
            </button>
            <button type="button" wire:click="openRevisionModal" class="px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-600 transition-colors">
                Minta Revisi
            </button>
            <button type="button" wire:click="openRejectModal" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                Tolak Laporan
            </button>
        @endif

        @if($isPic && $laporan->status === 'revision')
            <button type="button" wire:click="openReimportModal" class="px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors">
                Import Ulang
            </button>
        @endif
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <div class="flex">
                <button wire:click="setTab('items')" class="px-5 py-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'items' ? 'border-[#1A6FAA] text-[#1A6FAA]' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Item Kegiatan ({{ $laporan->items->count() }})
                </button>
                <button wire:click="setTab('riwayat')" class="px-5 py-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'riwayat' ? 'border-[#1A6FAA] text-[#1A6FAA]' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    Riwayat Approval
                </button>
            </div>
        </div>

        <!-- Tab: Items -->
        @if($activeTab === 'items')
            <div class="overflow-x-auto p-0">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600 font-medium border-b border-gray-200">
                        <tr>
                            <th class="px-5 py-3">Kode</th>
                            <th class="px-5 py-3">Nama Kegiatan</th>
                            <th class="px-5 py-3">Akun</th>
                            <th class="px-5 py-3">Kategori</th>
                            <th class="px-5 py-3 text-right">Vol Rencana</th>
                            <th class="px-5 py-3 text-right">Vol Realisasi</th>
                            <th class="px-5 py-3 text-right">Pagu</th>
                            <th class="px-5 py-3 text-right">Realisasi</th>
                            <th class="px-5 py-3 text-right">%</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($laporan->items as $item)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 font-medium text-gray-800">{{ $item->kode_kegiatan }}</td>
                                <td class="px-5 py-3.5 text-gray-700">{{ $item->nama_kegiatan }}</td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $item->kode_akun }}</td>
                                <td class="px-5 py-3.5 text-gray-600">{{ $item->kode_kategori }}</td>
                                <td class="px-5 py-3.5 text-right text-gray-700">{{ number_format($item->volume_rencana, 2) }}</td>
                                <td class="px-5 py-3.5 text-right text-gray-700">{{ number_format($item->volume_realisasi, 2) }}</td>
                                <td class="px-5 py-3.5 text-right text-gray-700">{{ number_format($item->pagu_anggaran, 2) }}</td>
                                <td class="px-5 py-3.5 text-right text-gray-700">{{ number_format($item->realisasi_anggaran, 2) }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    @if($item->persen_realisasi_anggaran > 100)
                                        <span class="text-red-600 font-medium">{{ number_format($item->persen_realisasi_anggaran, 2) }}%</span>
                                    @else
                                        <span class="text-green-600 font-medium">{{ number_format($item->persen_realisasi_anggaran, 2) }}%</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    @php
                                        $skColor = match($item->status_kegiatan) {
                                            'selesai' => ['bg-green-100', 'text-green-700'],
                                            'berlangsung' => ['bg-blue-100', 'text-blue-700'],
                                            'belum_dimulai' => ['bg-gray-100', 'text-gray-600'],
                                        };
                                        $skLabel = match($item->status_kegiatan) {
                                            'selesai' => 'Selesai',
                                            'berlangsung' => 'Berlangsung',
                                            'belum_dimulai' => 'Belum Dimulai',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $skColor[0] }} {{ $skColor[1] }}">
                                        {{ $skLabel }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-5 py-8 text-center text-gray-500">Tidak ada item kegiatan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Tab: Riwayat -->
        @if($activeTab === 'riwayat')
            <div class="p-5">
                @if($laporan->approvalLogs->isEmpty())
                    <p class="text-gray-500 text-center py-8">Belum ada riwayat approval.</p>
                @else
                    <div class="relative border-l-2 border-gray-200 ml-3 space-y-6">
                        @foreach($laporan->approvalLogs as $log)
                            @php
                                $iconColor = match($log->action) {
                                    'submit' => 'bg-blue-500',
                                    'approve' => 'bg-green-500',
                                    'request_revision' => 'bg-amber-500',
                                    'reject' => 'bg-red-500',
                                    default => 'bg-gray-500',
                                };
                                $actionLabel = match($log->action) {
                                    'submit' => 'Diajukan',
                                    'approve' => $log->level === 1 ? 'Disetujui Lv.1' : 'Disetujui Final',
                                    'request_revision' => 'Diminta Revisi',
                                    'reject' => 'Ditolak',
                                    default => $log->action,
                                };
                                $actionTextColor = match($log->action) {
                                    'approve' => 'text-green-700',
                                    'request_revision' => 'text-amber-700',
                                    'reject' => 'text-red-700',
                                    default => 'text-blue-700',
                                };
                            @endphp
                            <div class="relative pl-6">
                                <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full {{ $iconColor }} border-2 border-white"></div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-sm font-medium text-gray-800">{{ $log->user?->name ?? 'System' }}</p>
                                        <span class="text-xs text-gray-500">{{ $log->created_at->translatedFormat('d F Y H:i') }}</span>
                                    </div>
                                    <p class="text-sm font-medium {{ $actionTextColor }}">
                                        {{ $actionLabel }}
                                    </p>
                                    @if($log->catatan)
                                        <p class="text-sm text-gray-600 mt-1">{{ $log->catatan }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Submit Modal -->
    @if($showSubmitModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('showSubmitModal', false)"></div>
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 z-10 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Ajukan Laporan</h3>
                <p class="text-sm text-gray-500 mb-4">Laporan akan diajukan ke Approver Level 1 untuk ditinjau.</p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
                    <textarea wire:model="catatan" rows="3" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showSubmitModal', false)" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                    <button wire:click="submit" class="px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a] transition-colors">Ajukan</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Approve Modal -->
    @if($showApproveModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('showApproveModal', false)"></div>
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 z-10 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $approveLevel === 1 ? 'Setujui Lv.1' : 'Setujui Final' }}</h3>
                <p class="text-sm text-gray-500 mb-4">{{ $approveLevel === 1 ? 'Laporan akan diteruskan ke Approver Level 2.' : 'Laporan akan diarsipkan setelah persetujuan final.' }}</p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
                    <textarea wire:model="catatan" rows="3" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showApproveModal', false)" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                    <button wire:click="approve" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">Setujui</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Revision Modal -->
    @if($showRevisionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('showRevisionModal', false)"></div>
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 z-10 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Minta Revisi</h3>
                <p class="text-sm text-gray-500 mb-4">PIC akan menerima notifikasi dengan catatan revisi Anda.</p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Revisi <span class="text-red-600">*</span></label>
                    <textarea wire:model="catatan" rows="4" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm" placeholder="Jelaskan apa yang perlu diperbaiki..."></textarea>
                    @error('catatan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showRevisionModal', false)" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                    <button wire:click="requestRevision" class="px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-600 transition-colors">Kirim Permintaan Revisi</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Reject Modal -->
    @if($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('showRejectModal', false)"></div>
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 z-10 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Tolak Laporan</h3>
                <p class="text-sm text-gray-500 mb-4">Laporan akan ditolak dan PIC harus membuat laporan baru.</p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan <span class="text-red-600">*</span></label>
                    <textarea wire:model="catatan" rows="4" class="w-full border-gray-300 focus:border-[#1A6FAA] focus:ring-[#1A6FAA] rounded-lg text-sm" placeholder="Jelaskan alasan penolakan..."></textarea>
                    @error('catatan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showRejectModal', false)" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
                    <button wire:click="reject" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">Tolak Laporan</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Re-import Modal -->
    @if($showReimportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('showReimportModal', false)"></div>
            <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full mx-4 z-10 p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Import Ulang Data</h3>
                <p class="text-sm text-gray-500 mb-4">Unggah file Excel yang sudah diperbaiki. Data lama akan diganti.</p>

                <div class="flex gap-3 mb-4">
                    <input type="file" wire:model="reimportFile" accept=".xlsx,.csv" class="flex-1 text-sm">
                    <button wire:click="parseReimportFile" class="px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg hover:bg-[#155a8a]">Preview</button>
                </div>
                @error('reimportFile') <p class="text-sm text-red-600 mb-3">{{ $message }}</p> @enderror

                @if(!empty($reimportPreview))
                    <div class="overflow-x-auto max-h-64 mb-4 border rounded-lg">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-gray-600 font-medium border-b">
                                <tr>
                                    <th class="px-3 py-2">#</th>
                                    <th class="px-3 py-2">Kode</th>
                                    <th class="px-3 py-2">Nama</th>
                                    <th class="px-3 py-2 text-right">Pagu</th>
                                    <th class="px-3 py-2 text-right">Realisasi</th>
                                    <th class="px-3 py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($reimportPreview as $row)
                                    @php $rowClass = $row['status'] === 'error' ? 'bg-red-50' : ($row['status'] === 'warning' ? 'bg-yellow-50' : ''); @endphp
                                    <tr class="{{ $rowClass }}">
                                        <td class="px-3 py-2">{{ $row['row_num'] }}</td>
                                        <td class="px-3 py-2">{{ $row['data']['kode_kegiatan'] }}</td>
                                        <td class="px-3 py-2">{{ $row['data']['nama_kegiatan'] }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['data']['pagu_anggaran'], 2) }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($row['data']['realisasi_anggaran'], 2) }}</td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $row['status'] === 'error' ? 'bg-red-100 text-red-700' : ($row['status'] === 'warning' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                                                {{ ucfirst($row['status']) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button wire:click="$set('showReimportModal', false)" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg">Batal</button>
                        <button wire:click="saveReimport" @disabled($reimportHasErrors) class="px-4 py-2 bg-[#1A6FAA] text-white text-sm font-medium rounded-lg disabled:opacity-50">Simpan Perubahan</button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
