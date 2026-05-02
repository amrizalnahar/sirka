<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PicConfig extends Model
{
    use HasFactory;

    protected $fillable = ['departemen_id', 'jenis_laporan_id', 'user_id', 'email'];

    public function departemen()
    {
        return $this->belongsTo(Departement::class, 'departemen_id');
    }

    public function jenisLaporan()
    {
        return $this->belongsTo(JenisLaporan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
