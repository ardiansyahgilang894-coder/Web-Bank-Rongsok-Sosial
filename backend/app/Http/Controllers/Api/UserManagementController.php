<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => User::select(
                'id',
                'name',
                'email',
                'role',
                'status',
                'email_verified_at',
                'created_at'
            )
                ->latest()
                ->get()
        ]);
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => ['required', Rule::in(['admin', 'petugas', 'user'])],
        ]);

        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return response()->json([
                'message' => 'Kamu tidak bisa mengubah role akun sendiri.'
            ], 422);
        }

        $user->update([
            'role' => $request->role,
        ]);

        return response()->json([
            'message' => 'Role user berhasil diperbarui',
            'data' => $user
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ]);

        $user = User::findOrFail($id);

        if ($user->id === Auth::id() && $request->status === 'nonaktif') {
            return response()->json([
                'message' => 'Kamu tidak bisa menonaktifkan akun sendiri.'
            ], 422);
        }

        $user->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Status user berhasil diperbarui',
            'data' => $user
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return response()->json([
                'message' => 'Kamu tidak bisa menghapus akun sendiri.'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus'
        ]);
    }
}
