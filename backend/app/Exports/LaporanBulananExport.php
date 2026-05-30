<?php

namespace App\Exports;

use App\Models\PenjualanRongsok;
use App\Models\PemasukanKas;
use App\Models\PengeluaranKas;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LaporanBulananExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'Tahun',
            'Bulan',
            'Total Berat Rongsok',
            'Hasil Jual Rongsok',
            'Total Pemasukan',
            'Total Pengeluaran',
            'Sisa Saldo',
        ];
    }

    public function array(): array
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

        return $penjualan->map(function ($item) {
            $pemasukan = PemasukanKas::whereYear('tanggal', $item->tahun)
                ->whereMonth('tanggal', $item->bulan)
                ->sum('nominal');

            $pengeluaran = PengeluaranKas::whereYear('tanggal', $item->tahun)
                ->whereMonth('tanggal', $item->bulan)
                ->sum('nominal');

            return [
                $item->tahun,
                $item->bulan,
                $item->total_berat . ' Kg',
                $item->total_pendapatan,
                $pemasukan,
                $pengeluaran,
                $pemasukan - $pengeluaran,
            ];
        })->toArray();
    }
}