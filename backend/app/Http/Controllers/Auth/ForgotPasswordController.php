<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

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

    try {
        $this->sendOtpWithBrevo($user, $otp, 'reset');
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'OTP gagal dikirim.',
            'error' => $e->getMessage(),
        ], 500);
    }

    return response()->json([
        'message' => 'Kode OTP reset password berhasil dikirim',
    ]);
}

    private function sendOtpWithBrevo($user, $otp, $type = 'register')
{
    $subject = $type === 'reset'
        ? 'Kode OTP Reset Password Loopit'
        : 'Kode OTP Verifikasi Akun Loopit';

    $title = $type === 'reset'
        ? 'Reset Password Loopit'
        : 'Verifikasi Akun Loopit';

    $description = $type === 'reset'
        ? 'Gunakan kode OTP berikut untuk reset password akun kamu:'
        : 'Gunakan kode OTP berikut untuk verifikasi akun kamu:';

    $response = Http::withHeaders([
        'api-key' => env('BREVO_API_KEY'),
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ])->post('https://api.brevo.com/v3/smtp/email', [
        'sender' => [
            'name' => env('MAIL_FROM_NAME', 'Loopit'),
            'email' => env('MAIL_FROM_ADDRESS'),
        ],
        'to' => [
            [
                'email' => $user->email,
                'name' => $user->name,
            ],
        ],
        'subject' => $subject,
        'htmlContent' => "
            <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #334155;'>
                <h2 style='color: #059669;'>{$title}</h2>
                <p>Halo <strong>{$user->name}</strong>,</p>
                <p>{$description}</p>
                <h1 style='letter-spacing: 6px; color: #059669;'>{$otp}</h1>
                <p>Kode OTP ini berlaku selama <strong>10 menit</strong>.</p>
                <p>Jika kamu tidak merasa melakukan permintaan ini, abaikan email ini.</p>
            </div>
        ",
    ]);

    if (!$response->successful()) {
        throw new \Exception($response->body());
    }
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