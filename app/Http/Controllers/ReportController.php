<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
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

        $deliveredCount = (clone $shipmentQuery)->whereHas('status', fn ($query) => $query->where('code', 'delivered'))->count();
        $totalShipments = $shipmentQuery->count();
        $deliveredOnTime = (clone $shipmentQuery)
            ->whereHas('status', fn ($query) => $query->where('code', 'delivered'))
            ->whereNotNull('delivered_at')
            ->whereNotNull('estimated_delivery_at')
            ->whereColumn('delivered_at', '<=', 'estimated_delivery_at')
            ->count();

        $data = [
            'period' => [
                'from' => $fromDate,
                'until' => $untilDate,
            ],
            'total_shipments' => $totalShipments,
            'delivered_count' => $deliveredCount,
            'cancelled_count' => (clone $shipmentQuery)->whereHas('status', fn ($query) => $query->where('code', 'cancelled'))->count(),
            'returned_count' => (clone $shipmentQuery)->whereHas('status', fn ($query) => $query->where('code', 'returned'))->count(),
            'total_revenue' => (float) (clone $paymentQuery)->where('status', 'settlement')->sum('amount'),
            'pending_revenue' => (float) (clone $paymentQuery)->where('status', 'pending')->sum('amount'),
            'on_time_rate' => $deliveredCount > 0 ? round(($deliveredOnTime / $deliveredCount) * 100, 1) : 0,
            'manual_override_count' => (clone $shipmentQuery)->whereNotNull('manual_override_by')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function branchPerformance(Request $request): JsonResponse
    {
        $actor = $request->user();
        [$fromDate, $untilDate] = $this->resolveDateRange($request);

        $query = Branch::query();

        if (in_array($actor?->role, ['manager', 'kasir'], true)) {
            $query->where('id', $actor->branch_id);
        }

        $branches = $query
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

        return response()->json([
            'status' => 'success',
            'data' => $branches
        ]);
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
        $actor = $request->user();
        [$fromDate, $untilDate] = $this->resolveDateRange($request);

        $couriers = User::query()
            ->where('role', 'courier')
            ->when(in_array($actor?->role, ['manager', 'kasir'], true), fn ($q) => $q->where('branch_id', $actor->branch_id))
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

        return response()->json([
            'status' => 'success',
            'data' => $couriers
        ]);
    }

    public function paymentOverview(Request $request): JsonResponse
    {
        $actor = $request->user();
        [$fromDate, $untilDate] = $this->resolveDateRange($request);

        $paymentQuery = Payment::query()->when($fromDate || $untilDate, function ($query) use ($fromDate, $untilDate) {
            $this->applyDateRange($query, 'created_at', $fromDate, $untilDate);
        });

        if (in_array($actor?->role, ['manager', 'kasir'], true)) {
            $paymentQuery->whereHas('shipment', fn ($q) => $q->where('branch_id', $actor->branch_id));
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'period' => [
                    'from' => $fromDate,
                    'until' => $untilDate,
                ],
                'settlement' => (float) (clone $paymentQuery)->where('status', 'settlement')->sum('amount'),
                'pending' => (float) (clone $paymentQuery)->where('status', 'pending')->sum('amount'),
                'failed' => (float) (clone $paymentQuery)->whereIn('status', ['deny', 'expire', 'cancel', 'failed'])->sum('amount'),
                'refund' => (float) (clone $paymentQuery)->where('status', 'refund')->sum('amount'),
            ]
        ]);
    }

    public function summaryExport(Request $request)
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

        $totalShipments = $shipmentQuery->count();
        $deliveredCount = (clone $shipmentQuery)->whereHas('status', fn ($query) => $query->where('code', 'delivered'))->count();
        $cancelledCount = (clone $shipmentQuery)->whereHas('status', fn ($query) => $query->where('code', 'cancelled'))->count();
        $returnedCount = (clone $shipmentQuery)->whereHas('status', fn ($query) => $query->where('code', 'returned'))->count();
        
        $deliveredOnTime = (clone $shipmentQuery)
            ->whereHas('status', fn ($query) => $query->where('code', 'delivered'))
            ->whereNotNull('delivered_at')
            ->whereNotNull('estimated_delivery_at')
            ->whereColumn('delivered_at', '<=', 'estimated_delivery_at')
            ->count();

        $onTimeRate = $deliveredCount > 0 ? round(($deliveredOnTime / $deliveredCount) * 100, 1) : 0;

        $revenueTotal = (float) (clone $paymentQuery)->where('status', 'settlement')->sum('amount');
        $paymentPending = (float) (clone $paymentQuery)->where('status', 'pending')->sum('amount');
        $paymentFailed = (float) (clone $paymentQuery)->whereIn('status', ['deny', 'expire', 'cancel', 'failed'])->sum('amount');

        $revenueByMethod = (clone $paymentQuery)
            ->where('status', 'settlement')
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->get();

        return response()->streamDownload(function () use ($fromDate, $untilDate, $totalShipments, $deliveredCount, $cancelledCount, $returnedCount, $onTimeRate, $revenueTotal, $paymentPending, $paymentFailed, $revenueByMethod) {
            $output = fopen('php://output', 'w');
            
            // Section 1: Period
            fputcsv($output, ['REPORT SUMMARY']);
            fputcsv($output, ['Period From', $fromDate ?: 'All Time']);
            fputcsv($output, ['Period Until', $untilDate ?: 'Now']);
            fputcsv($output, []);

            // Section 2: Operational Metrics
            fputcsv($output, ['OPERATIONAL METRICS']);
            fputcsv($output, ['Total Shipments', $totalShipments]);
            fputcsv($output, ['Delivered', $deliveredCount]);
            fputcsv($output, ['Cancelled', $cancelledCount]);
            fputcsv($output, ['Returned', $returnedCount]);
            fputcsv($output, ['On-Time Delivery Rate', $onTimeRate . '%']);
            fputcsv($output, []);

            // Section 3: Financial Metrics
            fputcsv($output, ['FINANCIAL METRICS']);
            fputcsv($output, ['Total Revenue (Settled)', $revenueTotal]);
            fputcsv($output, ['Total Pending', $paymentPending]);
            fputcsv($output, ['Total Failed/Cancelled', $paymentFailed]);
            fputcsv($output, []);

            // Section 4: Revenue by Method
            fputcsv($output, ['REVENUE BY PAYMENT METHOD']);
            fputcsv($output, ['Method', 'Amount']);
            foreach ($revenueByMethod as $m) {
                fputcsv($output, [ucfirst($m->method), $m->total]);
            }
            fputcsv($output, []);

            // Section 5: Branch Breakdown (If Admin)
            if (request()->user()?->role === 'admin') {
                $branches = Branch::query()->get();
                fputcsv($output, ['BRANCH PERFORMANCE BREAKDOWN']);
                fputcsv($output, ['Branch Code', 'Branch Name', 'Total Shipments', 'Total Revenue']);
                foreach ($branches as $branch) {
                    $bShipments = Shipment::query()->where('branch_id', $branch->id)
                        ->when($fromDate || $untilDate, fn($q) => $this->applyDateRange($q, 'created_at', $fromDate, $untilDate))
                        ->count();
                    $bRevenue = Payment::query()
                        ->whereHas('shipment', fn($q) => $q->where('branch_id', $branch->id))
                        ->where('status', 'settlement')
                        ->when($fromDate || $untilDate, fn($q) => $this->applyDateRange($q, 'created_at', $fromDate, $untilDate))
                        ->sum('amount');
                    fputcsv($output, [$branch->code, $branch->name, $bShipments, $bRevenue]);
                }
            }

            fclose($output);
        }, 'report-summary-' . now()->format('Y-m-d') . '.csv');
    }

    public function branchPerformanceExport(Request $request)
    {
        [$fromDate, $untilDate] = $this->resolveDateRange($request);
        $branches = Branch::query()->withCount(['shipments'])->get();

        return response()->streamDownload(function () use ($branches, $fromDate, $untilDate) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['branch', 'code', 'shipments_count', 'revenue']);

            foreach ($branches as $branch) {
                $revenue = Payment::query()
                    ->whereHas('shipment', fn ($query) => $query->where('branch_id', $branch->id))
                    ->where('status', 'settlement')
                    ->when($fromDate || $untilDate, fn ($q) => $this->applyDateRange($q, 'created_at', $fromDate, $untilDate))
                    ->sum('amount');

                fputcsv($output, [$branch->name, $branch->code, $branch->shipments_count, $revenue]);
            }
            fclose($output);
        }, 'branch-performance-report.csv');
    }

    private function resolveDateRange(Request $request): array
    {
        return [ $request->query('from'), $request->query('until') ];
    }

    private function applyDateRange($query, string $column, ?string $fromDate, ?string $untilDate): void
    {
        if ($fromDate) $query->whereDate($column, '>=', $fromDate);
        if ($untilDate) $query->whereDate($column, '<=', $untilDate);
    }
}
