<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $otp = rand(100000, 999999);


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
            'email_verified_at' => null,
        ]);

        try {
            Log::info('Mulai kirim OTP register', [
                'email' => $request->email,
                'otp' => $otp,
            ]);

            Mail::to($user->email)->send(new OtpMail($otp));

            Log::info('OTP berhasil dikirim', [
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal kirim OTP register', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Registrasi berhasil, tetapi OTP gagal dikirim.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Registrasi berhasil. Kode OTP telah dikirim ke email.',
            'email' => $user->email,
        ], 201);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|max:6',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        if (!$user->otp_code || $user->otp_code !== $request->otp) {
            return response()->json([
                'message' => 'Kode OTP tidak valid.',
            ], 400);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return response()->json([
                'message' => 'Kode OTP sudah kedaluwarsa.',
            ], 400);
        }


        $user->update([
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'Verifikasi akun berhasil.',
        ]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email sudah terverifikasi.',
            ], 400);
        }

        $otp = rand(100000, 999999);

        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new OtpMail($otp));

        return response()->json([
            'message' => 'Kode OTP baru berhasil dikirim.',
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'email' => ['Email atau password salah.'],
            ], 400);
        }

        if ($user->status === 'nonaktif') {
            return response()->json([
                'message' => 'Akun kamu telah dinonaktifkan.',
                'status' => 'inactive'
            ], 403);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Akun belum diverifikasi. Silakan verifikasi OTP terlebih dahulu.',
            ], 400);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}
