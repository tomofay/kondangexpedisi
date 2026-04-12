<?php

namespace App\Http\Controllers;

use App\Models\ShipmentTracking;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class ShipmentTrackingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(ShipmentTracking::query()->with(['shipment', 'status', 'creator'])->latest('event_at')->paginate(20));
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
    public function store(Request $request, AuditLogService $auditLogService)
    {
        $validated = $request->validate([
            'shipment_id' => ['required', 'exists:shipments,id'],
            'status_id' => ['required', 'exists:shipment_statuses,id'],
            'created_by' => ['nullable', 'exists:users,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'event_at' => ['nullable', 'date'],
        ]);

        $tracking = ShipmentTracking::query()->create([
            ...$validated,
            'event_at' => $validated['event_at'] ?? now(),
        ]);

        $auditLogService->record(
            'shipment_tracking.create',
            $tracking,
            $request->user(),
            [],
            $tracking->fresh()->only(['shipment_id', 'status_id', 'location', 'event_at']),
            'Tracking shipment ditambahkan.'
        );

        return response()->json(['message' => 'Shipment tracking created.', 'data' => $tracking], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShipmentTracking $shipmentTracking)
    {
        return response()->json($shipmentTracking->load(['shipment', 'status', 'creator']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShipmentTracking $shipmentTracking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ShipmentTracking $shipmentTracking, AuditLogService $auditLogService)
    {
        $before = $shipmentTracking->only(['shipment_id', 'status_id', 'location', 'event_at', 'notes']);

        $validated = $request->validate([
            'shipment_id' => ['sometimes', 'exists:shipments,id'],
            'status_id' => ['sometimes', 'exists:shipment_statuses,id'],
            'created_by' => ['nullable', 'exists:users,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'event_at' => ['sometimes', 'date'],
        ]);

        $shipmentTracking->update($validated);

        $auditLogService->record(
            'shipment_tracking.update',
            $shipmentTracking,
            $request->user(),
            $before,
            $shipmentTracking->fresh()->only(['shipment_id', 'status_id', 'location', 'event_at', 'notes']),
            'Tracking shipment diperbarui.'
        );

        return response()->json(['message' => 'Shipment tracking updated.', 'data' => $shipmentTracking->fresh()]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShipmentTracking $shipmentTracking, AuditLogService $auditLogService)
    {
        $auditLogService->record(
            'shipment_tracking.delete',
            $shipmentTracking,
            request()->user(),
            $shipmentTracking->only(['shipment_id', 'status_id', 'location', 'event_at', 'notes']),
            [],
            'Tracking shipment dihapus.'
        );

        $shipmentTracking->delete();

        return response()->json(['message' => 'Shipment tracking deleted.']);
    }
}
