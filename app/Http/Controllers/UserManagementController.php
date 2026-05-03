<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users.
     * Admin: semua user. Manager: user di cabangnya (read-only).
     */
    public function index(Request $request): JsonResponse
    {
        $actor = $request->user();

        if (! in_array($actor?->role, ['admin', 'manager'], true)) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola user.');
        }

        $perPage = min(max((int) $request->integer('per_page', 15), 5), 100);
        $sortBy = in_array($request->input('sort_by'), ['id', 'name', 'email', 'role', 'created_at'], true)
            ? $request->input('sort_by')
            : 'created_at';
        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $query = User::query()->with('branch');

        // Manager hanya bisa lihat user di cabangnya (kecuali customer)
        if ($actor->role === 'manager') {
            $query->where('branch_id', $actor->branch_id)
                  ->where('role', '!=', 'customer');
        }

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOL));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        $users = $query->orderBy($sortBy, $sortDir)->paginate($perPage)->appends($request->query());

        return response()->json($users);
    }

    /**
     * Store a newly created user. Admin only.
     */
    public function store(Request $request): JsonResponse
    {
        $actor = $request->user();

        if (! in_array($actor?->role, ['admin', 'manager'], true)) {
            abort(403, 'Anda tidak memiliki akses untuk membuat user baru.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'photo' => ['nullable', 'string', 'max:255'],
            'role' => ['required', Rule::in(['admin', 'manager', 'kasir', 'courier', 'customer'])],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'is_active' => ['sometimes', 'boolean'],
            'password' => ['required', 'string', 'min:8'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        if ($actor->role === 'manager') {
            $validated['branch_id'] = $actor->branch_id;
            if (in_array($validated['role'], ['admin', 'customer'])) {
                abort(403, 'Manager tidak dapat membuat user dengan role Admin atau Customer.');
            }
        }

        $validated['permissions'] = array_values(array_unique(array_filter($validated['permissions'] ?? [])));

        $user = User::query()->create($validated);

        return response()->json([
            'message' => 'User created.',
            'data' => $user,
        ], 201);
    }

    /**
     * Display user detail.
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();

        if (! in_array($actor?->role, ['admin', 'manager'], true)) {
            abort(403);
        }

        // Manager hanya bisa lihat user di cabangnya
        if ($actor->role === 'manager' && (int) $user->branch_id !== (int) $actor->branch_id) {
            abort(403, 'Anda hanya bisa melihat user di cabang Anda.');
        }

        return response()->json($user->load('branch'));
    }

    /**
     * Update user. Admin only.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();

        if (! in_array($actor?->role, ['admin', 'manager'], true)) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah data user.');
        }

        if ($actor->role === 'manager') {
            if ((int) $user->branch_id !== (int) $actor->branch_id || $user->role === 'customer' || $user->role === 'admin') {
                abort(403, 'Manager hanya dapat mengubah data staff di cabangnya sendiri.');
            }
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->whereNull('deleted_at')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'photo' => ['nullable', 'string', 'max:255'],
            'role' => ['sometimes', Rule::in(['admin', 'manager', 'kasir', 'courier', 'customer'])],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'is_active' => ['sometimes', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        if (array_key_exists('password', $validated) && empty($validated['password'])) {
            unset($validated['password']);
        }

        $validated['permissions'] = array_values(array_unique(array_filter($validated['permissions'] ?? [])));

        $user->update($validated);

        return response()->json([
            'message' => 'User updated.',
            'data' => $user->fresh()->load('branch'),
        ]);
    }

    /**
     * Delete user. Admin only.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();

        if (! in_array($actor?->role, ['admin', 'manager'], true)) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus user.');
        }

        if ($actor->role === 'manager') {
            if ((int) $user->branch_id !== (int) $actor->branch_id || $user->role === 'customer' || $user->role === 'admin') {
                abort(403, 'Manager hanya dapat menghapus staff di cabangnya sendiri.');
            }
        }

        if ((int) $actor->id === (int) $user->id) {
            return response()->json(['message' => 'Tidak bisa menghapus akun sendiri.'], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted.',
        ]);
    }
}
