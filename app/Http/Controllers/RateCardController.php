<?php

namespace App\Http\Controllers;

use App\Models\RateCard;
use App\Services\ApprovalWorkflowService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class RateCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', RateCard::class);

        $perPage = min(max((int) $request->integer('per_page', 15), 5), 100);
        $sortBy = in_array($request->input('sort_by'), ['id', 'service_type', 'base_price', 'per_kg_price', 'created_at'], true)
            ? $request->input('sort_by')
            : 'created_at';
        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $query = RateCard::query()->with(['originBranch', 'destinationBranch']);

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('service_type', 'like', "%{$search}%")
                    ->orWhereHas('originBranch', fn ($bQuery) => $bQuery->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                    ->orWhereHas('destinationBranch', fn ($bQuery) => $bQuery->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('origin_branch_id')) {
            $query->where('origin_branch_id', $request->integer('origin_branch_id'));
        }

        if ($request->filled('destination_branch_id')) {
            $query->where('destination_branch_id', $request->integer('destination_branch_id'));
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->input('service_type'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOL));
        }

        return response()->json(
            $query->orderBy($sortBy, $sortDir)->paginate($perPage)->appends($request->query())
        );
    }

    /**
     * Store a newly created resource in storage.
     * Admin: direct create. Manager: via approval ke admin.
     */
    public function store(Request $request, ApprovalWorkflowService $approvalWorkflowService, AuditLogService $auditLogService)
    {
        $this->authorize('create', RateCard::class);
        $actor = $request->user();

        $validated = $request->validate([
            'origin_branch_id' => ['required', 'exists:branches,id'],
            'destination_branch_id' => ['required', 'exists:branches,id'],
            'service_type' => ['required', 'in:regular,express,same_day,economy'],
            'min_weight_kg' => ['required', 'numeric', 'min:0'],
            'max_weight_kg' => ['nullable', 'numeric', 'gte:min_weight_kg'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'per_kg_price' => ['required', 'numeric', 'min:0'],
            'insurance_fee' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        // Manager harus via approval ke admin
        if ($actor?->role === 'manager') {
            $task = $approvalWorkflowService->requestKasirEditApproval(
                new RateCard(),
                $actor,
                $validated,
                $validated['reason'] ?? 'Manager mengajukan pembuatan rate card baru.'
            );

            return response()->json([
                'message' => 'Pengajuan rate card baru dikirim ke admin untuk disetujui.',
                'data' => [
                    'task_id' => $task->id,
                    'task_status' => $task->status,
                ],
            ], 202);
        }

        // Admin langsung create
        $rateCard = RateCard::query()->create($validated);

        $auditLogService->record(
            'rate_card.create',
            $rateCard,
            $actor,
            [],
            $rateCard->fresh()->only(['origin_branch_id', 'destination_branch_id', 'service_type', 'base_price', 'per_kg_price']),
            'Rate card baru dibuat oleh admin.'
        );

        return response()->json(['message' => 'Rate card created.', 'data' => $rateCard->load(['originBranch', 'destinationBranch'])], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(RateCard $rateCard)
    {
        $this->authorize('view', $rateCard);

        return response()->json($rateCard->load(['originBranch', 'destinationBranch']));
    }

    /**
     * Update the specified resource in storage.
     * Admin: direct update. Manager: via approval ke admin.
     */
    public function update(Request $request, RateCard $rateCard, ApprovalWorkflowService $approvalWorkflowService, AuditLogService $auditLogService)
    {
        $this->authorize('update', $rateCard);
        $actor = $request->user();

        $validated = $request->validate([
            'origin_branch_id' => ['sometimes', 'exists:branches,id'],
            'destination_branch_id' => ['sometimes', 'exists:branches,id'],
            'service_type' => ['sometimes', 'in:regular,express,same_day,economy'],
            'min_weight_kg' => ['sometimes', 'numeric', 'min:0'],
            'max_weight_kg' => ['nullable', 'numeric', 'gte:min_weight_kg'],
            'base_price' => ['sometimes', 'numeric', 'min:0'],
            'per_kg_price' => ['sometimes', 'numeric', 'min:0'],
            'insurance_fee' => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        // Manager harus via approval ke admin
        if ($actor?->role === 'manager') {
            $approval = $approvalWorkflowService->requestRateCardApproval(
                $rateCard,
                $actor,
                collect($validated)->except('reason')->toArray(),
                $validated['reason'] ?? 'Manager mengajukan perubahan rate card.'
            );

            return response()->json([
                'message' => 'Pengajuan perubahan rate card dikirim ke admin untuk disetujui.',
                'data' => [
                    'approval_id' => $approval->id,
                    'approval_status' => $approval->status,
                ],
            ], 202);
        }

        // Admin langsung update
        $before = $rateCard->only(array_keys($validated));
        $rateCard->update(collect($validated)->except('reason')->toArray());

        $auditLogService->record(
            'rate_card.update',
            $rateCard,
            $actor,
            $before,
            $rateCard->fresh()->only(array_keys($validated)),
            'Rate card diperbarui oleh admin.'
        );

        return response()->json([
            'message' => 'Rate card updated.',
            'data' => $rateCard->fresh()->load(['originBranch', 'destinationBranch']),
        ]);
    }

    /**
     * Remove the specified resource from storage. Admin only.
     */
    public function destroy(RateCard $rateCard, AuditLogService $auditLogService)
    {
        $this->authorize('delete', $rateCard);

        $auditLogService->record(
            'rate_card.delete',
            $rateCard,
            request()->user(),
            $rateCard->only(['origin_branch_id', 'destination_branch_id', 'service_type', 'base_price']),
            [],
            'Rate card dihapus.'
        );

        $rateCard->delete();

        return response()->json(['message' => 'Rate card deleted.']);
    }
}
