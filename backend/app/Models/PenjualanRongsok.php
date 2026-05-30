<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PenjualanRongsok extends Model
{
    protected $table = 'penjualan_rongsok';

    protected $fillable = [
        'tanggal',
        'periode_mulai',
        'periode_selesai',
        'total_berat',
        'total_pendapatan',
        'tempat_jual',
        'keterangan',
        'foto_bukti',
        'sumber',
        'created_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
