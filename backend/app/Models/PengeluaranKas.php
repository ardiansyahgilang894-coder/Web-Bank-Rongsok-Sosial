<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengeluaranKas extends Model
{
    protected $table = 'pengeluaran_kas';

    protected $fillable = [
        'tanggal',
        'keperluan',
        'deskripsi',
        'nominal',
        'foto',
        'created_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
