<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGaleriKegiatanRequest;
use App\Http\Requests\UpdateGaleriKegiatanRequest;
use App\Models\GaleriKegiatan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\PenjualanRongsok;
use App\Models\PemasukanKas;
use App\Models\PengeluaranKas;

class GaleriKegiatanController extends Controller
{
    // LIST
    public function index()
    {
        $data = GaleriKegiatan::with('user')
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Data galeri kegiatan',
            'data' => $data
        ]);
    }

    // CREATE
    public function store(StoreGaleriKegiatanRequest $request)
    {
        // dd($request->all(), $request->file('foto'));

        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|in:pengambilan_rongsok,penjualan_rongsok,kegiatan_sosial,penyaluran_dana',
            'tanggal' => 'required|date',
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // UPLOAD FOTO
        $foto = $request->file('foto')
            ->store('galeri', 'public');

        // SIMPAN DATABASE
        $galeri = GaleriKegiatan::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori,
            'tanggal' => $request->tanggal,
            'foto' => $foto,
            'created_by' => Auth::id()
        ]);

        return response()->json([
            'message' => 'Galeri berhasil ditambahkan',
            'data' => $galeri
        ], 201);
    }

    // DETAIL
    public function show($id)
    {
        $galeri = GaleriKegiatan::with('user')
            ->find($id);

        if (!$galeri) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'data' => $galeri
        ]);
    }

    // UPDATE
    public function update(UpdateGaleriKegiatanRequest $request, $id)
    {
        $galeri = GaleriKegiatan::findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|in:pengambilan_rongsok,penjualan_rongsok,kegiatan_sosial,penyaluran_dana',
            'tanggal' => 'required|date',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if (!$galeri) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // UPDATE FOTO
        if ($request->hasFile('foto')) {

            // HAPUS FOTO LAMA
            if ($galeri->foto) {
                Storage::disk('public')
                    ->delete($galeri->foto);
            }

            // UPLOAD FOTO BARU
            $foto = $request->file('foto')
                ->store('galeri', 'public');

            $galeri->foto = $foto;
        }

        // UPDATE DATA
        $galeri->judul = $request->judul;
        $galeri->deskripsi = $request->deskripsi;
        $galeri->kategori = $request->kategori;
        $galeri->tanggal = $request->tanggal;

        $galeri->save();

        return response()->json([
            'message' => 'Data berhasil diupdate',
            'data' => $galeri
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        $galeri = GaleriKegiatan::find($id);

        if (!$galeri) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // HAPUS FOTO
        if ($galeri->foto) {
            Storage::disk('public')
                ->delete($galeri->foto);
        }

        $galeri->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ]);
    }

    public function semuaBukti()
    {
        $penjualan = PenjualanRongsok::whereNotNull('foto_bukti')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'penjualan-' . $item->id,
                    'real_id' => null,
                    'judul' => 'Bukti Penjualan Rongsok',
                    'kategori' => 'penjualan_rongsok',
                    'tanggal' => $item->tanggal,
                    'foto' => $item->foto_bukti,
                    'deskripsi' => $item->tempat_jual ?? '-',
                    'sumber' => 'Penjualan Rongsok',
                ];
            });

        $pemasukan = PemasukanKas::whereNotNull('foto')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'pemasukan-' . $item->id,
                    'real_id' => null,
                    'judul' => 'Bukti Pemasukan Kas',
                    'kategori' => 'pemasukan_kas',
                    'tanggal' => $item->tanggal,
                    'foto' => $item->foto,
                    'deskripsi' => $item->keterangan ?? '-',
                    'sumber' => $item->sumber === 'penjualan_rongsok' ? 'Penjualan Rongsok' : ($item->sumber === 'donasi' ? 'Donasi' : 'Pemasukan Lainnya'),
                ];
            });

        $pengeluaran = PengeluaranKas::whereNotNull('foto')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'pengeluaran-' . $item->id,
                    'real_id' => null,
                    'judul' => 'Bukti Pengeluaran Kas',
                    'kategori' => 'pengeluaran_kas',
                    'tanggal' => $item->tanggal,
                    'foto' => $item->foto,
                    'deskripsi' => $item->keperluan ?? '-',
                    'sumber' => 'Pengeluaran Kas',
                ];
            });

        $galeri = GaleriKegiatan::whereNotNull('foto')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'galeri-' . $item->id,
                    'real_id' => $item->id,
                    'judul' => $item->judul,
                    'kategori' => $item->kategori,
                    'tanggal' => $item->tanggal,
                    'foto' => $item->foto,
                    'deskripsi' => $item->deskripsi ?? '-',
                    'sumber' => 'Galeri Kegiatan',
                ];
            });

        $data = collect()
            ->merge($penjualan)
            ->merge($pemasukan)
            ->merge($pengeluaran)
            ->merge($galeri)
            ->sortByDesc('tanggal')
            ->values();

        return response()->json([
            'data' => $data
        ]);
    }
}
