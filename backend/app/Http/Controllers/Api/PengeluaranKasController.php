<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengeluaranKasRequest;
use App\Http\Requests\UpdatePengeluaranKasRequest;
use App\Models\PengeluaranKas;
use Illuminate\Support\Facades\Auth;

class PengeluaranKasController extends Controller
{
    // LIST
    public function index()
    {
        $data = PengeluaranKas::with('user')
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Data pengeluaran kas',
            'data' => $data
        ]);
    }

    // CREATE
    public function store(StorePengeluaranKasRequest $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keperluan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'nominal' => 'required|integer|min:0',
            'sumber' => 'nullable|in:penjualan_rongsok,donasi,lainnya',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $foto = null;

        if ($request->hasFile('foto')) {
            $foto = $request->file('foto')->store('pengeluaran-kas', 'public');
        }

        $pengeluaran = PengeluaranKas::create([
            'tanggal' => $request->tanggal,
            'keperluan' => $request->keperluan,
            'deskripsi' => $request->deskripsi,
            'nominal' => $request->nominal,
            'foto' => $foto,
            'created_by' => Auth::id()
        ]);

        return response()->json([
            'message' => 'Pengeluaran berhasil ditambahkan',
            'data' => $pengeluaran
        ], 201);
    }

    // DETAIL
    public function show($id)
    {
        $pengeluaran = PengeluaranKas::with('user')
            ->find($id);

        if (!$pengeluaran) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'data' => $pengeluaran
        ]);
    }

    // UPDATE
    public function update(UpdatePengeluaranKasRequest $request, $id)
    {
        $pengeluaran = PengeluaranKas::find($id);

        $request->validate([
            'tanggal' => 'required|date',
            'keperluan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'nominal' => 'required|integer|min:0',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        

        if (!$pengeluaran) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $pengeluaran->update([
            'tanggal' => $request->tanggal,
            'keperluan' => $request->keperluan,
            'deskripsi' => $request->deskripsi,
            'nominal' => $request->nominal,
            'foto' => $request->hasFile('foto') ? $request->file('foto')->store('pengeluaran-kas', 'public') : $pengeluaran->foto,
        ]);

        return response()->json([
            'message' => 'Data berhasil diupdate',
            'data' => $pengeluaran
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        $pengeluaran = PengeluaranKas::find($id);

        if (!$pengeluaran) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $pengeluaran->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ]);
    }
}