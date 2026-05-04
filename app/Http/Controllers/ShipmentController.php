<?php

namespace App\Http\Controllers;

use App\Models\AdminTask;
use App\Models\Branch;
use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\ApprovalWorkflowService;
use App\Services\OperationalIssueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Services\ShipmentService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ShipmentController extends Controller
{

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

        if (in_array($actor?->role, ['kasir', 'manager'], true)) {
            $branch = Branch::query()->find($actor->branch_id);

            if (! $branch || ! $branch->is_active) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('branch_id', $branch->id);
            }
        }

        if ($actor?->role === 'courier') {
            $query->where('courier_id', $actor->id);
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

        if ($request->expectsJson()) {
            return response()->json($shipments);
        }

        $statuses = \App\Models\ShipmentStatus::all();

        return view('shipments.index', compact('shipments', 'statuses'));
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
        $actor = $request->user();

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
            'total_volume' => ['nullable', 'numeric', 'min:0'],
            'insurance_amount' => ['nullable', 'numeric', 'min:0'],
            'admin_fee' => ['nullable', 'numeric', 'min:0'],
            'subtotal_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', Rule::in(['midtrans', 'cash', 'transfer', 'e_wallet'])],
            'estimated_delivery_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'manual_override' => ['sometimes', 'boolean'],
            'manual_override_requires_approval' => ['sometimes', 'boolean'],
            'manual_override_reason' => ['nullable', 'string', 'max:500'],
        ]);

        // Enforce branch scope for manager & kasir
        if (in_array($actor?->role, ['manager', 'kasir'], true)) {
            if ((int) $validated['branch_id'] !== (int) $actor->branch_id) {
                abort(403, 'Anda hanya bisa membuat shipment di cabang Anda.');
            }
        }

        $manualOverride = (bool) ($validated['manual_override'] ?? false);
        $manualOverrideRequiresApproval = (bool) ($validated['manual_override_requires_approval'] ?? false);

        if ($manualOverride) {
            $this->assertManualCorrectionPermission($actor, (int) $validated['branch_id']);
        }

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


        try {
            if ($manualOverride && $manualOverrideRequiresApproval) {
                $shipment = $shipmentService->createShipment(array_merge($validated, [
                    'manual_override' => false,
                ]), $request->user());

                $pricingTask = $shipmentService->requestPricingOverrideApproval(
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

                return response()->json([
                    'message' => 'Shipment berhasil dibuat. Override tarif menunggu approval admin.',
                    'data' => $shipment->fresh(),
                    'pricing_override_task' => [
                        'task_id' => $pricingTask->id,
                        'status' => $pricingTask->status,
                    ],
                ], 202);
            }

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
    public function show(Shipment $shipment, Request $request)
    {
        $this->authorize('view', $shipment);

        $shipment->load(['branch', 'destinationBranch', 'courier', 'status', 'items', 'payments', 'trackings.status', 'operationalProofs']);

        if ($request->expectsJson()) {
            return response()->json($shipment);
        }

        return view('shipments.show', compact('shipment'));
    }

    public function label(Shipment $shipment)
    {
        $this->authorize('view', $shipment);

        $shipment->load([
            'items',
            'status',
            'payments',
            'branch',
            'destinationBranch',
            'courier.branch',
            'vehicle.branch',
            'customer',
        ]);

        $qrcode = base64_encode(QrCode::format('svg')->size(150)->margin(1)->generate($shipment->tracking_number));

        $pdf = Pdf::loadView('pdf.shipment-label', [
            'shipment'         => $shipment,
            'qrcode'           => $qrcode,
            'originBranch'     => $shipment->branch,
            'destinationBranch'=> $shipment->destinationBranch,
            'latestPayment'    => $shipment->payments->sortByDesc('created_at')->first(),
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
        $actor = $request->user();

        // Shipment update sekarang diizinkan langsung untuk kasir (tanpa approval)
        // logic approval lama dihapus sesuai request:
        // if ($actor?->role === 'kasir') {
        //     return $this->requestKasirEditApproval($request, $shipment, 'shipment');
        // }

        if ($actor?->role === 'courier') {
            abort(403, 'Kurir hanya boleh update status melalui endpoint tracking/transisi status.');
        }

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
            'manual_override_requires_approval' => ['sometimes', 'boolean'],
            'manual_override_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $manualOverride = (bool) ($validated['manual_override'] ?? false);
        $manualOverrideRequiresApproval = (bool) ($validated['manual_override_requires_approval'] ?? false);
        $pricingApprovalTask = null;

        if ($manualOverride) {
            $targetBranchId = (int) ($validated['branch_id'] ?? $shipment->branch_id);
            $this->assertManualCorrectionPermission($actor, $targetBranchId);
        }

        if ($manualOverride && empty($validated['manual_override_reason'])) {
            throw ValidationException::withMessages([
                'manual_override_reason' => 'Alasan override manual wajib diisi.',
            ]);
        }

        if (! $manualOverride && $manualOverrideRequiresApproval) {
            throw ValidationException::withMessages([
                'manual_override_requires_approval' => 'Aktifkan manual_override untuk membuat request approval override tarif.',
            ]);
        }

        if (! $manualOverride && (array_key_exists('subtotal_amount', $validated) || array_key_exists('total_amount', $validated))) {
            throw ValidationException::withMessages([
                'manual_override' => 'Gunakan manual override jika ingin mengubah subtotal atau total secara manual.',
            ]);
        }

        if (array_key_exists('status_id', $validated) && !in_array($actor?->role, ['manager', 'admin', 'kasir'], true)) {
            throw ValidationException::withMessages([
                'status_id' => 'Status shipment hanya boleh diubah melalui prosedur transisi status.',
            ]);
        }

        // Check if destination branch is provided and valid
        if (array_key_exists('destination_branch_id', $validated) && empty($validated['destination_branch_id'])) {
            unset($validated['destination_branch_id']);
        }

        $amountFields = array_intersect_key($validated, array_flip(['branch_id', 'destination_branch_id', 'service_type', 'total_weight_kg', 'insurance_amount', 'admin_fee']));

        if (! empty($amountFields) && ! $manualOverride) {
            $recalculated = $shipmentService->calculateTotalAmount(array_merge([
                'branch_id' => $validated['branch_id'] ?? $shipment->branch_id,
                'destination_branch_id' => $validated['destination_branch_id'] ?? $shipment->destination_branch_id,
            ], [
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

        if ($manualOverride && ! $manualOverrideRequiresApproval) {
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

        // Handle Status Transition if status_id changed and actor is manager/admin
        if (array_key_exists('status_id', $validated) && (int)$validated['status_id'] !== (int)$shipment->status_id) {
            $newStatus = ShipmentStatus::find($validated['status_id']);
            if ($newStatus) {
                // We use transitionStatus to ensure side effects are handled
                $shipmentService->transitionStatus(
                    $shipment,
                    $newStatus->code,
                    $actor->id,
                    $request->input('location') ?? $shipment->branch?->name ?? 'Update Manual',
                    $request->input('tracking_notes') ?? 'Status diperbarui secara manual melalui dashboard.',
                    true, // force transition for managers/admins/kasir
                    $validated['manual_override_reason'] ?? 'Update manual'
                );
            }
        }

        if ($manualOverride && $manualOverrideRequiresApproval) {
            $manualAmountFields = array_intersect_key($validated, array_flip([
                'subtotal_amount',
                'insurance_amount',
                'admin_fee',
                'total_amount',
            ]));

            if (count($manualAmountFields) !== 4) {
                throw ValidationException::withMessages([
                    'manual_override' => 'Semua field biaya wajib diisi ketika request approval override aktif.',
                ]);
            }

            $pricingApprovalTask = $shipmentService->requestPricingOverrideApproval(
                $shipment,
                $request->user(),
                $manualAmountFields,
                $validated['manual_override_reason']
            );
        }

        unset(
            $validated['manual_override'],
            $validated['manual_override_reason'],
            $validated['manual_override_requires_approval']
        );

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

        // Persist changes
        $shipment->update($validated);
        $freshShipment = $shipment->fresh();
        $auditLogService->record(
            'shipment.update',
            $shipment,
            $request->user(),
            $before,
            $freshShipment->only([
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

        if ($pricingApprovalTask) {
            return response()->json([
                'message' => 'Permintaan override tarif dikirim. Menunggu approval admin.',
                'data' => $shipment->fresh(),
                'pricing_override_task' => [
                    'task_id' => $pricingApprovalTask->id,
                    'status' => $pricingApprovalTask->status,
                ],
            ], 202);
        }

        return response()->json([
            'message' => 'Shipment berhasil diperbarui.',
            'data' => $shipment->fresh(),
        ]);
    }

    public function requestPricingOverride(Request $request, Shipment $shipment, ShipmentService $shipmentService)
    {
        $this->authorize('requestPricingOverride', $shipment);
        $actor = $request->user();

        $this->assertManualCorrectionPermission($actor, (int) $shipment->branch_id);

        $validated = $request->validate([
            'subtotal_amount' => ['required', 'numeric', 'min:0'],
            'insurance_amount' => ['required', 'numeric', 'min:0'],
            'admin_fee' => ['required', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $task = $shipmentService->requestPricingOverrideApproval(
            $shipment,
            $actor,
            [
                'subtotal_amount' => $validated['subtotal_amount'],
                'insurance_amount' => $validated['insurance_amount'],
                'admin_fee' => $validated['admin_fee'],
                'total_amount' => $validated['total_amount'],
            ],
            $validated['reason']
        );

        return response()->json([
            'message' => 'Request override tarif berhasil dibuat.',
            'data' => [
                'shipment_id' => $shipment->id,
                'pricing_approval_status' => 'pending',
                'task_id' => $task->id,
                'task_status' => $task->status,
            ],
        ], 202);
    }

    public function approvePricingOverride(Request $request, Shipment $shipment, ShipmentService $shipmentService)
    {
        $this->authorize('approvePricingOverride', $shipment);

        if ($request->user()?->role !== 'admin') {
            throw ValidationException::withMessages([
                'approver' => 'Hanya admin yang boleh menyetujui override tarif.',
            ]);
        }

        $validated = $request->validate([
            'approval_note' => ['nullable', 'string', 'max:500'],
        ]);

        $updatedShipment = $shipmentService->approvePricingOverrideRequest(
            $shipment,
            $request->user(),
            $validated['approval_note'] ?? null
        );

        return response()->json([
            'message' => 'Override tarif disetujui dan diterapkan.',
            'data' => $updatedShipment,
        ]);
    }

    public function rejectPricingOverride(Request $request, Shipment $shipment, ShipmentService $shipmentService)
    {
        $this->authorize('approvePricingOverride', $shipment);

        if ($request->user()?->role !== 'admin') {
            throw ValidationException::withMessages([
                'approver' => 'Hanya admin yang boleh menolak override tarif.',
            ]);
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $updatedShipment = $shipmentService->rejectPricingOverrideRequest(
            $shipment,
            $request->user(),
            $validated['rejection_reason']
        );

        return response()->json([
            'message' => 'Override tarif ditolak. Shipment kembali menggunakan tarif otomatis.',
            'data' => $updatedShipment,
        ]);
    }

    public function pricingApprovalInbox(Request $request)
    {
        $actor = $request->user();

        if (! in_array($actor?->role, ['admin', 'manager'], true)) {
            abort(403, 'Hanya admin/manager yang dapat melihat inbox approval pricing.');
        }

        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected', 'all'])],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $status = $validated['status'] ?? 'pending';
        $perPage = (int) ($validated['per_page'] ?? 25);

        $query = AdminTask::query()
            ->where('task_type', 'shipment_pricing_override_approval')
            ->with(['creator:id,name,email', 'assignee:id,name,email'])
            ->latest();

        if ($status === 'pending') {
            $query->whereIn('status', ['pending', 'in_progress']);
        }

        if ($status === 'approved') {
            $query
                ->where('status', 'completed')
                ->where(function ($builder) {
                    $builder->where('result->decision', 'approved')
                        ->orWhereNull('result->decision');
                });
        }

        if ($status === 'rejected') {
            $query->where('status', 'cancelled')->where('result->decision', 'rejected');
        }

        $paginator = $query->paginate($perPage);

        $shipmentIds = collect($paginator->items())
            ->map(fn (AdminTask $task) => (int) ($task->action_data['shipment_id'] ?? 0))
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        $shipments = Shipment::query()
            ->whereIn('id', $shipmentIds)
            ->get(['id', 'tracking_number', 'branch_id', 'pricing_mode', 'pricing_approval_status', 'auto_total_amount', 'corrected_total_amount', 'total_amount'])
            ->keyBy('id');

        $items = collect($paginator->items())->map(function (AdminTask $task) use ($shipments) {
            $shipmentId = (int) ($task->action_data['shipment_id'] ?? 0);
            $shipment = $shipmentId ? $shipments->get($shipmentId) : null;

            return [
                'task_id' => $task->id,
                'task_status' => $task->status,
                'priority' => $task->priority,
                'reason' => $task->description,
                'current_amounts' => $task->action_data['current_amounts'] ?? null,
                'proposed_amounts' => $task->action_data['proposed_amounts'] ?? null,
                'decision' => $task->result['decision'] ?? null,
                'decision_note' => $task->result['approval_note'] ?? ($task->result['reason'] ?? null),
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,
                'creator' => $task->creator,
                'assignee' => $task->assignee,
                'shipment' => $shipment ? [
                    'id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                    'branch_id' => $shipment->branch_id,
                    'pricing_mode' => $shipment->pricing_mode,
                    'pricing_approval_status' => $shipment->pricing_approval_status,
                    'auto_total_amount' => $shipment->auto_total_amount,
                    'corrected_total_amount' => $shipment->corrected_total_amount,
                    'effective_total_amount' => $shipment->total_amount,
                    'detail_endpoint' => route('shipments.show', $shipment),
                ] : null,
            ];
        })->values();

        $summaryBase = AdminTask::query()->where('task_type', 'shipment_pricing_override_approval');

        return response()->json([
            'status' => 'success',
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_next_page' => $paginator->hasMorePages(),
                'applied_filter' => $status,
            ],
            'summary' => [
                'pending' => (clone $summaryBase)->whereIn('status', ['pending', 'in_progress'])->count(),
                'approved' => (clone $summaryBase)->where('status', 'completed')->count(),
                'rejected' => (clone $summaryBase)->where('status', 'cancelled')->where('result->decision', 'rejected')->count(),
            ],
            'items' => $items,
        ]);
    }

    private function requestKasirEditApproval(Request $request, $model, string $modelType)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $changes = $request->except(['reason', '_token', '_method']);

        $task = app(ApprovalWorkflowService::class)->requestKasirEditApproval(
            $model,
            $request->user(),
            $changes,
            $validated['reason']
        );

        return response()->json([
            'message' => 'Permintaan perubahan dikirim ke manager untuk disetujui.',
            'data' => [
                'task_id' => $task->id,
                'task_status' => $task->status,
            ],
        ], 202);
    }

    public function assignCourier(Request $request, Shipment $shipment, ShipmentService $shipmentService, ApprovalWorkflowService $approvalWorkflowService)
    {
        $this->authorize('update', $shipment);

        if ($request->user()?->role === 'courier') {
            abort(403, 'Kurir tidak memiliki akses assignment kurir/kendaraan.');
        }

        $validated = $request->validate([
            'courier_id' => ['nullable', 'exists:users,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
        ]);

        $shipment->loadMissing('status');

        if (! in_array($shipment->status?->code, [null, 'pending'], true)) {
            $task = $approvalWorkflowService->requestShipmentReassignApproval(
                $shipment,
                $request->user(),
                $validated['courier_id'] ?? null,
                $validated['vehicle_id'] ?? null,
                'Shipment sudah berjalan. Perubahan assignment menunggu approval.'
            );

            return response()->json([
                'message' => 'Reassign shipment menunggu approval.',
                'data' => [
                    'shipment_id' => $shipment->id,
                    'task_id' => $task->id,
                    'task_status' => $task->status,
                ],
            ], 202);
        }

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

    public function transitionStatus(Request $request, Shipment $shipment, ShipmentService $shipmentService, ApprovalWorkflowService $approvalWorkflowService)
    {
        $this->authorize('transition', $shipment);

        $validated = $request->validate([
            'status_code' => ['required', 'string', 'max:40'],
            'location' => ['nullable', 'string', 'max:255'],
            'gps_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'gps_accuracy_m' => ['nullable', 'numeric', 'min:0'],
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

        $shipment->loadMissing('status');
        $finalStatusCodes = config('expedition.shipment_status_flow.final_statuses', ['delivered', 'cancelled', 'returned']);

        if (in_array($validated['status_code'], $finalStatusCodes, true)) {
            $task = $approvalWorkflowService->requestShipmentFinalStatusApproval(
                $shipment,
                $request->user(),
                $validated['status_code'],
                [
                    'location' => $validated['location'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'override_reason' => $validated['override_reason'] ?? 'Perubahan status final menunggu approval.',
                ]
            );

            return response()->json([
                'message' => 'Perubahan status final menunggu approval.',
                'data' => [
                    'shipment_id' => $shipment->id,
                    'status_code' => $validated['status_code'],
                    'task_id' => $task->id,
                    'task_status' => $task->status,
                ],
            ], 202);
        }

        $shipment = $shipmentService->transitionStatus(
            $shipment,
            $validated['status_code'],
            $request->user()?->id,
            $validated['location'] ?? null,
            $validated['notes'] ?? null,
            (bool) ($validated['force_transition'] ?? false),
            $validated['override_reason'] ?? null,
            isset($validated['gps_lat']) ? (float) $validated['gps_lat'] : null,
            isset($validated['gps_lng']) ? (float) $validated['gps_lng'] : null,
            isset($validated['gps_accuracy_m']) ? (float) $validated['gps_accuracy_m'] : null
        );

        return response()->json([
            'message' => 'Status shipment berhasil diperbarui.',
            'data' => $shipment,
        ]);
    }

    private function assertManualCorrectionPermission(?User $actor, int $targetBranchId): void
    {
        if (! $actor) {
            abort(403, 'Akses ditolak.');
        }

        if ($actor->role === 'admin') {
            return;
        }

        if ($actor->role === 'kasir' && (int) $actor->branch_id === $targetBranchId) {
            return;
        }

        if ($actor->role === 'manager' && (int) $actor->branch_id === $targetBranchId) {
            return;
        }

        abort(403, 'Anda tidak memiliki hak untuk koreksi manual pada shipment ini.');
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
