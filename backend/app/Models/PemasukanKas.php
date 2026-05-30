<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PemasukanKas extends Model
{
    protected $table = 'pemasukan_kas';

    protected $fillable = [
        'penjualan_rongsok_id',
        'tanggal',
        'sumber',
        'keterangan',
        'nominal',
        'foto',
        'created_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
