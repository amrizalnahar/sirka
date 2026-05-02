<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanApprovalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'laporan_id',
        'user_id',
        'action',
        'level',
        'catatan',
    ];

    protected $casts = [
        'level' => 'integer',
    ];

    public function laporan()
    {
        return $this->belongsTo(Laporan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
