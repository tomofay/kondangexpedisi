<?php

namespace App\Http\Controllers;

use App\Models\AdminTask;
use App\Models\Branch;
use App\Models\ErrorLog;
use App\Models\Payment;
use App\Models\RateCard;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        abort_unless($user && in_array($user->role, ['manager', 'admin', 'kasir'], true), 403, 'Dashboard web hanya untuk manager, admin, dan kasir.');

        return match ($user->role) {
            'manager' => view('dashboard.manager', [
                'user' => $user,
                'level' => 1,
                'metrics' => $this->managerMetrics($user),
            ]),
            'admin' => view('dashboard.admin', [
                'user' => $user,
                'level' => 2,
                'metrics' => $this->adminMetrics($user),
            ]),
            'kasir' => view('dashboard.kasir', [
                'user' => $user,
                'level' => 3,
                'metrics' => $this->kasirMetrics($user),
            ]),
            default => abort(403, 'Role tidak diizinkan.'),
        };
    }

    private function managerMetrics(User $user): array
    {
        $finalStatuses = ['delivered', 'cancelled', 'returned'];
        $branchId = $user->branch_id;

        $shipmentQuery = Shipment::query();
        $taskQuery = AdminTask::query();
        $paymentQuery = Payment::query();

        if ($branchId) {
            $shipmentQuery->where('branch_id', $branchId);
            $taskQuery->where(function($q) use ($branchId) {
                $q->whereHas('creator', fn($u) => $u->where('branch_id', $branchId))
                  ->orWhere('action_data->branch_id', $branchId);
            });
            $paymentQuery->whereHas('shipment', fn($q) => $q->where('branch_id', $branchId));
        }

        return [
            'branch_name' => $user->branch?->name,
            'shipments_today' => (clone $shipmentQuery)->whereDate('created_at', today())->count(),
            'shipments_in_progress' => (clone $shipmentQuery)
                ->whereHas('status', fn ($query) => $query->whereIn('code', ['pending', 'in_transit', 'out_for_delivery']))
                ->count(),
            'shipments_overdue' => (clone $shipmentQuery)
                ->whereNotNull('estimated_delivery_at')
                ->where('estimated_delivery_at', '<', now())
                ->whereDoesntHave('status', fn ($query) => $query->whereIn('code', $finalStatuses))
                ->count(),
            'pending_approvals' => (clone $taskQuery)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'payments_pending' => (clone $paymentQuery)->where('status', 'pending')->count(),
            'revenue_settlement_today' => (float) (clone $paymentQuery)->where('status', 'settlement')->whereDate('created_at', today())->sum('amount'),
        ];
    }

    private function adminMetrics(User $user): array
    {
        return [
            ...$this->managerMetrics($user),
            'users_total' => User::query()->count(),
            'branches_total' => Branch::query()->count(),
            'rate_cards_total' => RateCard::query()->count(),
            'errors_unresolved' => ErrorLog::query()->whereNull('resolved_at')->count(),
            'manual_correction_logs' => \App\Models\AuditLog::query()->where('is_manual_correction', true)->count(),
        ];
    }

    private function kasirMetrics(User $user): array
    {
        $shipmentQuery = Shipment::query()->where('branch_id', $user->branch_id);
        $paymentQuery = Payment::query()->whereHas('shipment', fn ($query) => $query->where('branch_id', $user->branch_id));

        return [
            'branch_name' => $user->branch?->name,
            'shipments_today' => (clone $shipmentQuery)->whereDate('created_at', today())->count(),
            'shipments_pending' => (clone $shipmentQuery)
                ->whereHas('status', fn ($query) => $query->whereIn('code', ['pending', 'in_transit', 'out_for_delivery']))
                ->count(),
            'payments_pending' => (clone $paymentQuery)->where('status', 'pending')->count(),
            'payments_settlement_today' => (clone $paymentQuery)->where('status', 'settlement')->whereDate('created_at', today())->count(),
            'revenue_settlement_today' => (float) (clone $paymentQuery)->where('status', 'settlement')->whereDate('created_at', today())->sum('amount'),
        ];
    }
}