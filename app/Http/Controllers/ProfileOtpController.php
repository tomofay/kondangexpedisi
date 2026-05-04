<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Mail\OtpMail;

class ProfileOtpController extends Controller
{
    public function sendEmailOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|unique:users,email,' . auth()->id(),
        ]);

        $otp = rand(100000, 999999);
        $email = $request->email;

        // Store in session
        Session::put('profile_otp', [
            'type' => 'email',
            'code' => $otp,
            'email' => $email,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send actual email
        try {
            Mail::to($email)->send(new OtpMail($otp, 'email'));
        } catch (\Exception $e) {
            \Log::error("Failed to send OTP email: " . $e->getMessage());
            // We still return success in debug mode to allow the user to see the OTP in logs
            if (!config('app.debug')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengirim email verifikasi. Silakan coba lagi nanti.'
                ], 500);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'OTP telah dikirim ke email baru Anda.',
            'debug_otp' => config('app.debug') ? $otp : null
        ]);
    }

    public function sendPasswordOtp(Request $request): JsonResponse
    {
        $user = auth()->user();
        $otp = rand(100000, 999999);

        Session::put('profile_otp', [
            'type' => 'password',
            'code' => $otp,
            'email' => $user->email,
            'expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($user->email)->send(new OtpMail($otp, 'password'));
        } catch (\Exception $e) {
            \Log::error("Failed to send OTP email: " . $e->getMessage());
            if (!config('app.debug')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengirim email verifikasi. Silakan coba lagi nanti.'
                ], 500);
            }
        }

        \Log::info("OTP for Password Change for {$user->email}: $otp");

        return response()->json([
            'status' => 'success',
            'message' => 'OTP telah dikirim ke email aktif Anda.',
            'debug_otp' => config('app.debug') ? $otp : null
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $stored = Session::get('profile_otp');

        if (!$stored || now()->gt($stored['expires_at'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP telah kedaluwarsa atau tidak valid.'
            ], 422);
        }

        if ((string)$request->otp !== (string)$stored['code']) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP yang Anda masukkan salah.'
            ], 422);
        }

        // Mark as verified for this session
        Session::put('profile_otp_verified', true);

        return response()->json([
            'status' => 'success',
            'message' => 'OTP berhasil diverifikasi.'
        ]);
    }
}
