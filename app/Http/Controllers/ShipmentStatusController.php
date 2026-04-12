<?php

namespace App\Http\Controllers;

use App\Models\ShipmentStatus;
use Illuminate\Http\Request;

class ShipmentStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(ShipmentStatus::query()->orderBy('sequence')->paginate(15));
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
            'code' => ['required', 'string', 'max:40', 'unique:shipment_statuses,code'],
            'name' => ['required', 'string', 'max:255'],
            'sequence' => ['required', 'integer', 'min:1'],
            'is_final' => ['sometimes', 'boolean'],
            'badge_color' => ['required', 'string', 'max:30'],
        ]);

        $status = ShipmentStatus::query()->create($validated);

        return response()->json(['message' => 'Shipment status created.', 'data' => $status], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShipmentStatus $shipmentStatus)
    {
        return response()->json($shipmentStatus->load('shipments', 'trackings'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShipmentStatus $shipmentStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ShipmentStatus $shipmentStatus)
    {
        $validated = $request->validate([
            'code' => ['sometimes', 'string', 'max:40', 'unique:shipment_statuses,code,' . $shipmentStatus->id],
            'name' => ['sometimes', 'string', 'max:255'],
            'sequence' => ['sometimes', 'integer', 'min:1'],
            'is_final' => ['sometimes', 'boolean'],
            'badge_color' => ['sometimes', 'string', 'max:30'],
        ]);

        $shipmentStatus->update($validated);

        return response()->json(['message' => 'Shipment status updated.', 'data' => $shipmentStatus->fresh()]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShipmentStatus $shipmentStatus)
    {
        $shipmentStatus->delete();

        return response()->json(['message' => 'Shipment status deleted.']);
    }
}
