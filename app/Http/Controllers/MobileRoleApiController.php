<?php

namespace App\Http\Controllers;

use App\Models\CourierSyncEvent;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\ShipmentTracking;
use App\Models\ShipmentTrackingProof;
use App\Services\AuditLogService;
use App\Services\ShipmentService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class MobileRoleApiController extends Controller
{
    public function customerShipments(Request $request): JsonResponse
    {
        $customer = $this->resolveCustomer($request);
        $perPage = min(max((int) $request->integer('per_page', 15), 5), 50);

        $query = Shipment::query()
            ->select([
                'id',
                'tracking_number',
                'customer_id',
                'status_id',
                'service_type',
                'total_amount',
                'payment_status',
                'estimated_delivery_at',
                'current_status_at',
                'created_at',
            ])
            ->with([
                'status:id,code,name,badge_color',
                'trackings' => fn ($trackingQuery) => $trackingQuery
                    ->select(['id', 'shipment_id', 'status_id', 'location', 'event_at'])
                    ->latest('event_at')
                    ->limit(1),
                'trackings.status:id,code,name',
            ])
            ->where('customer_id', $customer->id)
            ->latest('id');

        if ($request->filled('status')) {
            $query->whereHas('status', fn ($statusQuery) => $statusQuery->where('code', (string) $request->input('status')));
        }

        $paginator = $query->paginate($perPage);

        $items = collect($paginator->items())->map(function (Shipment $shipment) {
            $latestTracking = $shipment->trackings->first();

            return [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'status' => $shipment->status?->only(['code', 'name', 'badge_color']),
                'service_type' => $shipment->service_type,
                'total_amount' => $shipment->total_amount,
                'payment_status' => $shipment->payment_status,
                'estimated_delivery_at' => $shipment->estimated_delivery_at,
                'current_status_at' => $shipment->current_status_at,
                'latest_tracking' => $latestTracking ? [
                    'status' => $latestTracking->status?->only(['code', 'name']),
                    'location' => $latestTracking->location,
                    'event_at' => $latestTracking->event_at,
                ] : null,
                'detail_endpoint' => route('mobile.customer.shipments.show', $shipment),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'meta' => $this->paginatorMeta($paginator),
            'items' => $items,
        ]);
    }

    public function customerShipmentDetail(Request $request, Shipment $shipment): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        abort_unless((int) $shipment->customer_id === (int) $customer->id, 403, 'Shipment ini bukan milik customer saat ini.');

        $shipment->load([
            'status:id,code,name,badge_color',
            'trackings' => fn ($query) => $query->select(['id', 'shipment_id', 'status_id', 'location', 'notes', 'event_at'])->latest('event_at'),
            'trackings.status:id,code,name',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'status' => $shipment->status?->only(['code', 'name', 'badge_color']),
                'service_type' => $shipment->service_type,
                'total_amount' => $shipment->total_amount,
                'payment_status' => $shipment->payment_status,
                'estimated_delivery_at' => $shipment->estimated_delivery_at,
                'current_status_at' => $shipment->current_status_at,
                'trackings' => $shipment->trackings->map(fn ($tracking) => [
                    'status' => $tracking->status?->only(['code', 'name']),
                    'location' => $tracking->location,
                    'notes' => $tracking->notes,
                    'event_at' => $tracking->event_at,
                ])->values(),
            ],
        ]);
    }

    public function courierShipments(Request $request): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor && $actor->role === 'courier', 403, 'Akses endpoint khusus courier ditolak.');

        $perPage = min(max((int) $request->integer('per_page', 15), 5), 50);

        $query = Shipment::query()
            ->select([
                'id',
                'tracking_number',
                'courier_id',
                'status_id',
                'service_type',
                'recipient_name',
                'recipient_phone',
                'recipient_address',
                'estimated_delivery_at',
                'current_status_at',
                'notes',
                'created_at',
            ])
            ->with(['status:id,code,name,badge_color'])
            ->where('courier_id', $actor->id)
            ->latest('id');

        if ($request->filled('status')) {
            $query->whereHas('status', fn ($statusQuery) => $statusQuery->where('code', (string) $request->input('status')));
        }

        $paginator = $query->paginate($perPage);

        $items = collect($paginator->items())->map(function (Shipment $shipment) {
            return [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'status' => $shipment->status?->only(['code', 'name', 'badge_color']),
                'service_type' => $shipment->service_type,
                'recipient' => [
                    'name' => $shipment->recipient_name,
                    'phone_masked' => $this->maskPhone($shipment->recipient_phone),
                    'address' => $shipment->recipient_address,
                ],
                'estimated_delivery_at' => $shipment->estimated_delivery_at,
                'current_status_at' => $shipment->current_status_at,
                'notes' => $shipment->notes,
                'transition_status_endpoint' => route('shipments.transition-status', $shipment),
                'tracking_create_endpoint' => route('shipment-trackings.store'),
                'detail_endpoint' => route('mobile.courier.shipments.show', $shipment),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'meta' => $this->paginatorMeta($paginator),
            'items' => $items,
        ]);
    }

    public function courierShipmentDetail(Request $request, Shipment $shipment): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor && $actor->role === 'courier', 403, 'Akses endpoint khusus courier ditolak.');
        abort_unless((int) $shipment->courier_id === (int) $actor->id, 403, 'Shipment ini tidak ditugaskan kepada courier ini.');

        $shipment->load([
            'status:id,code,name,badge_color',
            'trackings' => fn ($query) => $query->select(['id', 'shipment_id', 'status_id', 'location', 'notes', 'event_at'])->latest('event_at')->limit(15),
            'trackings.status:id,code,name',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'status' => $shipment->status?->only(['code', 'name', 'badge_color']),
                'recipient' => [
                    'name' => $shipment->recipient_name,
                    'phone_masked' => $this->maskPhone($shipment->recipient_phone),
                    'address' => $shipment->recipient_address,
                ],
                'service_type' => $shipment->service_type,
                'estimated_delivery_at' => $shipment->estimated_delivery_at,
                'current_status_at' => $shipment->current_status_at,
                'trackings' => $shipment->trackings->map(fn ($tracking) => [
                    'status' => $tracking->status?->only(['code', 'name']),
                    'location' => $tracking->location,
                    'notes' => $tracking->notes,
                    'event_at' => $tracking->event_at,
                ])->values(),
            ],
        ]);
    }

    public function courierOfflineSync(Request $request, ShipmentService $shipmentService): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor && $actor->role === 'courier', 403, 'Akses endpoint khusus courier ditolak.');

        $validated = $request->validate([
            'events' => ['required', 'array', 'min:1', 'max:100'],
            'events.*.client_event_id' => ['required', 'string', 'max:120'],
            'events.*.event_type' => ['required', Rule::in(['transition_status', 'tracking_event'])],
            'events.*.shipment_id' => ['required', 'integer', 'exists:shipments,id'],
            'events.*.status_code' => ['required', 'string', 'max:40'],
            'events.*.location' => ['nullable', 'string', 'max:255'],
            'events.*.gps_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'events.*.gps_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'events.*.gps_accuracy_m' => ['nullable', 'numeric', 'min:0'],
            'events.*.notes' => ['nullable', 'string', 'max:500'],
            'events.*.occurred_at' => ['nullable', 'date'],
            'events.*.meta' => ['nullable', 'array'],
        ]);

        $results = [];

        foreach ($validated['events'] as $event) {
            $results[] = $this->processCourierSyncEvent($actor->id, $event, $shipmentService);
        }

        $summary = [
            'total' => count($results),
            'applied' => collect($results)->where('sync_status', 'applied')->count(),
            'duplicates' => collect($results)->where('sync_status', 'duplicate')->count(),
            'failed' => collect($results)->where('sync_status', 'failed')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'summary' => $summary,
            'items' => $results,
        ], 202);
    }

    public function courierOperationalProofStore(Request $request, Shipment $shipment, AuditLogService $auditLogService): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor && $actor->role === 'courier', 403, 'Akses endpoint khusus courier ditolak.');
        abort_unless((int) $shipment->courier_id === (int) $actor->id, 403, 'Shipment ini tidak ditugaskan kepada courier ini.');

        $validated = $request->validate([
            'tracking_id' => ['required', 'integer', 'exists:shipment_trackings,id'],
            'proof_type' => ['required', Rule::in(['pickup_photo', 'handover_photo', 'recipient_signature'])],
            'file' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:6144'],
            'gps_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'gps_accuracy_m' => ['nullable', 'numeric', 'min:0'],
            'captured_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $tracking = ShipmentTracking::query()->findOrFail((int) $validated['tracking_id']);
        abort_unless((int) $tracking->shipment_id === (int) $shipment->id, 422, 'tracking_id tidak sesuai dengan shipment.');

        $file = $request->file('file');
        $hash = hash_file('sha256', $file->getRealPath());

        $directory = sprintf('operational-proofs/%s/%s', $shipment->tracking_number, $validated['proof_type']);
        $extension = $file->extension() ?: 'jpg';
        $filename = sprintf('%s_%s.%s', now()->format('YmdHis'), substr($hash, 0, 16), $extension);
        $path = Storage::disk('public')->putFileAs($directory, $file, $filename);

        try {
            $proof = ShipmentTrackingProof::query()->create([
                'shipment_id' => $shipment->id,
                'tracking_id' => $tracking->id,
                'uploaded_by' => $actor->id,
                'proof_type' => (string) $validated['proof_type'],
                'file_path' => $path,
                'file_mime' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'file_hash' => $hash,
                'gps_lat' => $validated['gps_lat'] ?? null,
                'gps_lng' => $validated['gps_lng'] ?? null,
                'gps_accuracy_m' => $validated['gps_accuracy_m'] ?? null,
                'captured_at' => $validated['captured_at'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Bukti operasional duplikat, data sebelumnya dipakai.',
                    'data' => ShipmentTrackingProof::query()
                        ->where('tracking_id', $tracking->id)
                        ->where('proof_type', (string) $validated['proof_type'])
                        ->where('file_hash', $hash)
                        ->latest('id')
                        ->first(),
                ], 200);
            }

            throw $exception;
        }

        $auditLogService->record(
            'shipment_tracking.proof_upload',
            $proof,
            $actor,
            [],
            [
                'shipment_id' => $proof->shipment_id,
                'tracking_id' => $proof->tracking_id,
                'proof_type' => $proof->proof_type,
                'file_path' => $proof->file_path,
                'gps_lat' => $proof->gps_lat,
                'gps_lng' => $proof->gps_lng,
                'captured_at' => $proof->captured_at,
            ],
            'Upload bukti operasional courier.',
            [
                'source' => 'user_action',
                'is_manual_correction' => false,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Bukti operasional berhasil diunggah.',
            'data' => [
                'id' => $proof->id,
                'shipment_id' => $proof->shipment_id,
                'tracking_id' => $proof->tracking_id,
                'proof_type' => $proof->proof_type,
                'file_url' => Storage::disk('public')->url($proof->file_path),
                'file_hash' => $proof->file_hash,
                'gps_lat' => $proof->gps_lat,
                'gps_lng' => $proof->gps_lng,
                'gps_accuracy_m' => $proof->gps_accuracy_m,
                'captured_at' => $proof->captured_at,
                'created_at' => $proof->created_at,
            ],
        ], 201);
    }

    public function courierOperationalProofIndex(Request $request, Shipment $shipment): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor && $actor->role === 'courier', 403, 'Akses endpoint khusus courier ditolak.');
        abort_unless((int) $shipment->courier_id === (int) $actor->id, 403, 'Shipment ini tidak ditugaskan kepada courier ini.');

        $proofs = ShipmentTrackingProof::query()
            ->where('shipment_id', $shipment->id)
            ->when($request->filled('proof_type'), fn ($query) => $query->where('proof_type', (string) $request->input('proof_type')))
            ->latest('id')
            ->get()
            ->map(function (ShipmentTrackingProof $proof) {
                return [
                    'id' => $proof->id,
                    'tracking_id' => $proof->tracking_id,
                    'proof_type' => $proof->proof_type,
                    'file_url' => Storage::disk('public')->url($proof->file_path),
                    'gps_lat' => $proof->gps_lat,
                    'gps_lng' => $proof->gps_lng,
                    'gps_accuracy_m' => $proof->gps_accuracy_m,
                    'captured_at' => $proof->captured_at,
                    'created_at' => $proof->created_at,
                ];
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'shipment_id' => $shipment->id,
            'total' => $proofs->count(),
            'items' => $proofs,
        ]);
    }

    public function adminKasirShipments(Request $request): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor && in_array($actor->role, ['admin', 'kasir'], true), 403, 'Akses endpoint admin/kasir ditolak.');

        $perPage = min(max((int) $request->integer('per_page', 15), 5), 50);

        $query = Shipment::query()
            ->select([
                'id',
                'tracking_number',
                'branch_id',
                'status_id',
                'service_type',
                'sender_name',
                'recipient_name',
                'total_amount',
                'payment_status',
                'processing_status',
                'pricing_mode',
                'pricing_approval_status',
                'created_at',
            ])
            ->with([
                'status:id,code,name,badge_color',
                'branch:id,name,code,city',
            ])
            ->latest('id');

        if ($actor->role === 'kasir') {
            $query->where('branch_id', $actor->branch_id);
        }

        if ($request->filled('branch_id') && $actor->role === 'admin') {
            $query->where('branch_id', (int) $request->integer('branch_id'));
        }

        if ($request->filled('status')) {
            $query->whereHas('status', fn ($statusQuery) => $statusQuery->where('code', (string) $request->input('status')));
        }

        $paginator = $query->paginate($perPage);

        $items = collect($paginator->items())->map(function (Shipment $shipment) {
            return [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'branch' => $shipment->branch?->only(['id', 'name', 'code', 'city']),
                'status' => $shipment->status?->only(['code', 'name', 'badge_color']),
                'service_type' => $shipment->service_type,
                'sender_name' => $shipment->sender_name,
                'recipient_name' => $shipment->recipient_name,
                'total_amount' => $shipment->total_amount,
                'payment_status' => $shipment->payment_status,
                'processing_status' => $shipment->processing_status,
                'pricing_mode' => $shipment->pricing_mode,
                'pricing_approval_status' => $shipment->pricing_approval_status,
                'detail_endpoint' => route('shipments.show', $shipment),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'meta' => $this->paginatorMeta($paginator),
            'items' => $items,
        ]);
    }

    public function adminKasirPayments(Request $request): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor && in_array($actor->role, ['admin', 'kasir'], true), 403, 'Akses endpoint admin/kasir ditolak.');

        $perPage = min(max((int) $request->integer('per_page', 15), 5), 50);

        $query = Payment::query()
            ->select([
                'id',
                'shipment_id',
                'customer_id',
                'method',
                'status',
                'amount',
                'processing_status',
                'created_at',
            ])
            ->with([
                'shipment:id,tracking_number,branch_id,status_id',
                'shipment.status:id,code,name,badge_color',
                'customer:id,name,phone',
            ])
            ->latest('id');

        if ($actor->role === 'kasir') {
            $query->whereHas('shipment', fn ($shipmentQuery) => $shipmentQuery->where('branch_id', $actor->branch_id));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        $paginator = $query->paginate($perPage);

        $items = collect($paginator->items())->map(function (Payment $payment) {
            return [
                'id' => $payment->id,
                'method' => $payment->method,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'processing_status' => $payment->processing_status,
                'shipment' => [
                    'id' => $payment->shipment?->id,
                    'tracking_number' => $payment->shipment?->tracking_number,
                    'status' => $payment->shipment?->status?->only(['code', 'name', 'badge_color']),
                ],
                'customer' => [
                    'id' => $payment->customer?->id,
                    'name' => $payment->customer?->name,
                    'phone_masked' => $this->maskPhone($payment->customer?->phone),
                ],
                'detail_endpoint' => route('payments.show', $payment),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'meta' => $this->paginatorMeta($paginator),
            'items' => $items,
        ]);
    }

    private function resolveCustomer(Request $request): Customer
    {
        $user = $request->user();

        abort_unless($user && $user->role === 'customer', 403, 'Akses endpoint customer ditolak.');

        $customer = $user->customer;

        abort_unless($customer, 404, 'Profil customer belum terhubung dengan akun ini.');

        return $customer;
    }

    private function paginatorMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'has_next_page' => $paginator->hasMorePages(),
        ];
    }

    private function processCourierSyncEvent(int $courierId, array $event, ShipmentService $shipmentService): array
    {
        $clientEventId = (string) $event['client_event_id'];

        $existing = CourierSyncEvent::query()
            ->where('courier_id', $courierId)
            ->where('client_event_id', $clientEventId)
            ->first();

        if ($existing) {
            return [
                'client_event_id' => $clientEventId,
                'sync_status' => 'duplicate',
                'reason' => 'Event sudah pernah diproses.',
                'shipment_id' => $existing->shipment_id,
                'event_type' => $existing->event_type,
                'synced_at' => $existing->synced_at,
            ];
        }

        $shipment = Shipment::query()->find((int) $event['shipment_id']);

        if (! $shipment || (int) $shipment->courier_id !== $courierId) {
            $failedLog = CourierSyncEvent::query()->create([
                'courier_id' => $courierId,
                'shipment_id' => $shipment?->id,
                'client_event_id' => $clientEventId,
                'event_type' => (string) $event['event_type'],
                'status' => 'failed',
                'payload' => $event,
                'processing_error' => 'Shipment tidak ditemukan atau tidak ditugaskan ke courier ini.',
                'occurred_at' => $event['occurred_at'] ?? null,
                'synced_at' => now(),
            ]);

            return [
                'client_event_id' => $clientEventId,
                'sync_status' => 'failed',
                'reason' => $failedLog->processing_error,
                'shipment_id' => $shipment?->id,
                'event_type' => (string) $event['event_type'],
                'synced_at' => $failedLog->synced_at,
            ];
        }

        try {
            DB::transaction(function () use ($courierId, $shipment, $event, $shipmentService) {
                if ($event['event_type'] === 'transition_status') {
                    $shipmentService->transitionStatus(
                        $shipment,
                        (string) $event['status_code'],
                        $courierId,
                        $event['location'] ?? null,
                        $event['notes'] ?? null,
                        false,
                        null,
                        isset($event['gps_lat']) ? (float) $event['gps_lat'] : null,
                        isset($event['gps_lng']) ? (float) $event['gps_lng'] : null,
                        isset($event['gps_accuracy_m']) ? (float) $event['gps_accuracy_m'] : null
                    );
                }

                if ($event['event_type'] === 'tracking_event') {
                    $status = ShipmentStatus::query()->where('code', (string) $event['status_code'])->first();

                    if (! $status) {
                        throw new \RuntimeException('Status code tracking tidak valid.');
                    }

                    ShipmentTracking::query()->create([
                        'shipment_id' => $shipment->id,
                        'status_id' => $status->id,
                        'created_by' => $courierId,
                        'location' => $event['location'] ?? null,
                        'gps_lat' => $event['gps_lat'] ?? null,
                        'gps_lng' => $event['gps_lng'] ?? null,
                        'gps_accuracy_m' => $event['gps_accuracy_m'] ?? null,
                        'notes' => $event['notes'] ?? 'Sinkronisasi event tracking dari mode offline.',
                        'event_at' => $event['occurred_at'] ?? now(),
                    ]);
                }

                CourierSyncEvent::query()->create([
                    'courier_id' => $courierId,
                    'shipment_id' => $shipment->id,
                    'client_event_id' => (string) $event['client_event_id'],
                    'event_type' => (string) $event['event_type'],
                    'status' => 'applied',
                    'payload' => $event,
                    'occurred_at' => $event['occurred_at'] ?? null,
                    'synced_at' => now(),
                ]);
            });

            return [
                'client_event_id' => $clientEventId,
                'sync_status' => 'applied',
                'shipment_id' => $shipment->id,
                'event_type' => (string) $event['event_type'],
                'synced_at' => now(),
            ];
        } catch (Throwable $throwable) {
            $failedLog = CourierSyncEvent::query()->create([
                'courier_id' => $courierId,
                'shipment_id' => $shipment->id,
                'client_event_id' => $clientEventId,
                'event_type' => (string) $event['event_type'],
                'status' => 'failed',
                'payload' => $event,
                'processing_error' => $throwable->getMessage(),
                'occurred_at' => $event['occurred_at'] ?? null,
                'synced_at' => now(),
            ]);

            return [
                'client_event_id' => $clientEventId,
                'sync_status' => 'failed',
                'reason' => $failedLog->processing_error,
                'shipment_id' => $shipment->id,
                'event_type' => (string) $event['event_type'],
                'synced_at' => $failedLog->synced_at,
            ];
        }
    }

    private function maskPhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (! $digits) {
            return '-';
        }

        if (strlen($digits) <= 6) {
            return substr($digits, 0, 2).str_repeat('*', max(strlen($digits) - 2, 1));
        }

        return substr($digits, 0, 3).str_repeat('*', max(strlen($digits) - 6, 2)).substr($digits, -3);
    }
}
