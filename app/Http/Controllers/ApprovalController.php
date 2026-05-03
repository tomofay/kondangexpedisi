<?php

namespace App\Http\Controllers;

use App\Models\AdminTask;
use App\Models\RateCardApproval;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $actor = $request->user();

        abort_unless($actor && in_array($actor->role, ['admin', 'manager'], true), 403, 'Hanya admin/manager yang dapat melihat approval queue.');

        $validated = $request->validate([
            'scope' => ['nullable', Rule::in(['all', 'rate_card', 'shipment_final_status', 'shipment_reassign', 'payment_manual_status', 'approve_rate_card', 'shipment_final_status_approval', 'shipment_reassign_approval'])],
            'status' => ['nullable', Rule::in(['pending', 'in_progress', 'completed', 'cancelled', 'all', 'rejected'])],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $scope = $validated['scope'] ?? 'all';
        $status = $validated['status'] ?? 'pending';
        $perPage = (int) ($validated['per_page'] ?? 25);

        $taskQuery = AdminTask::query()->with(['creator:id,name,email', 'assignee:id,name,email'])->latest();

        if ($actor->role === 'manager') {
            $taskQuery->where(function ($q) use ($actor) {
                $q->whereHas('creator', fn ($u) => $u->where('branch_id', $actor->branch_id))
                    ->orWhere('action_data->branch_id', $actor->branch_id)
                    ->orWhereRaw("JSON_EXTRACT(action_data, '$.shipment_id') IN (SELECT id FROM shipments WHERE branch_id = ?)", [$actor->branch_id]);
            });
        }

        if ($status !== 'all') {
            $taskQuery->where('status', $status === 'pending' ? ['pending', 'in_progress'] : $status);
        }

        if ($scope !== 'all') {
            $taskQuery->where('task_type', match ($scope) {
                'shipment_final_status', 'shipment_final_status_approval' => 'shipment_final_status_approval',
                'shipment_reassign', 'shipment_reassign_approval' => 'shipment_reassign_approval',
                'payment_manual_status', 'payment_manual_status_approval' => 'payment_manual_status_approval',
                'rate_card', 'approve_rate_card' => 'approve_rate_card',
                default => $scope,
            });
        }

        $tasks = $taskQuery->paginate($perPage);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $tasks,
            ]);
        }

        return view('approvals.index', compact('tasks'));
    }

    public function approveTask(Request $request, AdminTask $task, ApprovalWorkflowService $approvalWorkflowService): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor && in_array($actor->role, ['admin', 'manager'], true), 403, 'Hanya admin/manager yang dapat menyetujui approval.');

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $approvalWorkflowService->approveAdminTask($task, $actor, $validated['note'] ?? null);

        return response()->json([
            'status' => 'success',
            'message' => 'Approval sensitif disetujui.',
            'data' => $result,
        ]);
    }

    public function rejectTask(Request $request, AdminTask $task, ApprovalWorkflowService $approvalWorkflowService): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor && in_array($actor->role, ['admin', 'manager'], true), 403, 'Hanya admin/manager yang dapat menolak approval.');

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $result = $approvalWorkflowService->rejectAdminTask($task, $actor, $validated['reason']);

        return response()->json([
            'status' => 'success',
            'message' => 'Approval sensitif ditolak.',
            'data' => $result,
        ]);
    }

    public function rateCardIndex(Request $request): JsonResponse
    {
        $actor = $request->user();

        abort_unless($actor && in_array($actor->role, ['admin', 'manager'], true), 403, 'Hanya admin/manager yang dapat melihat approval rate card.');

        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected', 'all'])],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $status = $validated['status'] ?? 'pending';
        $perPage = (int) ($validated['per_page'] ?? 25);

        $query = RateCardApproval::query()
            ->with(['rateCard.originBranch', 'rateCard.destinationBranch', 'requester:id,name,email', 'approver:id,name,email'])
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->paginate($perPage),
        ]);
    }

    public function approveRateCard(Request $request, RateCardApproval $rateCardApproval, ApprovalWorkflowService $approvalWorkflowService): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor && $actor->role === 'admin', 403, 'Hanya admin yang dapat menyetujui perubahan rate card.');

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $rateCard = $approvalWorkflowService->approveRateCardApproval($rateCardApproval, $actor, $validated['note'] ?? null);

        return response()->json([
            'status' => 'success',
            'message' => 'Perubahan rate card disetujui.',
            'data' => $rateCard,
        ]);
    }

    public function rejectRateCard(Request $request, RateCardApproval $rateCardApproval, ApprovalWorkflowService $approvalWorkflowService): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor && $actor->role === 'admin', 403, 'Hanya admin yang dapat menolak perubahan rate card.');

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $approval = $approvalWorkflowService->rejectRateCardApproval($rateCardApproval, $actor, $validated['reason']);

        return response()->json([
            'status' => 'success',
            'message' => 'Perubahan rate card ditolak.',
            'data' => $approval,
        ]);
    }
}
