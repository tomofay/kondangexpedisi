<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchBalance;
use App\Models\CourierEarning;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use App\Services\DailyReconciliationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $actor = $request->user();
        [$fromDate, $untilDate] = $this->resolveDateRange($request);

        $shipmentQuery = Shipment::query()->when($fromDate || $untilDate, function ($query) use ($fromDate, $untilDate) {
            $this->applyDateRange($query, 'created_at', $fromDate, $untilDate);
        });

        $paymentQuery = Payment::query()->when($fromDate || $untilDate, function ($query) use ($fromDate, $untilDate) {
            $this->applyDateRange($query, 'created_at', $fromDate, $untilDate);
        });

        if (in_array($actor?->role, ['manager', 'kasir'], true)) {
            $shipmentQuery->where('branch_id', $actor->branch_id);
            $paymentQuery->whereHas('shipment', fn ($q) => $q->where('branch_id', $actor->branch_id));
        }

        return response()->json([
            'period' => [
                'from' => $fromDate,
                'until' => $untilDate,
            ],
            'shipments_total' => $shipmentQuery->count(),
            'shipments_pending' => (clone $shipmentQuery)->whereHas('status', fn ($query) => $query->where('code', 'pending'))->count(),
            'shipments_delivered' => (clone $shipmentQuery)->whereHas('status', fn ($query) => $query->where('code', 'delivered'))->count(),
            'revenue_total' => (float) (clone $paymentQuery)->where('status', 'settlement')->sum('amount'),
            'payment_pending' => (float) (clone $paymentQuery)->where('status', 'pending')->sum('amount'),
            'branches_total' => Branch::query()->count(),
            'couriers_total' => User::query()->where('role', 'courier')->count(),
        ]);
    }

    public function branchPerformance(Request $request): JsonResponse
    {
        [$fromDate, $untilDate] = $this->resolveDateRange($request);

        $branches = Branch::query()
            ->withCount(['shipments'])
            ->get()
            ->map(fn (Branch $branch) => [
                'id' => $branch->id,
                'code' => $branch->code,
                'name' => $branch->name,
                'shipments_total' => $branch->shipments_count,
                'revenue_total' => (float) Payment::query()
                    ->whereHas('shipment', fn ($query) => $query->where('branch_id', $branch->id))
                    ->where('status', 'settlement')
                    ->when($fromDate || $untilDate, function ($query) use ($fromDate, $untilDate) {
                        $this->applyDateRange($query, 'created_at', $fromDate, $untilDate);
                    })
                    ->sum('amount'),
            ]);

        return response()->json($branches);
    }

    public function branchDetail(Request $request, Branch $branch): JsonResponse
    {
        [$fromDate, $untilDate] = $this->resolveDateRange($request);

        $shipmentQuery = Shipment::query()->where('branch_id', $branch->id)
            ->when($fromDate || $untilDate, function ($query) use ($fromDate, $untilDate) {
                $this->applyDateRange($query, 'created_at', $fromDate, $untilDate);
            });

        $paymentQuery = Payment::query()->whereHas('shipment', fn ($q) => $q->where('branch_id', $branch->id))
            ->when($fromDate || $untilDate, function ($query) use ($fromDate, $untilDate) {
                $this->applyDateRange($query, 'created_at', $fromDate, $untilDate);
            });

        $shipmentsTotal = $shipmentQuery->count();

        $shipmentsByStatus = (clone $shipmentQuery)
            ->selectRaw('shipment_statuses.code as code, shipment_statuses.name as name, COUNT(shipments.id) as total')
            ->join('shipment_statuses', 'shipment_statuses.id', '=', 'shipments.status_id')
            ->groupBy('shipment_statuses.code', 'shipment_statuses.name')
            ->get();

        $revenueTotal = (float) $paymentQuery->where('status', 'settlement')->sum('amount');
        $paymentsPending = (float) $paymentQuery->where('status', 'pending')->sum('amount');

        $recentShipments = (clone $shipmentQuery)
            ->with(['status', 'courier'])
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'tracking_number' => $s->tracking_number,
                'status' => $s->status?->name,
                'courier' => $s->courier?->name,
                'created_at' => $s->created_at,
            ]);

        $topCouriers = User::query()
            ->where('role', 'courier')
            ->whereHas('assignedShipments', fn ($q) => $q->where('branch_id', $branch->id)->whereHas('status', fn ($q2) => $q2->where('code', 'delivered')))
            ->withCount(['assignedShipments as delivered_count' => fn ($q) => $q->where('branch_id', $branch->id)->whereHas('status', fn ($q2) => $q2->where('code', 'delivered'))])
            ->orderByDesc('delivered_count')
            ->limit(5)
            ->get()
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'delivered' => $c->delivered_count]);

        $vehiclesCount = \App\Models\Vehicle::query()->where('branch_id', $branch->id)->count();
        $usersCount = \App\Models\User::query()->where('branch_id', $branch->id)->count();

        return response()->json([
            'id' => $branch->id,
            'code' => $branch->code,
            'name' => $branch->name,
            'period' => ['from' => $fromDate, 'until' => $untilDate],
            'shipments_total' => $shipmentsTotal,
            'shipments_by_status' => $shipmentsByStatus,
            'revenue_total' => $revenueTotal,
            'payments_pending' => $paymentsPending,
            'recent_shipments' => $recentShipments,
            'top_couriers' => $topCouriers,
            'vehicles_count' => $vehiclesCount,
            'users_count' => $usersCount,
        ]);
    }

    public function courierPerformance(Request $request): JsonResponse
    {
        [$fromDate, $untilDate] = $this->resolveDateRange($request);

        $couriers = User::query()
            ->where('role', 'courier')
            ->withCount(['assignedShipments'])
            ->get()
            ->map(fn (User $courier) => [
                'id' => $courier->id,
                'name' => $courier->name,
                'shipments_total' => $courier->assigned_shipments_count,
                'completed_total' => $courier->assignedShipments()
                    ->whereHas('status', fn ($query) => $query->where('code', 'delivered'))
                    ->when($fromDate || $untilDate, function ($query) use ($fromDate, $untilDate) {
                        $this->applyDateRange($query, 'created_at', $fromDate, $untilDate);
                    })
                    ->count(),
            ]);

        return response()->json($couriers);
    }

    public function paymentOverview(Request $request): JsonResponse
    {
        [$fromDate, $untilDate] = $this->resolveDateRange($request);

        $paymentQuery = Payment::query()->when($fromDate || $untilDate, function ($query) use ($fromDate, $untilDate) {
            $this->applyDateRange($query, 'created_at', $fromDate, $untilDate);
        });

        return response()->json([
            'period' => [
                'from' => $fromDate,
                'until' => $untilDate,
            ],
            'settlement' => (clone $paymentQuery)->where('status', 'settlement')->count(),
            'pending' => (clone $paymentQuery)->where('status', 'pending')->count(),
            'failed' => (clone $paymentQuery)->whereIn('status', ['deny', 'expire', 'cancel', 'failed'])->count(),
            'refund' => (clone $paymentQuery)->where('status', 'refund')->count(),
        ]);
    }

    public function dailyReconciliation(Request $request, DailyReconciliationService $dailyReconciliationService): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $report = $dailyReconciliationService->buildForDate(
            $validated['date'] ?? now(),
            (int) ($validated['limit'] ?? 25)
        );

        return response()->json([
            'status' => 'success',
            'data' => $report,
        ]);
    }

    public function branchBalances(Request $request): JsonResponse
    {
        [$fromDate, $untilDate] = $this->resolveDateRange($request);

        $balances = BranchBalance::query()
            ->with('branch')
            ->when($fromDate || $untilDate, function ($query) use ($fromDate, $untilDate) {
                $this->applyDateRange($query, 'balance_date', $fromDate, $untilDate);
            })
            ->orderByDesc('balance_date')
            ->paginate(20);

        return response()->json($balances);
    }

    public function courierEarnings(Request $request): JsonResponse
    {
        [$fromDate, $untilDate] = $this->resolveDateRange($request);

        $earnings = CourierEarning::query()
            ->with(['courier', 'shipment'])
            ->when($fromDate || $untilDate, function ($query) use ($fromDate, $untilDate) {
                $this->applyDateRange($query, 'earning_date', $fromDate, $untilDate);
            })
            ->orderByDesc('earning_date')
            ->paginate(20);

        return response()->json($earnings);
    }

    public function branchBalancesExport(Request $request)
    {
        [$fromDate, $untilDate] = $this->resolveDateRange($request);

        $balances = BranchBalance::query()
            ->with('branch')
            ->when($fromDate || $untilDate, function ($query) use ($fromDate, $untilDate) {
                $this->applyDateRange($query, 'balance_date', $fromDate, $untilDate);
            })
            ->orderByDesc('balance_date')
            ->get();

        return response()->streamDownload(function () use ($balances) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['branch', 'balance_date', 'opening_balance', 'cash_in', 'cash_out', 'closing_balance']);

            foreach ($balances as $balance) {
                fputcsv($output, [
                    $balance->branch?->name,
                    $balance->balance_date?->format('Y-m-d'),
                    $balance->opening_balance,
                    $balance->cash_in,
                    $balance->cash_out,
                    $balance->closing_balance,
                ]);
            }

            fclose($output);
        }, 'branch-balances.csv', ['Content-Type' => 'text/csv']);
    }

    public function courierEarningsExport(Request $request)
    {
        [$fromDate, $untilDate] = $this->resolveDateRange($request);

        $earnings = CourierEarning::query()
            ->with(['courier', 'shipment'])
            ->when($fromDate || $untilDate, function ($query) use ($fromDate, $untilDate) {
                $this->applyDateRange($query, 'earning_date', $fromDate, $untilDate);
            })
            ->orderByDesc('earning_date')
            ->get();

        return response()->streamDownload(function () use ($earnings) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['courier', 'earning_date', 'base_fee', 'bonus', 'total_amount', 'shipment']);

            foreach ($earnings as $earning) {
                fputcsv($output, [
                    $earning->courier?->name,
                    $earning->earning_date?->format('Y-m-d'),
                    $earning->base_fee,
                    $earning->bonus,
                    $earning->total_amount,
                    $earning->shipment?->tracking_number,
                ]);
            }

            fclose($output);
        }, 'courier-earnings.csv', ['Content-Type' => 'text/csv']);
    }

    public function summaryExport(Request $request)
    {
        [$fromDate, $untilDate] = $this->resolveDateRange($request);
        $summary = $this->buildSummaryData($fromDate, $untilDate);

        return response()->streamDownload(function () use ($summary) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['metric', 'value']);

            foreach ($summary as $key => $value) {
                if (is_array($value)) {
                    fputcsv($output, [$key, json_encode($value)]);
                    continue;
                }

                fputcsv($output, [$key, $value]);
            }

            fclose($output);
        }, 'report-summary.csv', ['Content-Type' => 'text/csv']);
    }

    public function branchPerformanceExport(Request $request)
    {
        [$fromDate, $untilDate] = $this->resolveDateRange($request);
        $branches = $this->buildBranchPerformanceData($fromDate, $untilDate);

        return response()->streamDownload(function () use ($branches) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['id', 'code', 'name', 'shipments_total', 'revenue_total']);

            foreach ($branches as $branch) {
                fputcsv($output, [
                    $branch['id'],
                    $branch['code'],
                    $branch['name'],
                    $branch['shipments_total'],
                    $branch['revenue_total'],
                ]);
            }

            fclose($output);
        }, 'branch-performance.csv', ['Content-Type' => 'text/csv']);
    }

    private function buildSummaryData(?string $fromDate, ?string $untilDate): array
    {
        $shipmentQuery = Shipment::query()->when($fromDate || $untilDate, function ($query) use ($fromDate, $untilDate) {
            $this->applyDateRange($query, 'created_at', $fromDate, $untilDate);
        });

        $paymentQuery = Payment::query()->when($fromDate || $untilDate, function ($query) use ($fromDate, $untilDate) {
            $this->applyDateRange($query, 'created_at', $fromDate, $untilDate);
        });

        return [
            'period' => [
                'from' => $fromDate,
                'until' => $untilDate,
            ],
            'shipments_total' => $shipmentQuery->count(),
            'shipments_pending' => (clone $shipmentQuery)->whereHas('status', fn ($query) => $query->where('code', 'pending'))->count(),
            'shipments_delivered' => (clone $shipmentQuery)->whereHas('status', fn ($query) => $query->where('code', 'delivered'))->count(),
            'revenue_total' => (float) (clone $paymentQuery)->where('status', 'settlement')->sum('amount'),
            'payment_pending' => (float) (clone $paymentQuery)->where('status', 'pending')->sum('amount'),
            'branches_total' => Branch::query()->count(),
            'couriers_total' => User::query()->where('role', 'courier')->count(),
        ];
    }

    private function buildBranchPerformanceData(?string $fromDate, ?string $untilDate): array
    {
        return Branch::query()
            ->withCount(['shipments'])
            ->get()
            ->map(fn (Branch $branch) => [
                'id' => $branch->id,
                'code' => $branch->code,
                'name' => $branch->name,
                'shipments_total' => $branch->shipments_count,
                'revenue_total' => (float) Payment::query()
                    ->whereHas('shipment', fn ($query) => $query->where('branch_id', $branch->id))
                    ->where('status', 'settlement')
                    ->when($fromDate || $untilDate, function ($query) use ($fromDate, $untilDate) {
                        $this->applyDateRange($query, 'created_at', $fromDate, $untilDate);
                    })
                    ->sum('amount'),
            ])
            ->values()
            ->all();
    }

    private function resolveDateRange(Request $request): array
    {
        return [
            $request->query('from'),
            $request->query('until'),
        ];
    }

    private function applyDateRange($query, string $column, ?string $fromDate, ?string $untilDate): void
    {
        if ($fromDate) {
            $query->whereDate($column, '>=', $fromDate);
        }

        if ($untilDate) {
            $query->whereDate($column, '<=', $untilDate);
        }
    }
}
