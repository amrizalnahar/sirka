<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'laporan_id',
        'kode_kegiatan',
        'nama_kegiatan',
        'kode_akun',
        'kode_kategori',
        'satuan',
        'volume_rencana',
        'volume_realisasi',
        'pagu_anggaran',
        'realisasi_anggaran',
        'tanggal_mulai',
        'tanggal_selesai',
        'status_kegiatan',
        'keterangan',
    ];

    protected $casts = [
        'volume_rencana' => 'decimal:2',
        'volume_realisasi' => 'decimal:2',
        'pagu_anggaran' => 'decimal:2',
        'realisasi_anggaran' => 'decimal:2',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function laporan()
    {
        return $this->belongsTo(Laporan::class);
    }
}
