<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenjualanRongsok;
use App\Models\PemasukanKas;
use App\Models\PengeluaranKas;
use Illuminate\Support\Facades\DB;
use App\Exports\LaporanBulananExport;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function bulanan()
    {
        $penjualan = PenjualanRongsok::select(
            DB::raw('YEAR(tanggal) as tahun'),
            DB::raw('MONTH(tanggal) as bulan'),
            DB::raw('SUM(total_berat) as total_berat'),
            DB::raw('SUM(total_pendapatan) as total_pendapatan')
        )
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        $pemasukan = PemasukanKas::select(
            DB::raw('YEAR(tanggal) as tahun'),
            DB::raw('MONTH(tanggal) as bulan'),
            DB::raw('SUM(nominal) as total_pemasukan')
        )
            ->groupBy('tahun', 'bulan')
            ->get();

        $pengeluaran = PengeluaranKas::select(
            DB::raw('YEAR(tanggal) as tahun'),
            DB::raw('MONTH(tanggal) as bulan'),
            DB::raw('SUM(nominal) as total_pengeluaran')
        )
            ->groupBy('tahun', 'bulan')
            ->get();

        return response()->json([
            'penjualan_rongsok' => $penjualan,
            'pemasukan_kas' => $pemasukan,
            'pengeluaran_kas' => $pengeluaran,
        ]);
    }

    public function exportExcel()
    {
        return Excel::download(
            new LaporanBulananExport,
            'laporan-bulanan-bank-rongsok.xlsx'
        );
    }
}
