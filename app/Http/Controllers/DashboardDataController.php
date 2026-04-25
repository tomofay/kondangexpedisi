<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\ShipmentTracking;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Zone;
use App\Models\ShipmentStatus;
use App\Models\RateCard;
use App\Models\RateCardApproval;
use App\Models\IntegrationStatus;
use App\Models\LandingPageContent;
use App\Models\ErrorLog;
use App\Models\AdminTask;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardDataController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user, 401);

        return response()->json(match ($user->role) {
            'admin' => $this->adminPayload($user),
            'manager' => $this->adminPayload($user),
            'kasir', 'courier' => $this->staffPayload($user),
            'customer' => $this->customerPayload($user),
            default => ['message' => 'Role tidak dikenali.'],
        });
    }

    private function adminPayload(User $user): array
    {
        $shipmentQuery = Shipment::query();
        $paymentQuery = Payment::query();

        $todayStart = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $monthStart = now()->startOfMonth();

        $yesterdayStart = now()->subDay()->startOfDay();
        $yesterdayEnd = now()->subDay()->endOfDay();
        $prevWeekStart = now()->subWeek()->startOfWeek();
        $prevWeekEnd = now()->subWeek()->endOfWeek();
        $prevMonthStart = now()->subMonth()->startOfMonth();
        $prevMonthEnd = now()->subMonth()->endOfMonth();

        $shipmentsToday = (clone $shipmentQuery)->where('created_at', '>=', $todayStart)->count();
        $shipmentsWeek = (clone $shipmentQuery)->where('created_at', '>=', $weekStart)->count();
        $shipmentsMonth = (clone $shipmentQuery)->where('created_at', '>=', $monthStart)->count();

        $shipmentsYesterday = (clone $shipmentQuery)->whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])->count();
        $shipmentsPrevWeek = (clone $shipmentQuery)->whereBetween('created_at', [$prevWeekStart, $prevWeekEnd])->count();
        $shipmentsPrevMonth = (clone $shipmentQuery)->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->count();

        $revenueSettled = (float) (clone $paymentQuery)->where('status', 'settlement')->sum('amount');
        $revenuePending = (float) (clone $paymentQuery)->whereIn('status', ['pending', 'deny', 'expire'])->sum('amount');

        $deliveredBase = (clone $shipmentQuery)
            ->whereHas('status', fn ($query) => $query->where('code', 'delivered'))
            ->whereNotNull('delivered_at')
            ->whereNotNull('estimated_delivery_at');
        $deliveredCount = (clone $deliveredBase)->count();
        $onTimeDeliveredCount = (clone $deliveredBase)->whereColumn('delivered_at', '<=', 'estimated_delivery_at')->count();
        $onTimeRate = $deliveredCount > 0 ? round(($onTimeDeliveredCount / $deliveredCount) * 100, 2) : 0;

        $cancelReturnCount = (clone $shipmentQuery)
            ->whereHas('status', fn ($query) => $query->whereIn('code', ['cancelled', 'returned']))
            ->count();
        $totalShipments = (clone $shipmentQuery)->count();
        $cancelReturnRate = $totalShipments > 0 ? round(($cancelReturnCount / $totalShipments) * 100, 2) : 0;

        $finalStatusCodes = ['delivered', 'cancelled', 'returned'];

        $alerts = [
            'payments_pending_24h' => [
                'total' => Payment::query()
                    ->where('status', 'pending')
                    ->where('created_at', '<=', now()->subDay())
                    ->count(),
                'items' => Payment::query()
                    ->with(['shipment.branch'])
                    ->where('status', 'pending')
                    ->where('created_at', '<=', now()->subDay())
                    ->latest('created_at')
                    ->limit(10)
                    ->get(),
            ],
            'shipments_overdue' => [
                'total' => Shipment::query()
                    ->whereNotNull('estimated_delivery_at')
                    ->where('estimated_delivery_at', '<', now())
                    ->whereDoesntHave('status', fn ($query) => $query->whereIn('code', $finalStatusCodes))
                    ->count(),
                'items' => Shipment::query()
                    ->with(['branch', 'status', 'courier'])
                    ->whereNotNull('estimated_delivery_at')
                    ->where('estimated_delivery_at', '<', now())
                    ->whereDoesntHave('status', fn ($query) => $query->whereIn('code', $finalStatusCodes))
                    ->orderBy('estimated_delivery_at')
                    ->limit(10)
                    ->get(),
            ],
            'cancel_return_spike_branches' => $this->cancelReturnSpikeBranches(),
            'failed_login_repeated_users' => $this->failedLoginRiskUsers(),
            'midtrans_callback_failures' => [
                'total_last_24h' => AuditLog::query()
                    ->where('action', 'payment.midtrans_callback_failed')
                    ->where('created_at', '>=', now()->subDay())
                    ->count(),
                'items' => AuditLog::query()
                    ->where('action', 'payment.midtrans_callback_failed')
                    ->latest('created_at')
                    ->limit(10)
                    ->get(),
            ],
        ];

        $branchPerformance = $this->branchPerformancePayload();
        $courierFleet = $this->courierFleetPayload($finalStatusCodes);
        $financialControl = $this->financialControlPayload();

        $statusBreakdown = Shipment::query()
            ->selectRaw('shipment_statuses.code as code, shipment_statuses.name as name, COUNT(shipments.id) as total')
            ->join('shipment_statuses', 'shipment_statuses.id', '=', 'shipments.status_id')
            ->groupBy('shipment_statuses.code', 'shipment_statuses.name')
            ->orderByRaw('COUNT(shipments.id) DESC')
            ->get();

        return [
            'role' => $user->role,
            'shipments_total' => $totalShipments,
            'shipments_pending' => Shipment::query()->whereHas('status', fn ($query) => $query->where('code', 'pending'))->count(),
            'shipments_in_transit' => Shipment::query()->whereHas('status', fn ($query) => $query->where('code', 'in_transit'))->count(),
            'shipments_delivered' => Shipment::query()->whereHas('status', fn ($query) => $query->where('code', 'delivered'))->count(),
            'shipments_today' => $shipmentsToday,
            'payments_total' => Payment::query()->count(),
            'revenue_total' => $revenueSettled,
            'outstanding_payments' => Payment::query()->whereIn('status', ['pending', 'deny', 'expire'])->count(),
            'payments_today' => Payment::query()->whereDate('created_at', today())->count(),
            'branches_total' => Branch::query()->count(),
            'users_total' => User::query()->count(),
            'status_breakdown' => $statusBreakdown,
            'trackings_recent' => ShipmentTracking::query()
                ->with(['shipment.status', 'status'])
                ->latest('event_at')
                ->limit(10)
                ->get(),
            'executive_kpi' => [
                'shipments' => [
                    'today' => $shipmentsToday,
                    'week' => $shipmentsWeek,
                    'month' => $shipmentsMonth,
                ],
                'revenue' => [
                    'settled' => $revenueSettled,
                    'pending' => $revenuePending,
                ],
                'on_time_delivery_rate' => $onTimeRate,
                'cancel_return_rate' => $cancelReturnRate,
                'growth' => [
                    'today_vs_yesterday_percent' => $this->growthPercent($shipmentsToday, $shipmentsYesterday),
                    'week_vs_previous_week_percent' => $this->growthPercent($shipmentsWeek, $shipmentsPrevWeek),
                    'month_vs_previous_month_percent' => $this->growthPercent($shipmentsMonth, $shipmentsPrevMonth),
                ],
            ],
            'alert_center' => $alerts,
            'branch_performance' => $branchPerformance,
            'courier_fleet_health' => $courierFleet,
            'financial_control' => $financialControl,
            'master_data_governance' => $this->masterDataGovernancePayload(),
            'user_access_management' => $this->userAccessManagementPayload(),
            'service_reliability' => $this->serviceReliabilityPayload(),
            'action_queue' => $this->actionQueuePayload(),
            'reporting_export' => $this->reportingExportPayload(),
            'trash_center' => $this->trashCenterPayload(),
            'system_snapshot' => $this->systemSnapshotPayload(),
            'permissions' => [
                'can_manage_shipments' => true,
                'can_manage_payments' => true,
                'can_view_reports' => true,
                'can_manage_master_data' => true,
            ],
        ];
    }

    private function staffPayload(User $user): array
    {
        $shipmentQuery = Shipment::query();
        $paymentQuery = Payment::query();
        $trackingQuery = ShipmentTracking::query()->with(['shipment.status', 'status']);

        if (in_array($user->role, ['kasir', 'courier'], true) && $user->branch_id) {
            $branch = Branch::query()->find($user->branch_id);
            $isStaffWithInactiveBranch = in_array($user->role, ['kasir'], true) && (! $branch || ! $branch->is_active);

            if ($isStaffWithInactiveBranch) {
                $shipmentQuery->whereRaw('1 = 0');
                $paymentQuery->whereRaw('1 = 0');
                $trackingQuery->whereRaw('1 = 0');
            } else {
                $shipmentQuery->where('branch_id', $user->branch_id);
                $paymentQuery->whereHas('shipment', fn ($query) => $query->where('branch_id', $user->branch_id));
                $trackingQuery->whereHas('shipment', fn ($query) => $query->where('branch_id', $user->branch_id));
            }
        }

        if ($user->role === 'courier') {
            $shipmentQuery->where('courier_id', $user->id);
            $trackingQuery->where('created_by', $user->id);
        }

        $statusBreakdown = (clone $shipmentQuery)
            ->selectRaw('shipment_statuses.code as code, shipment_statuses.name as name, COUNT(shipments.id) as total')
            ->join('shipment_statuses', 'shipment_statuses.id', '=', 'shipments.status_id')
            ->groupBy('shipment_statuses.code', 'shipment_statuses.name')
            ->orderByRaw('COUNT(shipments.id) DESC')
            ->get();

        $recentShipments = (clone $shipmentQuery)
            ->with(['status', 'branch', 'customer'])
            ->latest('id')
            ->limit(8)
            ->get();

        $recentPayments = (clone $paymentQuery)
            ->with(['shipment.status', 'customer'])
            ->latest('id')
            ->limit(8)
            ->get();

        return [
            'role' => $user->role,
            'branch_id' => $user->branch_id,
            'branch' => $user->branch,
            'shipments_total' => $shipmentQuery->count(),
            'shipments_pending' => (clone $shipmentQuery)->whereHas('status', fn ($query) => $query->where('code', 'pending'))->count(),
            'shipments_in_transit' => (clone $shipmentQuery)->whereHas('status', fn ($query) => $query->where('code', 'in_transit'))->count(),
            'shipments_delivered' => (clone $shipmentQuery)->whereHas('status', fn ($query) => $query->where('code', 'delivered'))->count(),
            'shipments_today' => (clone $shipmentQuery)->whereDate('created_at', today())->count(),
            'payments_total' => $paymentQuery->count(),
            'revenue_total' => (float) (clone $paymentQuery)->where('status', 'settlement')->sum('amount'),
            'outstanding_payments' => (clone $paymentQuery)->whereIn('status', ['pending', 'deny', 'expire'])->count(),
            'payments_today' => (clone $paymentQuery)->whereDate('created_at', today())->count(),
            'branches_total' => Branch::query()->count(),
            'users_total' => User::query()->count(),
            'status_breakdown' => $statusBreakdown,
            'recent_shipments' => $recentShipments,
            'recent_payments' => $recentPayments,
            'trackings_recent' => $trackingQuery
                ->latest('event_at')
                ->limit(10)
                ->get(),
            'permissions' => [
                'can_manage_shipments' => in_array($user->role, ['admin', 'kasir', 'courier'], true),
                'can_manage_payments' => in_array($user->role, ['admin', 'kasir'], true),
                'can_view_reports' => in_array($user->role, ['admin', 'kasir'], true),
                'can_manage_master_data' => in_array($user->role, ['admin', 'kasir'], true),
            ],
        ];
    }

    private function customerPayload($user): array
    {
        $customer = $user->customer;

        abort_unless($customer instanceof Customer, 404, 'Profil customer belum terhubung.');

        return [
            'role' => $user->role,
            'branch_id' => null,
            'customer' => $customer,
            'shipments_total' => $customer->shipments()->count(),
            'payments_total' => $customer->payments()->count(),
            'pending_shipments' => $customer->shipments()->where('payment_status', 'pending')->count(),
            'recent_shipments' => $customer->shipments()->with(['status', 'branch'])->latest()->limit(5)->get(),
            'recent_payments' => $customer->payments()->with('shipment.status')->latest()->limit(5)->get(),
        ];
    }

    private function growthPercent(int $current, int $previous): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function cancelReturnSpikeBranches()
    {
        $windowStart = now()->subDays(7);

        return Branch::query()
            ->select('branches.id', 'branches.code', 'branches.name')
            ->selectSub(function ($query) use ($windowStart) {
                $query->from('shipments')
                    ->join('shipment_statuses', 'shipment_statuses.id', '=', 'shipments.status_id')
                    ->whereColumn('shipments.branch_id', 'branches.id')
                    ->where('shipments.created_at', '>=', $windowStart)
                    ->whereIn('shipment_statuses.code', ['cancelled', 'returned'])
                    ->selectRaw('COUNT(*)');
            }, 'cancel_return_total')
            ->selectSub(function ($query) use ($windowStart) {
                $query->from('shipments')
                    ->whereColumn('shipments.branch_id', 'branches.id')
                    ->where('shipments.created_at', '>=', $windowStart)
                    ->selectRaw('COUNT(*)');
            }, 'shipment_total')
            ->havingRaw('cancel_return_total >= 2')
            ->orderByDesc('cancel_return_total')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $total = (int) ($item->shipment_total ?? 0);
                $cancelReturn = (int) ($item->cancel_return_total ?? 0);
                $item->rate = $total > 0 ? round(($cancelReturn / $total) * 100, 2) : 0;

                return $item;
            })
            ->values();
    }

    private function failedLoginRiskUsers()
    {
        return AuditLog::query()
            ->select('subject_id', DB::raw('COUNT(*) as attempts'))
            ->where('action', 'auth.login_failed')
            ->where('created_at', '>=', now()->subDay())
            ->groupBy('subject_id')
            ->havingRaw('COUNT(*) >= 3')
            ->orderByDesc('attempts')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                return [
                    'attempts' => (int) $row->attempts,
                    'user' => User::query()->find($row->subject_id, ['id', 'name', 'email', 'role']),
                ];
            })
            ->filter(fn ($row) => $row['user'] !== null)
            ->values();
    }

    private function branchPerformancePayload(): array
    {
        $windowStart = now()->subDays(30);

        $ranking = Branch::query()
            ->leftJoin('shipments', function ($join) use ($windowStart) {
                $join->on('shipments.branch_id', '=', 'branches.id')
                    ->where('shipments.created_at', '>=', $windowStart);
            })
            ->leftJoin('payments', function ($join) {
                $join->on('payments.shipment_id', '=', 'shipments.id')
                    ->where('payments.status', '=', 'settlement');
            })
            ->leftJoin('shipment_statuses', 'shipment_statuses.id', '=', 'shipments.status_id')
            ->groupBy('branches.id', 'branches.name', 'branches.code')
            ->selectRaw('branches.id, branches.name, branches.code')
            ->selectRaw('COUNT(DISTINCT shipments.id) as shipment_volume')
            ->selectRaw('COALESCE(SUM(payments.amount), 0) as revenue_settled')
            ->selectRaw('SUM(CASE WHEN shipment_statuses.code = "delivered" AND shipments.delivered_at IS NOT NULL AND shipments.estimated_delivery_at IS NOT NULL AND shipments.delivered_at <= shipments.estimated_delivery_at THEN 1 ELSE 0 END) as delivered_on_time')
            ->selectRaw('SUM(CASE WHEN shipment_statuses.code = "delivered" THEN 1 ELSE 0 END) as delivered_total')
            ->orderByDesc('shipment_volume')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $deliveredTotal = (int) ($item->delivered_total ?? 0);
                $item->sla_rate = $deliveredTotal > 0
                    ? round(((int) $item->delivered_on_time / $deliveredTotal) * 100, 2)
                    : 0;

                return $item;
            });

        $backlogHeatmap = Branch::query()
            ->leftJoin('shipments', 'shipments.branch_id', '=', 'branches.id')
            ->leftJoin('shipment_statuses', 'shipment_statuses.id', '=', 'shipments.status_id')
            ->groupBy('branches.id', 'branches.name')
            ->selectRaw('branches.id, branches.name')
            ->selectRaw('SUM(CASE WHEN shipment_statuses.code IN ("pending", "in_transit", "out_for_delivery") THEN 1 ELSE 0 END) as backlog_total')
            ->orderByDesc('backlog_total')
            ->get();

        $worstBottlenecks = $backlogHeatmap->take(5)->values();

        $problemShipments = Shipment::query()
            ->with(['branch', 'status', 'courier'])
            ->where(function ($query) {
                $query
                    ->where(function ($overdue) {
                        $overdue->whereNotNull('estimated_delivery_at')
                            ->where('estimated_delivery_at', '<', now())
                            ->whereHas('status', fn ($q) => $q->whereNotIn('code', ['delivered', 'cancelled', 'returned']));
                    })
                    ->orWhereHas('status', fn ($q) => $q->whereIn('code', ['cancelled', 'returned']));
            })
            ->latest('updated_at')
            ->limit(12)
            ->get();

        return [
            'ranking' => $ranking,
            'backlog_heatmap' => $backlogHeatmap,
            'worst_bottlenecks' => $worstBottlenecks,
            'problem_shipments' => $problemShipments,
        ];
    }

    private function courierFleetPayload(array $finalStatusCodes): array
    {
        $couriers = User::query()
            ->where('role', 'courier')
            ->with('branch')
            ->get()
            ->map(function (User $courier) {
                $activeShipments = Shipment::query()
                    ->where('courier_id', $courier->id)
                    ->whereHas('status', fn ($query) => $query->whereIn('code', ['pending', 'in_transit', 'out_for_delivery']))
                    ->count();

                return [
                    'id' => $courier->id,
                    'name' => $courier->name,
                    'email' => $courier->email,
                    'branch' => $courier->branch?->name,
                    'active_shipments' => $activeShipments,
                    'utilization_state' => $activeShipments >= 6 ? 'overload' : ($activeShipments <= 1 ? 'underload' : 'normal'),
                ];
            })
            ->sortByDesc('active_shipments')
            ->values();

        $fleetStatus = [
            'available' => Vehicle::query()->where('status', 'available')->count(),
            'in_use' => Vehicle::query()->where('status', 'in_use')->count(),
            'maintenance' => Vehicle::query()->where('status', 'maintenance')->count(),
            'inactive' => Vehicle::query()->where('status', 'inactive')->count(),
        ];

        $highDowntime = Vehicle::query()
            ->with('branch')
            ->whereIn('status', ['maintenance', 'inactive'])
            ->latest('updated_at')
            ->limit(10)
            ->get();

        $assignmentMismatch = Shipment::query()
            ->with(['branch', 'status'])
            ->whereNull('courier_id')
            ->whereDoesntHave('status', fn ($query) => $query->whereIn('code', $finalStatusCodes))
            ->latest('created_at')
            ->limit(12)
            ->get();

        return [
            'courier_utilization' => $couriers,
            'fleet_status' => $fleetStatus,
            'high_downtime_vehicles' => $highDowntime,
            'assignment_mismatch' => $assignmentMismatch,
        ];
    }

    private function financialControlPayload(): array
    {
        $dailySettlementTrend = collect(range(13, 0))
            ->map(function (int $offset) {
                $date = Carbon::today()->subDays($offset);
                $amount = (float) Payment::query()
                    ->where('status', 'settlement')
                    ->whereDate('created_at', $date)
                    ->sum('amount');

                return [
                    'period' => $date->format('Y-m-d'),
                    'amount' => $amount,
                ];
            })
            ->values();

        $weeklySettlementTrend = collect(range(7, 0))
            ->map(function (int $offset) {
                $start = Carbon::now()->subWeeks($offset)->startOfWeek();
                $end = Carbon::now()->subWeeks($offset)->endOfWeek();
                $amount = (float) Payment::query()
                    ->where('status', 'settlement')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('amount');

                return [
                    'period' => $start->format('Y-m-d').' s/d '.$end->format('Y-m-d'),
                    'amount' => $amount,
                ];
            })
            ->values();

        $outstandingPayments = Payment::query()->whereIn('status', ['pending', 'deny', 'expire']);
        $outstandingAging = [
            '0_1_days' => (clone $outstandingPayments)->where('created_at', '>=', now()->subDay())->count(),
            '2_3_days' => (clone $outstandingPayments)->whereBetween('created_at', [now()->subDays(3), now()->subDays(1)])->count(),
            '4_7_days' => (clone $outstandingPayments)->whereBetween('created_at', [now()->subDays(7), now()->subDays(3)])->count(),
            'gt_7_days' => (clone $outstandingPayments)->where('created_at', '<', now()->subDays(7))->count(),
        ];

        $codAging = [
            '0_1_days' => Shipment::query()->where('is_cod', true)->where('payment_status', '!=', 'paid')->where('created_at', '>=', now()->subDay())->count(),
            '2_3_days' => Shipment::query()->where('is_cod', true)->where('payment_status', '!=', 'paid')->whereBetween('created_at', [now()->subDays(3), now()->subDays(1)])->count(),
            '4_7_days' => Shipment::query()->where('is_cod', true)->where('payment_status', '!=', 'paid')->whereBetween('created_at', [now()->subDays(7), now()->subDays(3)])->count(),
            'gt_7_days' => Shipment::query()->where('is_cod', true)->where('payment_status', '!=', 'paid')->where('created_at', '<', now()->subDays(7))->count(),
        ];

        $refundCancelNominalByBranch = Branch::query()
            ->leftJoin('shipments', 'shipments.branch_id', '=', 'branches.id')
            ->leftJoin('payments', function ($join) {
                $join->on('payments.shipment_id', '=', 'shipments.id')
                    ->whereIn('payments.status', ['refund', 'cancel']);
            })
            ->groupBy('branches.id', 'branches.name')
            ->selectRaw('branches.id, branches.name, COALESCE(SUM(payments.amount), 0) as nominal')
            ->orderByDesc('nominal')
            ->get();

        return [
            'settlement_trend_daily' => $dailySettlementTrend,
            'settlement_trend_weekly' => $weeklySettlementTrend,
            'outstanding_payment_aging' => $outstandingAging,
            'cod_aging' => $codAging,
            'refund_cancel_nominal_by_branch' => $refundCancelNominalByBranch,
        ];
    }

    private function masterDataGovernancePayload(): array
    {
        $pendingApprovals = RateCardApproval::query()
            ->with(['rateCard.zone', 'requester', 'approver'])
            ->where('status', 'pending')
            ->latest('created_at')
            ->get();

        $approvalHistory = RateCardApproval::query()
            ->with(['rateCard.zone', 'requester', 'approver'])
            ->where('status', '!=', 'pending')
            ->latest('approved_at')
            ->limit(20)
            ->get();

        $branchCount = Branch::query()->count();
        $zoneCount = Zone::query()->count();
        $rateCardCount = RateCard::query()->count();
        $vehicleCount = Vehicle::query()->count();
        $statusCount = ShipmentStatus::query()->count();

        return [
            'pending_rate_card_approvals' => $pendingApprovals,
            'approval_history' => $approvalHistory,
            'master_data_stats' => [
                'branches' => $branchCount,
                'zones' => $zoneCount,
                'rate_cards' => $rateCardCount,
                'vehicles' => $vehicleCount,
                'shipment_statuses' => $statusCount,
            ],
            'recent_changes' => AuditLog::query()
                ->whereIn('action', ['branch.created', 'branch.updated', 'zone.created', 'rate_card.created', 'vehicle.created'])
                ->with(['user'])
                ->latest('created_at')
                ->limit(15)
                ->get(),
        ];
    }

    private function userAccessManagementPayload(): array
    {
        $users = User::query()
            ->with(['branch'])
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'branch' => $user->branch?->name,
                'is_active' => $user->is_active,
                'last_login_at' => $user->last_login_at?->format('Y-m-d H:i:s'),
                'last_activity_at' => $user->last_activity_at?->format('Y-m-d H:i:s'),
                'last_login_ip' => $user->last_login_ip,
                'permissions' => $user->getPermissions(),
            ]);

        $roleDistribution = User::query()
            ->groupBy('role')
            ->selectRaw('role, COUNT(*) as count')
            ->get();

        $recentLogins = User::query()
            ->whereNotNull('last_login_at')
            ->orderByDesc('last_login_at')
            ->limit(10)
            ->get(['id', 'name', 'email', 'role', 'last_login_at', 'last_login_ip']);

        $permissionMatrix = [
            'admin' => [
                'view_dashboard' => true,
                'manage_users' => true,
                'approve_rate_cards' => true,
                'manage_branches' => true,
                'view_reports' => true,
                'export_data' => true,
                'manage_roles' => true,
                'view_audit_logs' => true,
                'manage_vehicles' => true,
                'manage_zones' => true,
                'manage_shipments' => true,
                'process_payments' => true,
                'broadcast_messages' => true,
            ],
            'manager' => [
                'view_dashboard' => true,
                'view_team_performance' => true,
                'assign_shipments' => true,
                'manage_couriers' => true,
                'view_reports' => true,
                'export_data' => true,
            ],
            'kasir' => [
                'view_dashboard' => true,
                'process_payments' => true,
                'view_shipments' => true,
                'print_labels' => true,
            ],
            'courier' => [
                'view_assigned_shipments' => true,
                'update_shipment_status' => true,
                'view_earnings' => true,
            ],
            'customer' => [
                'view_own_shipments' => true,
                'track_shipments' => true,
                'manage_profile' => true,
            ],
        ];

        return [
            'total_users' => User::query()->count(),
            'active_users' => User::query()->where('is_active', true)->count(),
            'inactive_users' => User::query()->where('is_active', false)->count(),
            'users' => $users,
            'role_distribution' => $roleDistribution,
            'recent_logins' => $recentLogins,
            'permission_matrix' => $permissionMatrix,
        ];
    }

    private function serviceReliabilityPayload(): array
    {
        $integrationStatuses = IntegrationStatus::query()->get();

        $criticalErrorsLast24h = ErrorLog::query()
            ->where('severity', 'critical')
            ->where('created_at', '>=', now()->subDay())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $recentErrors = ErrorLog::query()
            ->where('created_at', '>=', now()->subDay())
            ->selectRaw('module, COUNT(*) as error_count, MAX(created_at) as last_error')
            ->groupBy('module')
            ->orderByDesc('error_count')
            ->get();

        $errorTrendByHour = ErrorLog::query()
            ->where('created_at', '>=', now()->subDay())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour, COUNT(*) as count")
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00')")
            ->orderBy('hour')
            ->get();

        // Placeholder for job queue health
        $jobQueueHealth = [
            'failed_jobs' => 3,
            'pending_jobs' => 12,
            'processing_jobs' => 2,
            'retry_pending' => 1,
        ];

        return [
            'integration_statuses' => $integrationStatuses,
            'integration_health_score' => round($integrationStatuses->avg(fn ($s) => $s->getHealthPercentage()), 2),
            'critical_errors_last_24h' => $criticalErrorsLast24h,
            'critical_error_count' => $criticalErrorsLast24h->count(),
            'recent_errors_by_module' => $recentErrors,
            'error_trend_by_hour' => $errorTrendByHour,
            'job_queue_health' => $jobQueueHealth,
            'backup_status' => [
                'last_backup' => now()->subHours(2)->format('Y-m-d H:i:s'),
                'status' => 'healthy',
                'size_gb' => 2.4,
            ],
        ];
    }

    private function actionQueuePayload(): array
    {
        $pendingTasks = AdminTask::query()
            ->with(['assignee', 'creator'])
            ->where('status', 'pending')
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->orderBy('created_at')
            ->get();

        $inProgressTasks = AdminTask::query()
            ->with(['assignee', 'creator'])
            ->where('status', 'in_progress')
            ->get();

        $tasksByType = AdminTask::query()
            ->selectRaw('task_type, COUNT(*) as count')
            ->where('status', 'pending')
            ->groupBy('task_type')
            ->get();

        $pendingApprovals = RateCardApproval::query()
            ->with(['rateCard', 'requester'])
            ->where('status', 'pending')
            ->count();

        $pendingPayments = Payment::query()
            ->where('status', 'pending')
            ->where('created_at', '<=', now()->subDay())
            ->count();

        $stuckShipments = Shipment::query()
            ->where('estimated_delivery_at', '<', now())
            ->whereDoesntHave('status', fn ($q) => $q->whereIn('code', ['delivered', 'cancelled', 'returned']))
            ->count();

        return [
            'pending_tasks' => $pendingTasks,
            'in_progress_tasks' => $inProgressTasks,
            'tasks_by_type' => $tasksByType,
            'quick_actions_summary' => [
                'pending_rate_card_approvals' => $pendingApprovals,
                'pending_payments_overdue' => $pendingPayments,
                'stuck_shipments' => $stuckShipments,
                'at_risk_users' => AuditLog::query()
                    ->select('actor_id')
                    ->whereNotNull('actor_id')
                    ->where('action', 'auth.login_failed')
                    ->where('created_at', '>=', now()->subDay())
                    ->groupBy('actor_id')
                    ->havingRaw('COUNT(*) >= 3')
                    ->count(),
            ],
        ];
    }

    private function reportingExportPayload(): array
    {
        $availableReports = Report::query()
            ->where('status', 'completed')
            ->latest('generated_at')
            ->limit(10)
            ->get();

        $scheduledReports = Report::query()
            ->where('frequency', '!=', 'manual')
            ->get();

        $reportTemplates = [
            [
                'id' => 1,
                'name' => 'Daily KPI Snapshot',
                'type' => 'kpi_snapshot',
                'format' => 'pdf',
                'frequency' => 'daily',
                'time' => '07:00',
            ],
            [
                'id' => 2,
                'name' => 'Weekly Performance Report',
                'type' => 'performance_report',
                'format' => 'excel',
                'frequency' => 'weekly',
                'day' => 'Monday',
            ],
            [
                'id' => 3,
                'name' => 'Branch Performance Analysis',
                'type' => 'branch_performance',
                'format' => 'pdf',
                'frequency' => 'weekly',
            ],
            [
                'id' => 4,
                'name' => 'Financial Settlement Report',
                'type' => 'financial_settlement',
                'format' => 'csv',
                'frequency' => 'daily',
            ],
        ];

        $exportHistory = Report::query()
            ->orderByDesc('generated_at')
            ->limit(10)
            ->get();

        return [
            'available_reports' => $availableReports,
            'scheduled_reports' => $scheduledReports,
            'report_templates' => $reportTemplates,
            'export_history' => $exportHistory,
            'quick_export_options' => [
                'daily_csv' => [
                    'type' => 'daily',
                    'format' => 'csv',
                ],
                'weekly_pdf' => [
                    'type' => 'weekly',
                    'format' => 'pdf',
                ],
                'custom_excel' => [
                    'type' => 'custom',
                    'format' => 'excel',
                ],
            ],
        ];
    }

    private function trashCenterPayload(): array
    {
        $resources = [
            'users' => [User::query(), 'name'],
            'branches' => [Branch::query(), 'name'],
            'zones' => [Zone::query(), 'name'],
            'rate_cards' => [RateCard::query(), 'service_type'],
            'vehicles' => [Vehicle::query(), 'name'],
            'shipments' => [Shipment::query(), 'tracking_number'],
            'payments' => [Payment::query(), 'method'],
            'landing_contents' => [LandingPageContent::query(), 'title'],
        ];

        $summary = [];
        $recent = collect();

        foreach ($resources as $type => [$query, $labelColumn]) {
            $trashedQuery = (clone $query)->onlyTrashed();
            $summary[$type] = $trashedQuery->count();

            $recent = $recent->concat(
                (clone $trashedQuery)
                    ->latest('deleted_at')
                    ->limit(5)
                    ->get()
                    ->map(function ($item) use ($type, $labelColumn) {
                        return [
                            'type' => $type,
                            'id' => $item->id,
                            'label' => $item->{$labelColumn} ?? ($item->tracking_number ?? ('#'.$item->id)),
                            'deleted_at' => optional($item->deleted_at)->format('Y-m-d H:i:s'),
                        ];
                    })
            );
        }

        return [
            'summary' => $summary,
            'recent' => $recent->sortByDesc('deleted_at')->take(20)->values(),
            'total' => array_sum($summary),
        ];
    }

    private function systemSnapshotPayload(): array
    {
        return [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_debug' => (bool) config('app.debug'),
            'timezone' => config('app.timezone'),
            'queue_driver' => config('queue.default'),
            'mail_mailer' => config('mail.default'),
            'broadcast_driver' => config('broadcasting.default'),
            'php_version' => PHP_VERSION,
            'maintenance_mode' => app()->isDownForMaintenance(),
            'integration_summary' => [
                'midtrans' => config('services.midtrans.server_key') ? 'configured' : 'missing',
                'queue' => config('queue.default') ?: 'default',
                'storage' => config('filesystems.default') ?: 'local',
            ],
        ];
    }
}
