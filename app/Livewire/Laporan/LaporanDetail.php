<?php

namespace App\Livewire\Laporan;

use App\Models\ApprovalChain;
use App\Models\Laporan;
use App\Models\LaporanApprovalLog;
use App\Models\LaporanItem;
use App\Models\PicConfig;
use App\Services\LaporanImportService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class LaporanDetail extends Component
{
    use WithFileUploads;

    public Laporan $laporan;
    public string $activeTab = 'items';

    // Action modals
    public bool $showSubmitModal = false;
    public bool $showApproveModal = false;
    public bool $showRevisionModal = false;
    public bool $showRejectModal = false;
    public bool $showReimportModal = false;

    public string $catatan = '';
    public int $approveLevel = 0;
    public int $maxRevisions = 3;

    // Re-import
    public $reimportFile = null;
    public array $reimportPreview = [];
    public bool $reimportHasErrors = false;

    protected function rules(): array
    {
        return [
            'catatan' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function mount(Laporan $laporan): void
    {
        $this->laporan = $laporan;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ─── SUBMIT ───
    public function openSubmitModal(): void
    {
        $this->catatan = '';
        $this->showSubmitModal = true;
    }

    public function submit(): void
    {
        $this->authorizeAction('submit');

        $chain = $this->getApprovalChain();
        if (! $chain) {
            $this->dispatch('notify', type: 'error', message: 'Rantai approval belum dikonfigurasi. Hubungi admin.');
            return;
        }

        DB::transaction(function () {
            $this->laporan->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'catatan_pic' => $this->catatan ?: null,
            ]);

            LaporanApprovalLog::create([
                'laporan_id' => $this->laporan->id,
                'user_id' => auth()->id(),
                'action' => 'submit',
                'catatan' => $this->catatan ?: null,
            ]);
        });

        $this->showSubmitModal = false;
        $this->laporan->refresh();
        $this->dispatch('notify', type: 'success', message: 'Laporan berhasil diajukan untuk approval.');
    }

    // ─── APPROVE ───
    public function openApproveModal(int $level): void
    {
        $this->approveLevel = $level;
        $this->catatan = '';
        $this->showApproveModal = true;
    }

    public function approve(): void
    {
        $this->authorizeAction('approve');

        $newStatus = $this->approveLevel === 1 ? 'approved_1' : 'approved_2';

        DB::transaction(function () use ($newStatus) {
            $this->laporan->update(['status' => $newStatus]);

            LaporanApprovalLog::create([
                'laporan_id' => $this->laporan->id,
                'user_id' => auth()->id(),
                'action' => 'approve',
                'level' => $this->approveLevel,
                'catatan' => $this->catatan ?: null,
            ]);

            // Auto-archive after L2 approval
            if ($this->approveLevel === 2) {
                $this->laporan->update(['status' => 'archived']);
            }
        });

        $this->showApproveModal = false;
        $this->laporan->refresh();
        $msg = $this->approveLevel === 1 ? 'Laporan disetujui Level 1.' : 'Laporan disetujui final dan diarsipkan.';
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    // ─── REQUEST REVISION ───
    public function openRevisionModal(): void
    {
        $this->catatan = '';
        $this->showRevisionModal = true;
    }

    public function requestRevision(): void
    {
        $this->validate([
            'catatan' => ['required', 'string', 'max:2000'],
        ], [
            'catatan.required' => 'Catatan revisi wajib diisi.',
        ]);

        $this->authorizeAction('request_revision');

        $level = $this->getApproverLevel();

        if ($this->laporan->revision_count >= $this->maxRevisions) {
            $this->dispatch('notify', type: 'warning', message: "Laporan sudah melewati batas revisi ({$this->maxRevisions}x). Pertimbangkan untuk menolak laporan.");
            return;
        }

        DB::transaction(function () use ($level) {
            $this->laporan->update([
                'status' => 'revision',
                'revision_count' => $this->laporan->revision_count + 1,
            ]);

            LaporanApprovalLog::create([
                'laporan_id' => $this->laporan->id,
                'user_id' => auth()->id(),
                'action' => 'request_revision',
                'level' => $level,
                'catatan' => $this->catatan,
            ]);
        });

        $this->showRevisionModal = false;
        $this->laporan->refresh();
        $this->dispatch('notify', type: 'success', message: 'Permintaan revisi berhasil dikirim ke PIC.');
    }

    // ─── REJECT ───
    public function openRejectModal(): void
    {
        $this->catatan = '';
        $this->showRejectModal = true;
    }

    public function reject(): void
    {
        $this->validate([
            'catatan' => ['required', 'string', 'max:2000'],
        ], [
            'catatan.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $this->authorizeAction('reject');
        $level = $this->getApproverLevel();

        DB::transaction(function () use ($level) {
            $this->laporan->update(['status' => 'rejected']);

            LaporanApprovalLog::create([
                'laporan_id' => $this->laporan->id,
                'user_id' => auth()->id(),
                'action' => 'reject',
                'level' => $level,
                'catatan' => $this->catatan,
            ]);
        });

        $this->showRejectModal = false;
        $this->laporan->refresh();
        $this->dispatch('notify', type: 'success', message: 'Laporan ditolak.');
    }

    // ─── RE-IMPORT (REVISION) ───
    public function openReimportModal(): void
    {
        $this->reimportFile = null;
        $this->reimportPreview = [];
        $this->reimportHasErrors = false;
        $this->showReimportModal = true;
    }

    public function parseReimportFile(): void
    {
        $this->validate([
            'reimportFile' => ['required', 'file', 'mimes:xlsx,csv', 'max:5120'],
        ]);

        $service = new LaporanImportService();
        $service->setContext(
            $this->laporan->departemen_id,
            $this->laporan->periode_bulan,
            $this->laporan->periode_tahun
        );

        $result = $service->parse($this->reimportFile->getRealPath());

        if (! empty($result['errors'])) {
            $this->dispatch('notify', type: 'error', message: implode(', ', $result['errors']));
            return;
        }

        $this->reimportPreview = $result['rows'];
        $this->reimportHasErrors = $result['summary']['errors'] > 0;
    }

    public function saveReimport(): void
    {
        if ($this->reimportHasErrors || empty($this->reimportPreview)) {
            $this->dispatch('notify', type: 'error', message: 'Tidak dapat menyimpan karena terdapat error.');
            return;
        }

        if (! $this->isPic()) {
            $this->dispatch('notify', type: 'error', message: 'Anda bukan PIC untuk laporan ini.');
            return;
        }

        DB::transaction(function () {
            $this->laporan->items()->delete();

            foreach ($this->reimportPreview as $row) {
                if ($row['status'] === 'error') {
                    continue;
                }
                LaporanItem::create(array_merge(
                    ['laporan_id' => $this->laporan->id],
                    $row['data']
                ));
            }
        });

        $this->showReimportModal = false;
        $this->laporan->refresh();
        $this->dispatch('notify', type: 'success', message: 'Data laporan berhasil diperbarui.');
    }

    // ─── RE-SUBMIT AFTER REVISION ───
    public function resubmit(): void
    {
        if ($this->laporan->status !== 'revision') {
            $this->dispatch('notify', type: 'error', message: 'Laporan tidak dalam status revisi.');
            return;
        }

        if (! $this->isPic()) {
            $this->dispatch('notify', type: 'error', message: 'Anda bukan PIC untuk laporan ini.');
            return;
        }

        $chain = $this->getApprovalChain();
        if (! $chain) {
            $this->dispatch('notify', type: 'error', message: 'Rantai approval belum dikonfigurasi.');
            return;
        }

        DB::transaction(function () {
            $this->laporan->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            LaporanApprovalLog::create([
                'laporan_id' => $this->laporan->id,
                'user_id' => auth()->id(),
                'action' => 'submit',
                'catatan' => 'Pengajuan ulang setelah revisi.',
            ]);
        });

        $this->laporan->refresh();
        $this->dispatch('notify', type: 'success', message: 'Laporan berhasil diajukan ulang.');
    }

    // ─── AUTHORIZATION HELPERS ───
    protected function authorizeAction(string $action): void
    {
        $user = auth()->user();

        switch ($action) {
            case 'submit':
                if (! $this->isPic() || ! in_array($this->laporan->status, ['draft', 'revision'])) {
                    abort(403, 'Tidak diizinkan.');
                }
                break;

            case 'approve':
                $level = $this->approveLevel;
                $expectedStatus = $level === 1 ? 'submitted' : 'approved_1';
                if ($this->laporan->status !== $expectedStatus || ! $this->isApproverAtLevel($level)) {
                    abort(403, 'Tidak diizinkan.');
                }
                break;

            case 'request_revision':
            case 'reject':
                if (! $this->isCurrentApprover()) {
                    abort(403, 'Tidak diizinkan.');
                }
                break;
        }
    }

    protected function isPic(): bool
    {
        return PicConfig::where('user_id', auth()->id())
            ->where('departemen_id', $this->laporan->departemen_id)
            ->where('jenis_laporan_id', $this->laporan->jenis_laporan_id)
            ->exists();
    }

    protected function isApproverAtLevel(int $level): bool
    {
        $column = "approver_level_{$level}_id";
        return ApprovalChain::where('jenis_laporan_id', $this->laporan->jenis_laporan_id)
            ->where('departemen_id', $this->laporan->departemen_id)
            ->where($column, auth()->id())
            ->exists();
    }

    protected function isCurrentApprover(): bool
    {
        $user = auth()->user();

        if ($this->laporan->status === 'submitted') {
            return $this->isApproverAtLevel(1);
        }

        if ($this->laporan->status === 'approved_1') {
            return $this->isApproverAtLevel(2);
        }

        return false;
    }

    protected function getApproverLevel(): int
    {
        if ($this->isApproverAtLevel(1) && $this->laporan->status === 'submitted') {
            return 1;
        }
        if ($this->isApproverAtLevel(2) && $this->laporan->status === 'approved_1') {
            return 2;
        }
        return 0;
    }

    protected function getApprovalChain(): ?ApprovalChain
    {
        return ApprovalChain::where('jenis_laporan_id', $this->laporan->jenis_laporan_id)
            ->where('departemen_id', $this->laporan->departemen_id)
            ->first();
    }

    public function render()
    {
        return view('livewire.laporan.laporan-detail', [
            'isPic' => $this->isPic(),
            'isApprover' => $this->isCurrentApprover(),
            'approverLevel' => $this->getApproverLevel(),
            'chain' => $this->getApprovalChain(),
            'statusLabels' => [
                'draft' => ['Draft', 'bg-gray-100', 'text-gray-700'],
                'submitted' => ['Diajukan', 'bg-blue-100', 'text-blue-700'],
                'revision' => ['Revisi', 'bg-amber-100', 'text-amber-700'],
                'approved_1' => ['Disetujui Lv.1', 'bg-purple-100', 'text-purple-700'],
                'approved_2' => ['Disetujui Lv.2', 'bg-indigo-100', 'text-indigo-700'],
                'archived' => ['Diarsipkan', 'bg-green-100', 'text-green-700'],
                'rejected' => ['Ditolak', 'bg-red-100', 'text-red-700'],
            ],
        ]);
    }
}
