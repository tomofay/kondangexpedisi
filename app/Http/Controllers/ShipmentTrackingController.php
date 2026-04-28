<?php

namespace App\Http\Controllers;

use App\Models\ShipmentTracking;
use App\Models\Shipment;
use App\Services\AuditLogService;
use App\Services\OperationalIssueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentTrackingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $actor = $request->user();

        $query = ShipmentTracking::query()->with(['shipment', 'status', 'creator'])->latest('event_at');

        if ($actor?->role === 'courier') {
            $query->whereHas('shipment', fn ($builder) => $builder->where('courier_id', $actor->id));
        }

        if ($actor?->role === 'kasir') {
            $query->whereHas('shipment', fn ($builder) => $builder->where('branch_id', $actor->branch_id));
        }

        return response()->json($query->paginate(20));
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
    public function store(Request $request, AuditLogService $auditLogService, OperationalIssueService $operationalIssueService)
    {
        $actor = $request->user();

        $validated = $request->validate([
            'shipment_id' => ['required', 'exists:shipments,id'],
            'status_id' => ['required', 'exists:shipment_statuses,id'],
            'created_by' => ['nullable', 'exists:users,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'gps_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'gps_accuracy_m' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'event_at' => ['nullable', 'date'],
        ]);

        $shipment = Shipment::query()->findOrFail((int) $validated['shipment_id']);
        $this->assertTrackingAccess($actor, $shipment);

        if ($actor?->role === 'courier') {
            $validated['created_by'] = $actor->id;
        }

        try {
            $tracking = DB::transaction(function () use ($validated, $request, $auditLogService) {
                $tracking = ShipmentTracking::query()->create([
                    ...$validated,
                    'event_at' => $validated['event_at'] ?? now(),
                ]);

                $auditLogService->record(
                    'shipment_tracking.create',
                    $tracking,
                    $request->user(),
                    [],
                    $tracking->fresh()->only(['shipment_id', 'status_id', 'location', 'gps_lat', 'gps_lng', 'gps_accuracy_m', 'event_at']),
                    'Tracking shipment ditambahkan.'
                );

                return $tracking;
            });
        } catch (\Throwable $throwable) {
            $shipment = \App\Models\Shipment::query()->find($validated['shipment_id']);
            $context = [
                'operation' => 'create',
                'tracking_payload' => $validated,
            ];

            if ($shipment) {
                $operationalIssueService->markShipmentError($shipment, 'Tracking shipment gagal disimpan.', $context, $request->user(), $throwable);
            } else {
                $operationalIssueService->recordError('shipment_tracking', 'Tracking shipment gagal disimpan.', $context, $request->user(), $throwable, 'high');
            }

            return response()->json(['message' => 'Tracking shipment gagal disimpan.'], 500);
        }

        return response()->json(['message' => 'Shipment tracking created.', 'data' => $tracking], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShipmentTracking $shipmentTracking)
    {
        $this->assertTrackingAccess(request()->user(), $shipmentTracking->shipment);

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
    public function update(Request $request, ShipmentTracking $shipmentTracking, AuditLogService $auditLogService, OperationalIssueService $operationalIssueService)
    {
        $actor = $request->user();
        $this->assertTrackingAccess($actor, $shipmentTracking->shipment);

        $before = $shipmentTracking->only(['shipment_id', 'status_id', 'location', 'gps_lat', 'gps_lng', 'gps_accuracy_m', 'event_at', 'notes']);

        $validated = $request->validate([
            'shipment_id' => ['sometimes', 'exists:shipments,id'],
            'status_id' => ['sometimes', 'exists:shipment_statuses,id'],
            'created_by' => ['nullable', 'exists:users,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'gps_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'gps_accuracy_m' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'event_at' => ['sometimes', 'date'],
        ]);

        if ($actor?->role === 'courier') {
            if (array_key_exists('shipment_id', $validated) || array_key_exists('created_by', $validated)) {
                return response()->json([
                    'message' => 'Kurir hanya boleh memperbarui status, lokasi, catatan, dan waktu event.',
                ], 422);
            }

            $validated['created_by'] = $actor->id;
        }

        try {
            DB::transaction(function () use ($shipmentTracking, $validated, $request, $auditLogService, $before) {
                $shipmentTracking->update($validated);

                $auditLogService->record(
                    'shipment_tracking.update',
                    $shipmentTracking,
                    $request->user(),
                    $before,
                    $shipmentTracking->fresh()->only(['shipment_id', 'status_id', 'location', 'gps_lat', 'gps_lng', 'gps_accuracy_m', 'event_at', 'notes']),
                    'Tracking shipment diperbarui.'
                );
            });
        } catch (\Throwable $throwable) {
            $shipment = $shipmentTracking->shipment;
            $context = [
                'operation' => 'update',
                'tracking_id' => $shipmentTracking->id,
                'tracking_payload' => $validated,
            ];

            if ($shipment) {
                $operationalIssueService->markShipmentError($shipment, 'Tracking shipment gagal diperbarui.', $context, $request->user(), $throwable);
            } else {
                $operationalIssueService->recordError('shipment_tracking', 'Tracking shipment gagal diperbarui.', $context, $request->user(), $throwable, 'high');
            }

            return response()->json(['message' => 'Tracking shipment gagal diperbarui.'], 500);
        }

        return response()->json(['message' => 'Shipment tracking updated.', 'data' => $shipmentTracking->fresh()]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShipmentTracking $shipmentTracking, AuditLogService $auditLogService, OperationalIssueService $operationalIssueService)
    {
        $actor = request()->user();
        $this->assertTrackingAccess($actor, $shipmentTracking->shipment);

        if ($actor?->role === 'courier') {
            abort(403, 'Kurir tidak boleh menghapus data tracking.');
        }

        try {
            $auditLogService->record(
                'shipment_tracking.delete',
                $shipmentTracking,
                request()->user(),
                $shipmentTracking->only(['shipment_id', 'status_id', 'location', 'event_at', 'notes']),
                [],
                'Tracking shipment dihapus.'
            );

            $shipmentTracking->delete();
        } catch (\Throwable $throwable) {
            $shipment = $shipmentTracking->shipment;
            $context = [
                'operation' => 'delete',
                'tracking_id' => $shipmentTracking->id,
                'tracking_payload' => [
                    'shipment_id' => $shipmentTracking->shipment_id,
                ],
            ];

            if ($shipment) {
                $operationalIssueService->markShipmentError($shipment, 'Tracking shipment gagal dihapus.', $context, request()->user(), $throwable);
            } else {
                $operationalIssueService->recordError('shipment_tracking', 'Tracking shipment gagal dihapus.', $context, request()->user(), $throwable, 'high');
            }

            return response()->json(['message' => 'Tracking shipment gagal dihapus.'], 500);
        }

        return response()->json(['message' => 'Shipment tracking deleted.']);
    }

    private function assertTrackingAccess($actor, ?Shipment $shipment): void
    {
        if (! $actor || ! $shipment) {
            abort(403, 'Akses tracking ditolak.');
        }

        if (in_array($actor->role, ['admin', 'manager'], true)) {
            return;
        }

        if ($actor->role === 'kasir' && (int) $actor->branch_id === (int) $shipment->branch_id) {
            return;
        }

        if ($actor->role === 'courier' && (int) $actor->id === (int) $shipment->courier_id) {
            return;
        }

        abort(403, 'Anda tidak memiliki akses pada tracking shipment ini.');
    }
}
