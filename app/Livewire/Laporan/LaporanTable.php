<?php

namespace App\Livewire\Laporan;

use App\Models\ApprovalChain;
use App\Models\Laporan;
use App\Models\PicConfig;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class LaporanTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public int $perPage = 10;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $query = Laporan::with(['departemen', 'jenisLaporan', 'creator'])
            ->when($this->search, fn ($q) => $q->where(function ($sq) {
                $sq->where('judul_laporan', 'like', "%{$this->search}%")
                   ->orWhere('kode_laporan', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('created_at');

        // Role-based filtering
        if ($user->hasRole('super-admin')) {
            // Can see all
        } elseif ($this->isApprover($user, 1)) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('departemen.picConfigs', fn ($sq) => $sq->where('user_id', $user->id))
                  ->orWhere(function ($sq) use ($user) {
                      $sq->where('status', 'submitted')
                         ->whereHas('departemen.approvalChains', fn ($ssq) => $ssq->where('approver_level_1_id', $user->id));
                  });
            });
        } elseif ($this->isApprover($user, 2)) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('departemen.picConfigs', fn ($sq) => $sq->where('user_id', $user->id))
                  ->orWhere(function ($sq) use ($user) {
                      $sq->where('status', 'approved_1')
                         ->whereHas('departemen.approvalChains', fn ($ssq) => $ssq->where('approver_level_2_id', $user->id));
                  });
            });
        } else {
            // PIC or regular user — only their department's laporans
            $deptIds = PicConfig::where('user_id', $user->id)->pluck('departemen_id');
            $query->whereIn('departemen_id', $deptIds);
        }

        return view('livewire.laporan.laporan-table', [
            'laporans' => $query->paginate($this->perPage),
            'statuses' => [
                'draft' => 'Draft',
                'submitted' => 'Diajukan',
                'revision' => 'Revisi',
                'approved_1' => 'Disetujui Lv.1',
                'approved_2' => 'Disetujui Lv.2',
                'archived' => 'Diarsipkan',
                'rejected' => 'Ditolak',
            ],
        ]);
    }

    protected function isApprover($user, int $level): bool
    {
        $column = "approver_level_{$level}_id";
        return ApprovalChain::where($column, $user->id)->exists();
    }
}
