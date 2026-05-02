<?php

namespace App\Models;

use App\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laporan extends Model
{
    use HasFactory, SoftDeletes, HasAuditTrail;

    protected $fillable = [
        'kode_laporan',
        'judul_laporan',
        'departemen_id',
        'jenis_laporan_id',
        'periode_bulan',
        'periode_tahun',
        'status',
        'catatan_pic',
        'submitted_at',
        'created_by',
        'revision_count',
    ];

    protected $casts = [
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
        'submitted_at' => 'datetime',
        'revision_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function ($laporan) {
            if (empty($laporan->kode_laporan)) {
                $laporan->kode_laporan = static::generateKodeLaporan($laporan);
            }
        });
    }

    protected static function generateKodeLaporan(self $laporan): string
    {
        $departemen = Departement::find($laporan->departemen_id);
        $deptCode = strtoupper(str_replace(' ', '-', $departemen?->name ?? 'UNKNOWN'));
        $period = sprintf('%04d%02d', $laporan->periode_tahun, $laporan->periode_bulan);

        $last = static::where('kode_laporan', 'like', "LPR-{$deptCode}-{$period}-%")
            ->withTrashed()
            ->orderBy('id', 'desc')
            ->first();

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last->kode_laporan);
            $lastSeq = (int) end($parts);
            $seq = $lastSeq + 1;
        }

        return sprintf('LPR-%s-%s-%04d', $deptCode, $period, $seq);
    }

    public function departemen()
    {
        return $this->belongsTo(Departement::class);
    }

    public function jenisLaporan()
    {
        return $this->belongsTo(JenisLaporan::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(LaporanItem::class);
    }

    public function approvalLogs()
    {
        return $this->hasMany(LaporanApprovalLog::class)->orderBy('created_at', 'desc');
    }

    public function scopeForUser($query, User $user)
    {
        $deptIds = PicConfig::where('user_id', $user->id)->pluck('departemen_id');
        return $query->whereIn('departemen_id', $deptIds);
    }

    public function scopeForApprover($query, User $user, int $level)
    {
        $chainIds = ApprovalChain::where("approver_level_{$level}_id", $user->id)
            ->select('jenis_laporan_id', 'departemen_id')
            ->get();

        return $query->where(function ($q) use ($chainIds) {
            foreach ($chainIds as $chain) {
                $q->orWhere(function ($sq) use ($chain) {
                    $sq->where('jenis_laporan_id', $chain->jenis_laporan_id)
                       ->where('departemen_id', $chain->departemen_id);
                });
            }
        });
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeRevision($query)
    {
        return $query->where('status', 'revision');
    }

    public function scopeApproved1($query)
    {
        return $query->where('status', 'approved_1');
    }

    public function scopePendingApproval($query, User $user, int $level)
    {
        $status = $level === 1 ? 'submitted' : 'approved_1';
        return $query->where('status', $status)->forApprover($user, $level);
    }

    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'revision']);
    }

    public function isPendingApproval(): bool
    {
        return in_array($this->status, ['submitted', 'approved_1']);
    }
}
