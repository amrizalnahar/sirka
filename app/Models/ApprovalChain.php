<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalChain extends Model
{
    use HasFactory;

    protected $fillable = [
        'jenis_laporan_id',
        'departemen_id',
        'approver_level_1_id',
        'approver_level_2_id',
    ];

    public function jenisLaporan()
    {
        return $this->belongsTo(JenisLaporan::class);
    }

    public function departemen()
    {
        return $this->belongsTo(Departement::class);
    }

    public function approverLevel1()
    {
        return $this->belongsTo(User::class, 'approver_level_1_id');
    }

    public function approverLevel2()
    {
        return $this->belongsTo(User::class, 'approver_level_2_id');
    }
}
