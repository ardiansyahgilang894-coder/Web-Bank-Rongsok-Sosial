<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE galeri_kegiatan 
            MODIFY kategori ENUM(
                'pengambilan_rongsok',
                'penjualan_rongsok',
                'kegiatan_sosial',
                'penyaluran_dana'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE galeri_kegiatan 
            MODIFY kategori ENUM(
                'pengambilan_rongsok',
                'kegiatan_sosial'
            ) NOT NULL
        ");
    }
};
