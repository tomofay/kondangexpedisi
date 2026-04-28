<?php

namespace App\Http\Controllers;

use App\Models\ShipmentTrackingProof;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OperationalProofController extends Controller
{
    public function adminInvestigationIndex(Request $request): JsonResponse
    {
        $actor = $request->user();

        abort_unless($actor && in_array($actor->role, ['admin', 'manager'], true), 403, 'Hanya admin/manager yang dapat melihat bukti operasional lintas shipment.');

        $validated = $request->validate([
            'proof_type' => ['nullable', 'in:pickup_photo,handover_photo,recipient_signature'],
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date'],
            'courier_id' => ['nullable', 'integer', 'exists:users,id'],
            'shipment_id' => ['nullable', 'integer', 'exists:shipments,id'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 25);

        $query = ShipmentTrackingProof::query()
            ->with([
                'shipment:id,tracking_number,branch_id,customer_id',
                'tracking:id,shipment_id,status_id,event_at,location,gps_lat,gps_lng',
                'tracking.status:id,code,name',
                'uploader:id,name,role',
            ])
            ->latest('id');

        if (! empty($validated['proof_type'])) {
            $query->where('proof_type', $validated['proof_type']);
        }

        if (! empty($validated['from'])) {
            $query->whereDate('captured_at', '>=', $validated['from']);
        }

        if (! empty($validated['until'])) {
            $query->whereDate('captured_at', '<=', $validated['until']);
        }

        if (! empty($validated['courier_id'])) {
            $query->where('uploaded_by', (int) $validated['courier_id']);
        }

        if (! empty($validated['shipment_id'])) {
            $query->where('shipment_id', (int) $validated['shipment_id']);
        }

        $paginator = $query->paginate($perPage);

        $items = collect($paginator->items())->map(function (ShipmentTrackingProof $proof) {
            return [
                'id' => $proof->id,
                'proof_type' => $proof->proof_type,
                'file_url' => Storage::disk('public')->url($proof->file_path),
                'file_hash' => $proof->file_hash,
                'captured_at' => $proof->captured_at,
                'gps_lat' => $proof->gps_lat,
                'gps_lng' => $proof->gps_lng,
                'gps_accuracy_m' => $proof->gps_accuracy_m,
                'notes' => $proof->notes,
                'shipment' => [
                    'id' => $proof->shipment?->id,
                    'tracking_number' => $proof->shipment?->tracking_number,
                    'branch_id' => $proof->shipment?->branch_id,
                ],
                'tracking_event' => [
                    'id' => $proof->tracking?->id,
                    'status' => $proof->tracking?->status?->only(['code', 'name']),
                    'event_at' => $proof->tracking?->event_at,
                    'location' => $proof->tracking?->location,
                ],
                'uploader' => $proof->uploader?->only(['id', 'name', 'role']),
                'created_at' => $proof->created_at,
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_next_page' => $paginator->hasMorePages(),
            ],
            'items' => $items,
        ]);
    }
}
