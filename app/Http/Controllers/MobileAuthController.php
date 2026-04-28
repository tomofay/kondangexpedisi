<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MobileAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:120'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak valid.',
            ]);
        }

        if (! in_array($user->role, ['customer', 'courier'], true)) {
            abort(403, 'Hanya akun customer atau courier yang dapat login di aplikasi mobile.');
        }

        if (! $user->is_active) {
            abort(403, 'Akun dinonaktifkan. Hubungi tim operasional.');
        }

        $token = $user->createToken(
            $validated['device_name'],
            ['mobile', 'role:'.$user->role]
        )->plainTextToken;

        $user->recordLogin((string) $request->ip());

        return response()->json([
            'status' => 'success',
            'message' => 'Login mobile berhasil.',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user, 401);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user, 401);

        $validated = $request->validate([
            'all_devices' => ['sometimes', 'boolean'],
        ]);

        if ((bool) ($validated['all_devices'] ?? false)) {
            $user->tokens()->delete();
        } else {
            $request->user()->currentAccessToken()?->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Logout mobile berhasil.',
        ]);
    }
}
