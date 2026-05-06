<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\OtpMail;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:30', 'unique:customers,phone', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $otp = rand(100000, 999999);
        
        // Store registration data in session temporarily
        Session::put('registration_data', [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'otp' => $otp,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Send OTP email
        try {
            Mail::to($request->email)->send(new OtpMail($otp, 'registration'));
        } catch (\Exception $e) {
            \Log::error("Failed to send registration OTP: " . $e->getMessage());
            if (!config('app.debug')) {
                return back()->withInput()->withErrors(['email' => 'Gagal mengirim email verifikasi. Silakan coba lagi.']);
            }
        }

        return redirect()->route('register.otp');
    }

    /**
     * Display the OTP verification view.
     */
    public function showOtpForm(): View|RedirectResponse
    {
        if (!Session::has('registration_data')) {
            return redirect()->route('register');
        }
        return view('auth.verify-otp');
    }

    /**
     * Verify the OTP and create the user account.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $data = Session::get('registration_data');

        if (!$data || now()->gt($data['expires_at'])) {
            Session::forget('registration_data');
            return redirect()->route('register')->withErrors(['email' => 'Waktu verifikasi habis. Silakan daftar kembali.']);
        }

        if ((string)$request->otp !== (string)$data['otp']) {
            return back()->withErrors(['otp' => 'Kode OTP yang Anda masukkan salah.']);
        }

        // OTP Valid, create the account
        try {
            $user = DB::transaction(function () use ($data) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'password' => $data['password'], // Already hashed
                    'role' => 'customer',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                $user->customer()->create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                ]);

                return $user;
            });

            Session::forget('registration_data');
            Auth::login($user);

            return redirect()->route('customer.dashboard');
        } catch (\Exception $e) {
            \Log::error("Registration creation failed: " . $e->getMessage());
            return redirect()->route('register')->withErrors(['email' => 'Gagal membuat akun. Silakan coba lagi.']);
        }
    }
}
