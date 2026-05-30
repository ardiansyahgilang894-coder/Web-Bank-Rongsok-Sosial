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
         Schema::create('pengeluaran_kas', function (Blueprint $table) {

            $table->id();

            // TANGGAL PENGELUARAN
            $table->date('tanggal');

            // KEPERLUAN SOSIAL
            $table->string('keperluan');

            // DESKRIPSI
            $table->text('deskripsi')->nullable();

            // NOMINAL
            $table->bigInteger('nominal');

            // FOTO DOKUMENTASI
            $table->string('foto')->nullable();

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
        Schema::dropIfExists('pengeluaran_kas');
    }
};
