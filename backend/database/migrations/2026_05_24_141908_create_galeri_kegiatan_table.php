<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('galeri_kegiatan', function (Blueprint $table) {

            $table->id();

            // JUDUL
            $table->string('judul');

            // DESKRIPSI
            $table->text('deskripsi')->nullable();

            // FOTO
            $table->string('foto');

            // KATEGORI
            $table->enum('kategori', [
                'pengambilan_rongsok',
                'kegiatan_sosial'
            ]);

            // TANGGAL
            $table->date('tanggal');

            // USER INPUT
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galeri_kegiatans');
    }
};
