<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $actor = $request->user();
        $perPage = min(max((int) $request->integer('per_page', 15), 5), 100);
        $sortBy = in_array($request->input('sort_by'), ['id', 'name', 'plate_number', 'type', 'status', 'created_at'], true)
            ? $request->input('sort_by')
            : 'created_at';
        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $query = Vehicle::query()->with('branch');

        if (in_array($actor?->role, ['manager', 'kasir'], true)) {
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
                    ->orWhere('plate_number', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        return response()->json(
            $query->orderBy($sortBy, $sortDir)->paginate($perPage)->appends($request->query())
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'plate_number' => [
                'required',
                'string',
                'max:30',
                Rule::unique('vehicles', 'plate_number')->whereNull('deleted_at'),
            ],
            'type' => ['required', 'in:motorcycle,car,van,truck'],
            'capacity_kg' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,in_use,maintenance,inactive'],
        ]);

        $vehicle = Vehicle::query()->create($validated);

        return response()->json(['message' => 'Vehicle created.', 'data' => $vehicle], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        return response()->json($vehicle->load('branch', 'shipments'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'branch_id' => ['sometimes', 'exists:branches,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'plate_number' => [
                'sometimes',
                'string',
                'max:30',
                Rule::unique('vehicles', 'plate_number')->whereNull('deleted_at')->ignore($vehicle->id),
            ],
            'type' => ['sometimes', 'in:motorcycle,car,van,truck'],
            'capacity_kg' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:available,in_use,maintenance,inactive'],
        ]);

        $vehicle->update($validated);

        return response()->json(['message' => 'Vehicle updated.', 'data' => $vehicle->fresh()]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return response()->json(['message' => 'Vehicle deleted.']);
    }
}
