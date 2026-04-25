<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Payment;
use App\Services\AuditLogService;
use App\Services\OperationalIssueService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Payment::class);
        $actor = $request->user();

        $perPage = min(max((int) $request->integer('per_page', 15), 5), 100);
        $sortBy = in_array($request->input('sort_by'), ['id', 'amount', 'status', 'method', 'created_at'], true)
            ? $request->input('sort_by')
            : 'created_at';
        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $query = Payment::query()->with(['shipment', 'processor']);

        if (in_array($actor?->role, ['kasir'], true)) {
            $managerBranch = Branch::query()->find($actor->branch_id);

            if (! $managerBranch || ! $managerBranch->is_active) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereHas('shipment', fn ($shipmentQuery) => $shipmentQuery->where('branch_id', $managerBranch->id));
            }
        }

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('method', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('midtrans_order_id', 'like', "%{$search}%")
                    ->orWhereHas('shipment', fn ($shipmentQuery) => $shipmentQuery->where('tracking_number', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }

        $payments = $query->orderBy($sortBy, $sortDir)->paginate($perPage)->appends($request->query());

        return response()->json($payments);
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
        $this->authorize('create', Payment::class);

        $validated = $request->validate([
            'shipment_id' => ['required', 'exists:shipments,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'method' => ['required', Rule::in(['midtrans', 'cash', 'transfer', 'e_wallet', 'cod'])],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $payment = Payment::query()->create([
            ...$validated,
            'status' => 'pending',
            'processed_by' => $request->user()?->id,
        ]);

        $auditLogService->record(
            'payment.create',
            $payment,
            $request->user(),
            [],
            $payment->fresh()->only(['shipment_id', 'customer_id', 'method', 'status', 'amount']),
            'Payment baru dibuat.'
        );

        return response()->json([
            'message' => 'Payment berhasil dibuat.',
            'data' => $payment,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);

        return response()->json($payment->load(['shipment', 'processor']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment, AuditLogService $auditLogService, OperationalIssueService $operationalIssueService)
    {
        $this->authorize('update', $payment);

        $before = $payment->only(['status', 'method', 'notes']);

        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'settlement', 'deny', 'expire', 'cancel', 'refund', 'failed'])],
            'method' => ['nullable', Rule::in(['midtrans', 'cash', 'transfer', 'e_wallet', 'cod'])],
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
            $payment = $operationalIssueService->applyPaymentManualOverride(
                $payment,
                $request->user(),
                ['status' => $validated['status'] ?? $payment->status],
                $validated['manual_override_reason']
            );
        } else {
            $payment->update($validated);
        }

        $auditLogService->record(
            'payment.update',
            $payment,
            $request->user(),
            $before,
            $payment->fresh()->only(['status', 'method', 'notes']),
            'Payment diperbarui.'
        );

        return response()->json([
            'message' => 'Payment berhasil diperbarui.',
            'data' => $payment->fresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment, AuditLogService $auditLogService)
    {
        $this->authorize('delete', $payment);

        $auditLogService->record(
            'payment.delete',
            $payment,
            request()->user(),
            $payment->only(['shipment_id', 'status', 'amount']),
            [],
            'Payment dihapus.'
        );

        $payment->delete();

        return response()->json([
            'message' => 'Payment berhasil dihapus.',
        ]);
    }
}
