<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenjualanRongsok;
use App\Models\PemasukanKas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenjualanRongsokController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => PenjualanRongsok::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'periode_mulai' => 'nullable|date',
            'periode_selesai' => 'nullable|date',
            'total_berat' => 'required|numeric|min:0',
            'total_pendapatan' => 'required|integer|min:0',
            'tempat_jual' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'foto_bukti' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $foto = null;

        if ($request->hasFile('foto_bukti')) {
            $foto = $request->file('foto_bukti')->store('penjualan-rongsok', 'public');
        }

        $penjualan = PenjualanRongsok::create([
            'tanggal' => $request->tanggal,
            'periode_mulai' => $request->periode_mulai,
            'periode_selesai' => $request->periode_selesai,
            'total_berat' => $request->total_berat,
            'total_pendapatan' => $request->total_pendapatan,
            'tempat_jual' => $request->tempat_jual,
            'keterangan' => $request->keterangan,
            'foto_bukti' => $foto,
            'created_by' => Auth::id(),
        ]);

        PemasukanKas::create([
            'penjualan_rongsok_id' => $penjualan->id,
            'tanggal' => $request->tanggal,
            'sumber' => 'penjualan_rongsok',
            'keterangan' => 'Hasil penjualan rongsok',
            'nominal' => $request->total_pendapatan,
            'foto' => $foto,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Data penjualan rongsok berhasil ditambahkan',
            'data' => $penjualan
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'data' => PenjualanRongsok::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $penjualan = PenjualanRongsok::findOrFail($id);

        $request->validate([
            'tanggal' => 'required|date',
            'periode_mulai' => 'nullable|date',
            'periode_selesai' => 'nullable|date',
            'total_berat' => 'required|numeric|min:0',
            'total_pendapatan' => 'required|integer|min:0',
            'tempat_jual' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'foto_bukti' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $foto = $penjualan->foto_bukti;

        if ($request->hasFile('foto_bukti')) {
            $foto = $request->file('foto_bukti')->store('penjualan-rongsok', 'public');
        }

        $penjualan->update([
    'tanggal' => $request->tanggal,
    'periode_mulai' => $request->periode_mulai,
    'periode_selesai' => $request->periode_selesai,
    'total_berat' => $request->total_berat,
    'total_pendapatan' => $request->total_pendapatan,
    'tempat_jual' => $request->tempat_jual,
    'keterangan' => $request->keterangan,
    'foto_bukti' => $foto,
]);

       PemasukanKas::where('penjualan_rongsok_id', $penjualan->id)->update([
    'tanggal' => $request->tanggal,
    'sumber' => 'penjualan_rongsok',
    'keterangan' => $request->keterangan ?? 'Hasil penjualan rongsok',
    'nominal' => $request->total_pendapatan,
    'foto' => $foto,
]);

        return response()->json([
            'message' => 'Data penjualan rongsok berhasil diperbarui',
            'data' => $penjualan
        ]);
    }

    public function destroy($id)
    {
        $data = PenjualanRongsok::findOrFail($id);

        PemasukanKas::where('penjualan_rongsok_id', $data->id)->delete();

        $data->delete();

        return response()->json([
            'message' => 'Data penjualan rongsok dan pemasukan terkait berhasil dihapus'
        ]);
    }
}
