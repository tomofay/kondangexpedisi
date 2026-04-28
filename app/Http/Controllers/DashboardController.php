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
                'metrics' => $this->managerMetrics(),
            ]),
            'admin' => view('dashboard.admin', [
                'user' => $user,
                'level' => 2,
                'metrics' => $this->adminMetrics(),
            ]),
            'kasir' => view('dashboard.kasir', [
                'user' => $user,
                'level' => 3,
                'metrics' => $this->kasirMetrics($user),
            ]),
            default => abort(403, 'Role tidak diizinkan.'),
        };
    }

    private function managerMetrics(): array
    {
        $finalStatuses = ['delivered', 'cancelled', 'returned'];

        return [
            'shipments_today' => Shipment::query()->whereDate('created_at', today())->count(),
            'shipments_in_progress' => Shipment::query()
                ->whereHas('status', fn ($query) => $query->whereIn('code', ['pending', 'in_transit', 'out_for_delivery']))
                ->count(),
            'shipments_overdue' => Shipment::query()
                ->whereNotNull('estimated_delivery_at')
                ->where('estimated_delivery_at', '<', now())
                ->whereDoesntHave('status', fn ($query) => $query->whereIn('code', $finalStatuses))
                ->count(),
            'pending_approvals' => AdminTask::query()
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'payments_pending' => Payment::query()->where('status', 'pending')->count(),
            'revenue_settlement_today' => (float) Payment::query()->where('status', 'settlement')->whereDate('created_at', today())->sum('amount'),
        ];
    }

    private function adminMetrics(): array
    {
        return [
            ...$this->managerMetrics(),
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