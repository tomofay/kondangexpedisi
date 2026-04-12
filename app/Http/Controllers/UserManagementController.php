<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $actor = $request->user();
        $perPage = min(max((int) $request->integer('per_page', 15), 5), 100);
        $sortBy = in_array($request->input('sort_by'), ['id', 'name', 'email', 'role', 'created_at'], true)
            ? $request->input('sort_by')
            : 'created_at';
        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $query = User::query()->with('branch');

        if ($actor?->role === 'manager') {
            $managerBranch = Branch::query()->find($actor->branch_id);

            if (! $managerBranch || ! $managerBranch->is_active) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('branch_id', $managerBranch->id);
            }
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

    public function store(Request $request): JsonResponse
    {
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

        $validated['permissions'] = array_values(array_unique(array_filter($validated['permissions'] ?? [])));

        $user = User::query()->create($validated);

        return response()->json([
            'message' => 'User created.',
            'data' => $user,
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->load('branch'));
    }

    public function update(Request $request, User $user): JsonResponse
    {
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

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ((int) $request->user()?->id === (int) $user->id) {
            return response()->json(['message' => 'Tidak bisa menghapus akun sendiri.'], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted.',
        ]);
    }
}
