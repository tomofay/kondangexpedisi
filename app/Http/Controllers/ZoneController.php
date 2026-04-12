<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 5), 100);
        $sortBy = in_array($request->input('sort_by'), ['id', 'code', 'name', 'multiplier', 'created_at'], true)
            ? $request->input('sort_by')
            : 'created_at';
        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $query = Zone::query();

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOL));
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
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('zones', 'code')->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'multiplier' => ['required', 'numeric', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $zone = Zone::query()->create($validated);

        return response()->json(['message' => 'Zone created.', 'data' => $zone], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Zone $zone)
    {
        return response()->json($zone->load('rateCards', 'shipments'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Zone $zone)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Zone $zone)
    {
        $validated = $request->validate([
            'code' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('zones', 'code')->whereNull('deleted_at')->ignore($zone->id),
            ],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'multiplier' => ['sometimes', 'numeric', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $zone->update($validated);

        return response()->json(['message' => 'Zone updated.', 'data' => $zone->fresh()]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Zone $zone)
    {
        $zone->delete();

        return response()->json(['message' => 'Zone deleted.']);
    }
}
