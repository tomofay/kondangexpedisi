<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Shipment;
use App\Services\AuditLogService;
use App\Services\OperationalIssueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Services\ShipmentService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorPNG;

class ShipmentController extends Controller
{
    private ?bool $hasDestinationBranchColumn = null;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Shipment::class);
        $actor = $request->user();

        $perPage = min(max((int) $request->integer('per_page', 15), 5), 100);
        $sortBy = in_array($request->input('sort_by'), ['id', 'tracking_number', 'created_at', 'estimated_delivery_at', 'payment_status'], true)
            ? $request->input('sort_by')
            : 'created_at';
        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $query = Shipment::query()->with(['branch', 'destinationBranch', 'courier', 'status']);

        if (in_array($actor?->role, ['kasir'], true)) {
            $managerBranch = Branch::query()->find($actor->branch_id);

            if (! $managerBranch || ! $managerBranch->is_active) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('branch_id', $managerBranch->id);
            }
        }

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                    ->orWhere('sender_name', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('recipient_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->integer('status_id'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        $shipments = $query->orderBy($sortBy, $sortDir)->paginate($perPage)->appends($request->query());

        return response()->json($shipments);
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
    public function store(Request $request, ShipmentService $shipmentService, OperationalIssueService $operationalIssueService)
    {
        $this->authorize('create', Shipment::class);

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'destination_branch_id' => ['nullable', 'exists:branches,id'],
            'courier_id' => ['nullable', 'exists:users,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_phone' => ['required', 'string', 'max:30'],
            'sender_address' => ['required', 'string'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:30'],
            'recipient_address' => ['required', 'string'],
            'service_type' => ['required', Rule::in(['regular', 'express', 'same_day', 'economy'])],
            'total_weight_kg' => ['required', 'numeric', 'min:0.1'],
            'zone_id' => ['nullable', 'exists:zones,id'],
            'total_volume' => ['nullable', 'numeric', 'min:0'],
            'insurance_amount' => ['nullable', 'numeric', 'min:0'],
            'admin_fee' => ['nullable', 'numeric', 'min:0'],
            'subtotal_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'is_cod' => ['sometimes', 'boolean'],
            'cod_amount' => ['nullable', 'numeric', 'min:0'],
            'estimated_delivery_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'manual_override' => ['sometimes', 'boolean'],
            'manual_override_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $manualOverride = (bool) ($validated['manual_override'] ?? false);

        if ($manualOverride && empty($validated['manual_override_reason'])) {
            throw ValidationException::withMessages([
                'manual_override_reason' => 'Alasan override manual wajib diisi.',
            ]);
        }

        if ($manualOverride) {
            foreach (['subtotal_amount', 'insurance_amount', 'admin_fee', 'total_amount'] as $field) {
                if (! array_key_exists($field, $validated)) {
                    throw ValidationException::withMessages([
                        $field => 'Field ini wajib diisi ketika menggunakan manual override.',
                    ]);
                }
            }
        }

        if (empty($validated['zone_id']) && ! empty($validated['destination_branch_id'])) {
            $validated['zone_id'] = Branch::query()->whereKey($validated['destination_branch_id'])->value('zone_id');
        }

        if (! $manualOverride && empty($validated['zone_id'])) {
            throw ValidationException::withMessages([
                'zone_id' => 'Zona tujuan tidak ditemukan. Pilih cabang tujuan yang memiliki zona aktif.',
            ]);
        }

        try {
            if ($manualOverride) {
                $shipment = DB::transaction(function () use ($validated, $request, $shipmentService, $operationalIssueService) {
                    $shipment = $shipmentService->createShipment(array_merge($validated, [
                        'subtotal_amount' => $validated['subtotal_amount'],
                        'insurance_amount' => $validated['insurance_amount'],
                        'admin_fee' => $validated['admin_fee'],
                        'total_amount' => $validated['total_amount'],
                    ]), $request->user());

                    return $operationalIssueService->applyShipmentManualOverride(
                        $shipment,
                        $request->user(),
                        [
                            'subtotal_amount' => $validated['subtotal_amount'],
                            'insurance_amount' => $validated['insurance_amount'],
                            'admin_fee' => $validated['admin_fee'],
                            'total_amount' => $validated['total_amount'],
                        ],
                        $validated['manual_override_reason']
                    );
                });
            } else {
                $shipment = $shipmentService->createShipment($validated, $request->user());
            }
        } catch (\Throwable $throwable) {
            $operationalIssueService->recordError('shipment', 'Shipment gagal dibuat.', ['payload' => $validated], $request->user(), $throwable, 'critical');

            return response()->json([
                'message' => 'Shipment gagal dibuat.',
            ], 500);
        }

        return response()->json([
            'message' => 'Shipment berhasil dibuat.',
            'data' => $shipment,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Shipment $shipment)
    {
        $this->authorize('view', $shipment);

        $shipment->load(['items', 'status', 'trackings.status', 'payments']);

        return response()->json($shipment);
    }

    public function label(Shipment $shipment)
    {
        $this->authorize('view', $shipment);

        $shipment->load([
            'items',
            'status',
            'payments',
            'branch.zone',
            'destinationBranch.zone',
            'courier.branch',
            'vehicle.branch',
            'zone',
            'customer',
        ]);

        $generator = new BarcodeGeneratorPNG();
        $barcode = base64_encode($generator->getBarcode($shipment->tracking_number, $generator::TYPE_CODE_128, 2, 70));

        $pdf = Pdf::loadView('pdf.shipment-label', [
            'shipment' => $shipment,
            'barcode' => $barcode,
            'originBranch' => $shipment->branch,
            'destinationBranch' => $shipment->destinationBranch ?? ($shipment->zone_id ? Branch::query()->where('zone_id', $shipment->zone_id)->first() : null),
            'latestPayment' => $shipment->payments->sortByDesc('created_at')->first(),
        ])->setPaper('a5', 'portrait');

        return $pdf->download('resi-'.$shipment->tracking_number.'.pdf');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shipment $shipment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shipment $shipment, ShipmentService $shipmentService, AuditLogService $auditLogService, OperationalIssueService $operationalIssueService)
    {
        $this->authorize('update', $shipment);

        $supportsDestinationBranch = $this->supportsDestinationBranchColumn();

        $before = $shipment->only([
            'branch_id',
            'destination_branch_id',
            'courier_id',
            'vehicle_id',
            'status_id',
            'payment_status',
            'sender_name',
            'sender_phone',
            'sender_address',
            'recipient_name',
            'recipient_phone',
            'recipient_address',
            'service_type',
            'total_weight_kg',
            'total_volume',
            'subtotal_amount',
            'insurance_amount',
            'admin_fee',
            'total_amount',
            'estimated_delivery_at',
            'delivered_at',
            'notes',
        ]);

        $validated = $request->validate([
            'branch_id' => ['sometimes', 'exists:branches,id'],
            'destination_branch_id' => ['nullable', 'exists:branches,id'],
            'courier_id' => ['nullable', 'exists:users,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'status_id' => ['nullable', 'exists:shipment_statuses,id'],
            'payment_status' => ['nullable', Rule::in(['unpaid', 'pending', 'paid', 'failed', 'refunded'])],
            'sender_name' => ['nullable', 'string', 'max:255'],
            'sender_phone' => ['nullable', 'string', 'max:30'],
            'sender_address' => ['nullable', 'string'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_phone' => ['nullable', 'string', 'max:30'],
            'recipient_address' => ['nullable', 'string'],
            'service_type' => ['nullable', Rule::in(['regular', 'express', 'same_day', 'economy'])],
            'total_weight_kg' => ['nullable', 'numeric', 'min:0.1'],
            'total_volume' => ['nullable', 'numeric', 'min:0'],
            'insurance_amount' => ['nullable', 'numeric', 'min:0'],
            'admin_fee' => ['nullable', 'numeric', 'min:0'],
            'subtotal_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'estimated_delivery_at' => ['nullable', 'date'],
            'delivered_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'manual_override' => ['sometimes', 'boolean'],
            'manual_override_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $manualOverride = (bool) ($validated['manual_override'] ?? false);

        if ($manualOverride && empty($validated['manual_override_reason'])) {
            throw ValidationException::withMessages([
                'manual_override_reason' => 'Alasan override manual wajib diisi.',
            ]);
        }

        if (! $manualOverride && (array_key_exists('subtotal_amount', $validated) || array_key_exists('total_amount', $validated))) {
            throw ValidationException::withMessages([
                'manual_override' => 'Gunakan manual override jika ingin mengubah subtotal atau total secara manual.',
            ]);
        }

        if (array_key_exists('status_id', $validated)) {
            throw ValidationException::withMessages([
                'status_id' => 'Status shipment hanya boleh diubah melalui prosedur transisi status.',
            ]);
        }

        if (! empty($validated['destination_branch_id'])) {
            $validated['zone_id'] = Branch::query()->whereKey($validated['destination_branch_id'])->value('zone_id');
        }

        if (! $supportsDestinationBranch) {
            unset($validated['destination_branch_id']);
        }

        $amountFields = array_intersect_key($validated, array_flip(['branch_id', 'destination_branch_id', 'zone_id', 'service_type', 'total_weight_kg', 'insurance_amount', 'admin_fee']));

        if (! empty($amountFields) && ! $manualOverride) {
            $recalculated = $shipmentService->calculateTotalAmount(array_merge([
                'branch_id' => $validated['branch_id'] ?? $shipment->branch_id,
            ], [
                'zone_id' => $validated['zone_id'] ?? $shipment->zone_id,
                'service_type' => $validated['service_type'] ?? $shipment->service_type,
                'total_weight_kg' => $validated['total_weight_kg'] ?? $shipment->total_weight_kg,
                'insurance_amount' => $validated['insurance_amount'] ?? $shipment->insurance_amount,
                'admin_fee' => $validated['admin_fee'] ?? $shipment->admin_fee,
            ]));

            $validated['subtotal_amount'] = $recalculated['subtotal_amount'];
            $validated['insurance_amount'] = $recalculated['insurance_amount'];
            $validated['admin_fee'] = $recalculated['admin_fee'];
            $validated['total_amount'] = $recalculated['total_amount'];

            $shipment->payments()
                ->whereIn('status', ['pending', 'unpaid'])
                ->update(['amount' => $recalculated['total_amount']]);
        }

        if ($manualOverride) {
            $manualAmountFields = array_intersect_key($validated, array_flip([
                'subtotal_amount',
                'insurance_amount',
                'admin_fee',
                'total_amount',
            ]));

            if (count($manualAmountFields) !== 4) {
                throw ValidationException::withMessages([
                    'manual_override' => 'Semua field biaya wajib diisi ketika manual override aktif.',
                ]);
            }

            $shipment = $operationalIssueService->applyShipmentManualOverride(
                $shipment,
                $request->user(),
                $manualAmountFields,
                $validated['manual_override_reason']
            );
        }

        $shipment->fill(array_intersect_key($validated, array_flip([
            'branch_id',
            'destination_branch_id',
            'courier_id',
            'vehicle_id',
            'status_id',
            'payment_status',
            'sender_name',
            'sender_phone',
            'sender_address',
            'recipient_name',
            'recipient_phone',
            'recipient_address',
            'service_type',
            'total_weight_kg',
            'total_volume',
            'subtotal_amount',
            'insurance_amount',
            'admin_fee',
            'total_amount',
            'estimated_delivery_at',
            'delivered_at',
            'notes',
        ])));

        $shipment->update($validated);

        $auditLogService->record(
            'shipment.update',
            $shipment,
            $request->user(),
            $before,
            $shipment->fresh()->only([
                'destination_branch_id',
                'courier_id',
                'vehicle_id',
                'status_id',
                'payment_status',
                'service_type',
                'total_weight_kg',
                'total_volume',
                'subtotal_amount',
                'insurance_amount',
                'admin_fee',
                'total_amount',
                'estimated_delivery_at',
                'delivered_at',
                'notes',
            ]),
            'Shipment diperbarui secara manual.'
        );

        return response()->json([
            'message' => 'Shipment berhasil diperbarui.',
            'data' => $shipment->fresh(),
        ]);
    }

    private function supportsDestinationBranchColumn(): bool
    {
        if ($this->hasDestinationBranchColumn !== null) {
            return $this->hasDestinationBranchColumn;
        }

        $this->hasDestinationBranchColumn = Schema::hasColumn('shipments', 'destination_branch_id');

        return $this->hasDestinationBranchColumn;
    }

    public function assignCourier(Request $request, Shipment $shipment, ShipmentService $shipmentService)
    {
        $this->authorize('update', $shipment);

        $validated = $request->validate([
            'courier_id' => ['nullable', 'exists:users,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
        ]);

        $shipment = $shipmentService->assignCourier(
            $shipment,
            $validated['courier_id'] ?? null,
            $validated['vehicle_id'] ?? null,
            $request->user()?->id
        );

        return response()->json([
            'message' => 'Kurir berhasil ditugaskan.',
            'data' => $shipment,
        ]);
    }

    public function transitionStatus(Request $request, Shipment $shipment, ShipmentService $shipmentService)
    {
        $this->authorize('update', $shipment);

        $validated = $request->validate([
            'status_code' => ['required', 'string', 'max:40'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'force_transition' => ['sometimes', 'boolean'],
            'override_reason' => ['nullable', 'string', 'max:255'],
        ]);

        if (! empty($validated['force_transition']) && $request->user()?->role !== 'admin') {
            throw ValidationException::withMessages([
                'force_transition' => 'Hanya admin yang boleh melakukan override status final.',
            ]);
        }

        if (! empty($validated['force_transition']) && empty($validated['override_reason'])) {
            throw ValidationException::withMessages([
                'override_reason' => 'Alasan override wajib diisi.',
            ]);
        }

        $shipment = $shipmentService->transitionStatus(
            $shipment,
            $validated['status_code'],
            $request->user()?->id,
            $validated['location'] ?? null,
            $validated['notes'] ?? null,
            (bool) ($validated['force_transition'] ?? false),
            $validated['override_reason'] ?? null
        );

        return response()->json([
            'message' => 'Status shipment berhasil diperbarui.',
            'data' => $shipment,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shipment $shipment, AuditLogService $auditLogService)
    {
        $this->authorize('delete', $shipment);

        $before = $shipment->only(['tracking_number', 'branch_id', 'status_id', 'payment_status']);

        $auditLogService->record(
            'shipment.delete',
            $shipment,
            request()->user(),
            $before,
            [],
            'Shipment dihapus.'
        );

        $shipment->delete();

        return response()->json([
            'message' => 'Shipment berhasil dihapus.',
        ]);
    }

}
