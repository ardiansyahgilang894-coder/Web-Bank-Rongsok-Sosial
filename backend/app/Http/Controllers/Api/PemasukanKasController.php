<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePemasukanKasRequest;
use App\Http\Requests\UpdatePemasukanKasRequest;
use App\Models\PemasukanKas;
use Illuminate\Support\Facades\Auth;
use App\Models\PenjualanRongsok;

class PemasukanKasController extends Controller
{
    // LIST
    public function index()
    {
        $data = PemasukanKas::with('user')
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Data pemasukan kas',
            'data' => $data
        ]);
    }

    // CREATE
    public function store(StorePemasukanKasRequest $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'nominal' => 'required|integer|min:0',
            'sumber' => 'required|in:donasi,lainnya',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $foto = null;

        if ($request->hasFile('foto')) {
            $foto = $request->file('foto')->store('pemasukan-kas', 'public');
        }

        $pemasukan = PemasukanKas::create([
            'penjualan_rongsok_id' => null,
            'tanggal' => $request->tanggal,
            'sumber' => $request->sumber,
            'keterangan' => $request->keterangan,
            'deskripsi' => $request->deskripsi,
            'nominal' => $request->nominal,
            'foto' => $foto,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Pemasukan berhasil ditambahkan',
            'data' => $pemasukan
        ], 201);
    }

    // DETAIL
    public function show($id)
    {
        $pemasukan = PemasukanKas::find($id);

        if (!$pemasukan) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'data' => $pemasukan
        ]);
    }

    // UPDATE
    public function update(UpdatePemasukanKasRequest $request, $id)
    {
        $pemasukan = PemasukanKas::findOrFail($id);

        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'nominal' => 'required|integer|min:0',
            'sumber' => 'required|in:donasi,lainnya',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $foto = $pemasukan->foto;

        if ($request->hasFile('foto')) {
            $foto = $request->file('foto')->store('pemasukan-kas', 'public');
        }

        $pemasukan->update([
            'tanggal' => $request->tanggal,
            'sumber' => $request->sumber,
            'keterangan' => $request->keterangan,
            'deskripsi' => $request->deskripsi,
            'nominal' => $request->nominal,
            'foto' => $foto,
        ]);

        return response()->json([
            'message' => 'Data berhasil diupdate',
            'data' => $pemasukan
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        $data = PemasukanKas::findOrFail($id);

        if ($data->penjualan_rongsok_id) {
            PenjualanRongsok::where('id', $data->penjualan_rongsok_id)->delete();
        }

        $data->delete();

        return response()->json([
            'message' => 'Data pemasukan kas berhasil dihapus'
        ]);
    }
}
