<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetService
{
    /**
     * Kirim link reset password ke email pengguna.
     */
    public function sendResetLink(string $email): string
    {
        return Password::sendResetLink([
            'email' => $email,
        ]);
    }

    /**
     * Proses reset password menggunakan token reset.
     */
    public function resetPassword(array $payload): string
    {
        return Password::reset(
            $payload,
            function (User $user) use ($payload): void {
                $user->forceFill([
                    'password' => Hash::make($payload['password']),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );
    }
}