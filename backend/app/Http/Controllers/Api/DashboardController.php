<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PemasukanKas;
use App\Models\PengeluaranKas;
use App\Models\GaleriKegiatan;
use App\Models\PenjualanRongsok;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPemasukan = PemasukanKas::sum('nominal');
        $totalPengeluaran = PengeluaranKas::sum('nominal');

        $bulanLabels = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];

        $penjualanBulanan = PenjualanRongsok::select(
            DB::raw('MONTH(tanggal) as bulan'),
            DB::raw('SUM(total_pendapatan) as total_pendapatan'),
            DB::raw('SUM(total_berat) as total_berat')
        )
            ->whereYear('tanggal', date('Y'))
            ->groupBy(DB::raw('MONTH(tanggal)'))
            ->get()
            ->keyBy('bulan');

        $pemasukanBulanan = PemasukanKas::select(
            DB::raw('MONTH(tanggal) as bulan'),
            DB::raw('SUM(nominal) as total')
        )
            ->whereYear('tanggal', date('Y'))
            ->groupBy(DB::raw('MONTH(tanggal)'))
            ->get()
            ->keyBy('bulan');

        $pengeluaranBulanan = PengeluaranKas::select(
            DB::raw('MONTH(tanggal) as bulan'),
            DB::raw('SUM(nominal) as total')
        )
            ->whereYear('tanggal', date('Y'))
            ->groupBy(DB::raw('MONTH(tanggal)'))
            ->get()
            ->keyBy('bulan');

        $categories = [];
        $dataPenjualan = [];
        $dataBerat = [];
        $dataPemasukan = [];
        $dataPengeluaran = [];

        foreach ($bulanLabels as $angkaBulan => $namaBulan) {
            $categories[] = $namaBulan;

            $dataPenjualan[] = (int) optional($penjualanBulanan->get($angkaBulan))->total_pendapatan;
            $dataBerat[] = (float) optional($penjualanBulanan->get($angkaBulan))->total_berat;
            $dataPemasukan[] = (int) optional($pemasukanBulanan->get($angkaBulan))->total;
            $dataPengeluaran[] = (int) optional($pengeluaranBulanan->get($angkaBulan))->total;
        }

        return response()->json([
            'summary' => [
                'total_penjualan_rongsok' => PenjualanRongsok::sum('total_pendapatan'),
                'total_berat_rongsok' => PenjualanRongsok::sum('total_berat'),
                'total_pemasukan' => $totalPemasukan,
                'total_pengeluaran' => $totalPengeluaran,
                'saldo_kas' => $totalPemasukan - $totalPengeluaran,
                'total_galeri' => GaleriKegiatan::count(),
            ],

            'charts' => [
                'penjualan_rongsok' => [
                    'categories' => $categories,
                    'series' => [
                        [
                            'name' => 'Pendapatan Rongsok',
                            'data' => $dataPenjualan,
                        ],
                    ],
                ],

                'berat_rongsok' => [
                    'categories' => $categories,
                    'series' => [
                        [
                            'name' => 'Berat Rongsok',
                            'data' => $dataBerat,
                        ],
                    ],
                ],

                'kas_sosial' => [
                    'categories' => $categories,
                    'series' => [
                        [
                            'name' => 'Pemasukan',
                            'data' => $dataPemasukan,
                        ],
                        [
                            'name' => 'Pengeluaran',
                            'data' => $dataPengeluaran,
                        ],
                    ],
                ],
            ],

            'latest' => [
                'penjualan_rongsok' => PenjualanRongsok::latest()->take(5)->get(),
                'pemasukan_kas' => PemasukanKas::latest()->take(5)->get(),
                'pengeluaran_kas' => PengeluaranKas::latest()->take(5)->get(),
                'galeri_kegiatan' => GaleriKegiatan::latest('tanggal')->take(6)->get(),
            ],
        ]);
    }


    public function statistik()
    {
        $totalPemasukan = PemasukanKas::sum('nominal');
        $totalPengeluaran = PengeluaranKas::sum('nominal');

        return response()->json([
            'total_pemasukan' => $totalPemasukan,
            'total_pengeluaran' => $totalPengeluaran,
            'saldo_kas' => $totalPemasukan - $totalPengeluaran,
        ]);
    }

    public function publicDashboard()
    {
        $totalPemasukan = PemasukanKas::sum('nominal');
        $totalPengeluaran = PengeluaranKas::sum('nominal');

        return response()->json([
            'total_hasil_rongsok' => $totalPemasukan,
            'saldo_kas_sosial' => $totalPemasukan - $totalPengeluaran,
            'pengeluaran_sosial' => PengeluaranKas::latest()->get(),
            'galeri_kegiatan' => GaleriKegiatan::latest()->get(),
        ]);
    }
}
