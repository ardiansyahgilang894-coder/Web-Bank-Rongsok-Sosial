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
        Schema::create('pemasukan_kas', function (Blueprint $table) {

            $table->id();

            // TANGGAL PENJUALAN
            $table->date('tanggal');

            // KETERANGAN
            $table->string('keterangan');

            // TOTAL HASIL PENJUALAN
            $table->bigInteger('nominal');

            // FOTO BUKTI
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
        Schema::dropIfExists('pemasukan_kas');
    }
};
