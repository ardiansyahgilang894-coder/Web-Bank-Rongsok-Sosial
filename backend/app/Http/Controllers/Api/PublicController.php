<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GaleriKegiatan;
use App\Models\PemasukanKas;
use App\Models\PengeluaranKas;
use App\Models\PenjualanRongsok;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    public function transparansi()
    {
        $totalPemasukan = PemasukanKas::sum('nominal');
        $totalPengeluaran = PengeluaranKas::sum('nominal');

        return response()->json([
            'summary' => [
                'total_hasil_jual_rongsok' => PenjualanRongsok::sum('total_pendapatan'),
                'total_berat_rongsok' => PenjualanRongsok::sum('total_berat'),
                'total_pemasukan' => $totalPemasukan,
                'total_pengeluaran' => $totalPengeluaran,
                'saldo_kas' => $totalPemasukan - $totalPengeluaran,
                'total_kegiatan' => GaleriKegiatan::count(),
            ],

            'pemasukan_terbaru' => PemasukanKas::latest()
                ->take(5)
                ->get(),

            'nominal' => PenjualanRongsok::latest()
                ->take(5)
                ->get(),

            'pengeluaran_terbaru' => PengeluaranKas::latest()
                ->take(5)
                ->get(),

            'galeri_terbaru' => GaleriKegiatan::latest()
                ->take(6)
                ->get(),

            'laporan_bulanan' => PenjualanRongsok::select(
                DB::raw('YEAR(tanggal) as tahun'),
                DB::raw('MONTH(tanggal) as bulan'),
                DB::raw('SUM(total_berat) as total_berat'),
                DB::raw('SUM(total_pendapatan) as total_pendapatan')
            )
                ->groupBy('tahun', 'bulan')
                ->orderBy('tahun', 'desc')
                ->orderBy('bulan', 'desc')
                ->take(12)
                ->get()
                ->reverse()
                ->values(),
        ]);
    }
}
