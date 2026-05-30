<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Email tidak ditemukan',
            ], 404);
        }

        $otp = random_int(100000, 999999);

        $user->update([
            'reset_password_otp' => $otp,
            'reset_password_otp_expires_at' => now()->addMinutes(10),
            'reset_password_token' => null,
            'reset_password_token_expires_at' => null,
        ]);

        Mail::raw("Kode OTP reset password Anda adalah: {$otp}", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Reset Password Loopit');
        });

        return response()->json([
            'message' => 'Kode OTP reset password berhasil dikirim',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->where('reset_password_otp', $request->otp)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'OTP tidak valid',
            ], 422);
        }

        if (
            !$user->reset_password_otp_expires_at ||
            Carbon::parse($user->reset_password_otp_expires_at)->isPast()
        ) {
            return response()->json([
                'message' => 'OTP sudah kedaluwarsa',
            ], 422);
        }

        $token = Str::random(64);

        $user->update([
            'reset_password_token' => hash('sha256', $token),
            'reset_password_token_expires_at' => now()->addMinutes(15),
            'reset_password_otp' => null,
            'reset_password_otp_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'OTP valid',
            'reset_token' => $token,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'reset_token' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        $hashedToken = hash('sha256', $request->reset_token);

        $user = User::where('email', $request->email)
            ->where('reset_password_token', $hashedToken)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Token reset tidak valid',
            ], 422);
        }

        if (
            !$user->reset_password_token_expires_at ||
            Carbon::parse($user->reset_password_token_expires_at)->isPast()
        ) {
            return response()->json([
                'message' => 'Token reset sudah kedaluwarsa',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'reset_password_token' => null,
            'reset_password_token_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'Password berhasil diubah',
        ]);
    }
}