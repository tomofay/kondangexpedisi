<?php

namespace App\Http\Controllers;

use App\Models\RateCard;
use Illuminate\Http\Request;

class RateCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 5), 100);
        $sortBy = in_array($request->input('sort_by'), ['id', 'service_type', 'base_price', 'per_kg_price', 'created_at'], true)
            ? $request->input('sort_by')
            : 'created_at';
        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $query = RateCard::query()->with(['originZone', 'destinationZone']);

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('service_type', 'like', "%{$search}%")
                    ->orWhereHas('originZone', fn ($zoneQuery) => $zoneQuery->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                    ->orWhereHas('destinationZone', fn ($zoneQuery) => $zoneQuery->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('origin_zone_id')) {
            $query->where('origin_zone_id', $request->integer('origin_zone_id'));
        }

        if ($request->filled('destination_zone_id')) {
            $query->where('destination_zone_id', $request->integer('destination_zone_id'));
        }

        if ($request->filled('zone_id')) {
            $zoneId = $request->integer('zone_id');
            $query->where(function ($routeQuery) use ($zoneId) {
                $routeQuery->where('origin_zone_id', $zoneId)
                    ->orWhere('destination_zone_id', $zoneId);
            });
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->input('service_type'));
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
            'origin_zone_id' => ['nullable', 'exists:zones,id'],
            'destination_zone_id' => ['nullable', 'exists:zones,id'],
            'zone_id' => ['nullable', 'exists:zones,id'],
            'service_type' => ['required', 'in:regular,express,same_day,economy'],
            'min_weight_kg' => ['required', 'numeric', 'min:0'],
            'max_weight_kg' => ['nullable', 'numeric', 'gte:min_weight_kg'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'per_kg_price' => ['required', 'numeric', 'min:0'],
            'insurance_fee' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $routeZoneId = $validated['zone_id'] ?? null;
        $validated['origin_zone_id'] = $validated['origin_zone_id'] ?? $routeZoneId;
        $validated['destination_zone_id'] = $validated['destination_zone_id'] ?? $routeZoneId;
        $validated['zone_id'] = $validated['destination_zone_id'];

        if (! $validated['origin_zone_id'] || ! $validated['destination_zone_id']) {
            return response()->json(['message' => 'Zona asal dan zona tujuan wajib diisi.'], 422);
        }

        $rateCard = RateCard::query()->create($validated);

        return response()->json(['message' => 'Rate card created.', 'data' => $rateCard->load(['originZone', 'destinationZone'])], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(RateCard $rateCard)
    {
        return response()->json($rateCard->load(['originZone', 'destinationZone']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RateCard $rateCard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RateCard $rateCard)
    {
        $validated = $request->validate([
            'origin_zone_id' => ['sometimes', 'nullable', 'exists:zones,id'],
            'destination_zone_id' => ['sometimes', 'nullable', 'exists:zones,id'],
            'zone_id' => ['sometimes', 'nullable', 'exists:zones,id'],
            'service_type' => ['sometimes', 'in:regular,express,same_day,economy'],
            'min_weight_kg' => ['sometimes', 'numeric', 'min:0'],
            'max_weight_kg' => ['nullable', 'numeric', 'gte:min_weight_kg'],
            'base_price' => ['sometimes', 'numeric', 'min:0'],
            'per_kg_price' => ['sometimes', 'numeric', 'min:0'],
            'insurance_fee' => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('zone_id', $validated)) {
            $validated['destination_zone_id'] = $validated['zone_id'];
        }

        if (array_key_exists('destination_zone_id', $validated) && $validated['destination_zone_id']) {
            $validated['zone_id'] = $validated['destination_zone_id'];
        }

        $rateCard->update($validated);

        return response()->json(['message' => 'Rate card updated.', 'data' => $rateCard->fresh()->load(['originZone', 'destinationZone'])]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RateCard $rateCard)
    {
        $rateCard->delete();

        return response()->json(['message' => 'Rate card deleted.']);
    }
}
