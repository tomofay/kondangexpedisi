<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourierShipmentResource;
use App\Http\Resources\CourierTrackingResource;
use App\Models\Shipment;
use App\Models\ShipmentTrackingProof;
use App\Services\ShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MobileCourierApiController extends Controller
{
    public function __construct(
        private readonly ShipmentService $shipmentService
    ) {}

    /**
     * List shipments assigned to the current courier.
     */
    public function assignedShipments(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $shipments = Shipment::query()
            ->where('courier_id', $user->id)
            ->whereHas('status', function ($q) {
                $q->whereNotIn('code', ['delivered', 'cancelled', 'returned']);
            })
            ->with(['status'])
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return CourierShipmentResource::collection($shipments)->response();
    }

    /**
     * Get detail of a specific shipment.
     */
    public function shipmentDetail(Shipment $shipment): JsonResponse
    {
        $this->authorizeCourierAccess($shipment);

        $shipment->load(['status', 'trackings.status', 'items']);

        return response()->json([
            'data' => new CourierShipmentResource($shipment),
            'trackings' => CourierTrackingResource::collection($shipment->trackings),
            'items' => $shipment->items,
        ]);
    }

    /**
     * Update shipment status with GPS and optional Proof of Delivery (POD).
     */
    public function updateStatus(Request $request, Shipment $shipment): JsonResponse
    {
        $this->authorizeCourierAccess($shipment);

        $validated = $request->validate([
            'status_code' => ['required', 'string'],
            'location' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'gps_lat' => ['required', 'numeric'],
            'gps_lng' => ['required', 'numeric'],
            'gps_accuracy' => ['nullable', 'numeric'],
            'proof_image' => ['nullable', 'image', 'max:5120'], // Max 5MB
            'recipient_signature' => ['nullable', 'string'], // Base64 signature
        ]);

        // Enforcement: Status 'delivered' MUST have a proof image
        if ($validated['status_code'] === 'delivered' && ! $request->hasFile('proof_image')) {
            throw ValidationException::withMessages([
                'proof_image' => 'Bukti foto wajib diunggah untuk status Terkirim (Delivered).',
            ]);
        }

        return DB::transaction(function () use ($request, $shipment, $validated) {
            $updatedShipment = $this->shipmentService->transitionStatus(
                $shipment,
                $validated['status_code'],
                $request->user()->id,
                $validated['location'],
                $validated['notes'],
                false,
                null,
                $validated['gps_lat'],
                $validated['gps_lng'],
                $validated['gps_accuracy'] ?? null
            );

            // Handle Proof of Delivery (POD)
            if ($request->hasFile('proof_image')) {
                $path = $request->file('proof_image')->store('proofs/' . now()->format('Y/m/d'), 'public');
                
                ShipmentTrackingProof::create([
                    'shipment_id' => $shipment->id,
                    'tracking_id' => $updatedShipment->trackings()->latest()->first()->id,
                    'proof_type' => 'photo',
                    'file_path' => $path,
                    'metadata' => [
                        'gps_lat' => $validated['gps_lat'],
                        'gps_lng' => $validated['gps_lng'],
                        'device_info' => $request->header('User-Agent'),
                    ],
                ]);
            }

            if (! empty($validated['recipient_signature'])) {
                // Logic to save base64 signature could be added here
            }

            return response()->json([
                'message' => 'Status shipment berhasil diperbarui.',
                'data' => new CourierShipmentResource($updatedShipment->fresh(['status'])),
            ]);
        });
    }

    /**
     * History of completed shipments for the courier.
     */
    public function deliveryHistory(Request $request): JsonResponse
    {
        $user = $request->user();

        $shipments = Shipment::query()
            ->where('courier_id', $user->id)
            ->whereHas('status', function ($q) {
                $q->whereIn('code', ['delivered', 'returned']);
            })
            ->with(['status'])
            ->latest('delivered_at')
            ->paginate($request->integer('per_page', 15));

        return CourierShipmentResource::collection($shipments)->response();
    }

    private function authorizeCourierAccess(Shipment $shipment): void
    {
        if ($shipment->courier_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke shipment ini.');
        }
    }
}
