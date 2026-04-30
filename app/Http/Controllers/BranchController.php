<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     * Admin: semua cabang. Manager & Kasir: hanya cabangnya sendiri.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Branch::class);
        $actor = $request->user();

        $perPage = min(max((int) $request->integer('per_page', 15), 5), 100);
        $sortBy = in_array($request->input('sort_by'), ['id', 'code', 'name', 'city', 'created_at'], true)
            ? $request->input('sort_by')
            : 'created_at';
        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $query = Branch::query();

        // Manager & Kasir hanya melihat cabangnya sendiri
        if (in_array($actor?->role, ['manager', 'kasir'], true)) {
            $query->where('id', $actor->branch_id);
        }

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOL));
        }

        $branches = $query->orderBy($sortBy, $sortDir)->paginate($perPage)->appends($request->query());

        return response()->json($branches);
    }

    /**
     * Store a newly created resource in storage. Admin only.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Branch::class);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('branches', 'code')->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $branch = Branch::query()->create($validated);

        return response()->json(['message' => 'Branch created.', 'data' => $branch], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch)
    {
        $this->authorize('view', $branch);

        return response()->json($branch->load(['users', 'vehicles', 'shipments']));
    }

    /**
     * Update the specified resource in storage.
     * Admin: semua cabang. Manager: hanya cabangnya sendiri.
     */
    public function update(Request $request, Branch $branch)
    {
        $this->authorize('update', $branch);

        $validated = $request->validate([
            'code' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('branches', 'code')->whereNull('deleted_at')->ignore($branch->id),
            ],
            'name' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['sometimes', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $branch->update($validated);

        return response()->json(['message' => 'Branch updated.', 'data' => $branch->fresh()]);
    }

    /**
     * Remove the specified resource from storage. Admin only.
     */
    public function destroy(Branch $branch)
    {
        $this->authorize('delete', $branch);

        $branch->delete();

        return response()->json(['message' => 'Branch deleted.']);
    }
}
