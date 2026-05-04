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
        $search = trim((string) $request->input('search', ''));

        $query = Vehicle::query()->with('branch');

        if ($actor->role !== 'admin') {
            $query->where('branch_id', $actor->branch_id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $vehicles = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        return response()->json($vehicles);
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
        $this->authorize('create', Vehicle::class);

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
            'status' => ['required', 'in:available,in_use,maintenance,inactive'],
        ]);

        $actor = $request->user();
        if ($actor->role !== 'admin') {
            $validated['branch_id'] = $actor->branch_id;
        }

        $vehicle = Vehicle::query()->create($validated);

        return response()->json(['message' => 'Vehicle created.', 'data' => $vehicle], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);
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
        $this->authorize('update', $vehicle);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'plate_number' => [
                'sometimes',
                'string',
                'max:30',
                Rule::unique('vehicles', 'plate_number')->ignore($vehicle->id)->whereNull('deleted_at'),
            ],
            'type' => ['sometimes', 'in:motorcycle,car,van,truck'],
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
        $this->authorize('delete', $vehicle);
        $vehicle->delete();

        return response()->json(['message' => 'Vehicle deleted.']);
    }
}
