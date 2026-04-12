<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentItem;
use Illuminate\Http\Request;
use App\Services\ShipmentService;

class ShipmentItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(ShipmentItem::query()->with('shipment')->latest()->paginate(15));
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
    public function store(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'weight_kg' => ['required', 'numeric', 'min:0'],
            'length_cm' => ['nullable', 'numeric', 'min:0'],
            'width_cm' => ['nullable', 'numeric', 'min:0'],
            'height_cm' => ['nullable', 'numeric', 'min:0'],
            'declared_value' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $item = ShipmentItem::query()->create([
            ...$validated,
            'shipment_id' => $shipment->id,
        ]);

        app(ShipmentService::class)->recalculateShipmentTotals($shipment);

        return response()->json([
            'message' => 'Shipment item berhasil ditambahkan.',
            'data' => $item->fresh(),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShipmentItem $shipmentItem)
    {
        return response()->json($shipmentItem->load('shipment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShipmentItem $shipmentItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ShipmentItem $shipmentItem)
    {
        $validated = $request->validate([
            'item_name' => ['sometimes', 'string', 'max:255'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'weight_kg' => ['sometimes', 'numeric', 'min:0'],
            'length_cm' => ['nullable', 'numeric', 'min:0'],
            'width_cm' => ['nullable', 'numeric', 'min:0'],
            'height_cm' => ['nullable', 'numeric', 'min:0'],
            'declared_value' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $shipmentItem->update($validated);

        app(ShipmentService::class)->recalculateShipmentTotals($shipmentItem->shipment);

        return response()->json([
            'message' => 'Shipment item berhasil diperbarui.',
            'data' => $shipmentItem->fresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShipmentItem $shipmentItem)
    {
        $shipment = $shipmentItem->shipment;
        $shipmentItem->delete();

        app(ShipmentService::class)->recalculateShipmentTotals($shipment);

        return response()->json([
            'message' => 'Shipment item berhasil dihapus.',
        ]);
    }
}
