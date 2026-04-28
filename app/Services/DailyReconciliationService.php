<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Shipment;
use App\Models\ShipmentTracking;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DailyReconciliationService
{
    public function buildForDate(Carbon|string|null $date = null, int $limit = 25): array
    {
        $targetDate = $date instanceof Carbon
            ? $date->copy()
            : Carbon::parse($date ?? now());

        $start = $targetDate->copy()->startOfDay();
        $end = $targetDate->copy()->endOfDay();

        $shipments = Shipment::query()
            ->with(['status:id,code,name,is_final', 'branch:id,name,code', 'courier:id,name', 'customer.user:id,name,phone'])
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start, $end])
                    ->orWhereBetween('updated_at', [$start, $end]);
            })
            ->latest('updated_at')
            ->get();

        $payments = Payment::query()
            ->with(['shipment.status:id,code,name,is_final'])
            ->whereBetween('created_at', [$start, $end])
            ->latest('created_at')
            ->get();

        $trackings = ShipmentTracking::query()
            ->with(['shipment.status:id,code,name,is_final', 'status:id,code,name,is_final'])
            ->whereBetween('event_at', [$start, $end])
            ->latest('event_at')
            ->get();

        $shipmentMap = $shipments->keyBy('id');
        $shipmentIds = collect()
            ->merge($shipments->pluck('id'))
            ->merge($payments->pluck('shipment_id'))
            ->merge($trackings->pluck('shipment_id'))
            ->filter()
            ->unique()
            ->values();

        $summary = [
            'shipments_checked' => $shipmentIds->count(),
            'payments_checked' => $payments->count(),
            'trackings_checked' => $trackings->count(),
            'final_shipments' => $shipmentMap->filter(fn (Shipment $shipment) => (bool) $shipment->status?->is_final)->count(),
            'automatic_shipments' => $shipmentMap->filter(fn (Shipment $shipment) => $this->shipmentOrigin($shipment) === 'automatic')->count(),
            'manual_shipments' => $shipmentMap->filter(fn (Shipment $shipment) => $this->shipmentOrigin($shipment) === 'manual_override')->count(),
            'automatic_payments' => $payments->filter(fn ($payment) => $this->paymentOrigin($payment) === 'automatic')->count(),
            'manual_payments' => $payments->filter(fn ($payment) => $this->paymentOrigin($payment) === 'manual_override')->count(),
            'matched_shipments' => 0,
            'exception_shipments' => 0,
        ];

        $shipmentPaymentMap = $payments->groupBy('shipment_id');
        $shipmentTrackingMap = $trackings->groupBy('shipment_id');
        $exceptions = collect();

        foreach ($shipmentIds as $shipmentId) {
            $shipment = $shipmentMap->get($shipmentId) ?? Shipment::query()
                ->with(['status:id,code,name,is_final', 'branch:id,name,code', 'courier:id,name', 'customer.user:id,name,phone'])
                ->find($shipmentId);

            if (! $shipment) {
                continue;
            }

            $issues = $this->inspectShipment($shipment, $shipmentPaymentMap->get($shipmentId, collect()), $shipmentTrackingMap->get($shipmentId, collect()));

            if ($issues->isEmpty()) {
                $summary['matched_shipments']++;
                continue;
            }

            $summary['exception_shipments']++;

            $exceptions->push([
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'branch' => $shipment->branch?->only(['id', 'code', 'name']),
                'courier' => $shipment->courier?->only(['id', 'name']),
                'customer' => $shipment->customer?->user?->only(['id', 'name', 'phone']),
                'shipment_status' => $shipment->status?->only(['code', 'name', 'is_final']),
                'data_origin' => [
                    'shipment' => $this->shipmentOrigin($shipment),
                    'payment' => $this->paymentOrigin($shipmentPaymentMap->get($shipmentId, collect())->sortByDesc('created_at')->first()),
                ],
                'payment_status' => $shipment->payment_status,
                'latest_payment_status' => $this->latestPaymentStatus($shipmentPaymentMap->get($shipmentId, collect())),
                'latest_tracking_status' => $this->latestTrackingStatus($shipmentTrackingMap->get($shipmentId, collect())),
                'issues' => $issues->values(),
                'manual_action' => $this->recommendedAction($issues),
                'reconciliation_date' => $start->toDateString(),
            ]);
        }

        $issueBreakdown = $exceptions
            ->flatMap(fn (array $exception) => $exception['issues'])
            ->groupBy('code')
            ->map(fn (Collection $items, string $code) => [
                'code' => $code,
                'count' => $items->count(),
            ])
            ->values();

        return [
            'period' => [
                'date' => $start->toDateString(),
                'from' => $start->toDateTimeString(),
                'until' => $end->toDateTimeString(),
            ],
            'summary' => [
                ...$summary,
                'issue_breakdown' => $issueBreakdown,
            ],
            'exceptions' => $exceptions->take($limit)->values(),
            'meta' => [
                'total_exceptions' => $exceptions->count(),
                'returned' => min($limit, $exceptions->count()),
                'limit' => $limit,
            ],
        ];
    }

    private function inspectShipment(Shipment $shipment, Collection $payments, Collection $trackings): Collection
    {
        $issues = collect();
        $latestPayment = $payments->sortByDesc('created_at')->first();
        $latestTracking = $trackings->sortByDesc('event_at')->first();
        $finalStatusCode = $shipment->status?->is_final ? $shipment->status?->code : null;
        $normalizedLatestPaymentStatus = $this->normalizePaymentStatus($latestPayment?->status);

        if ($payments->isEmpty()) {
            $issues->push($this->issue('missing_payment', 'Shipment belum memiliki payment terkait.', 'Buat payment manual atau sinkronkan callback gateway.', 'high'));
        }

        if ($trackings->isEmpty()) {
            $issues->push($this->issue('missing_tracking', 'Shipment belum memiliki tracking event.', 'Tambahkan tracking atau sinkronkan event kurir.', 'high'));
        }

        if ($latestPayment && $shipment->payment_status !== $normalizedLatestPaymentStatus) {
            $issues->push($this->issue('payment_status_mismatch', 'Status payment shipment berbeda dengan payment terbaru.', 'Review callback pembayaran lalu lakukan koreksi manual jika perlu.', 'medium'));
        }

        if ($shipment->status?->is_final) {
            if (! $latestTracking) {
                $issues->push($this->issue('final_status_without_tracking', 'Shipment final tetapi belum ada tracking penutup.', 'Lengkapi tracking final sesuai status shipment.', 'high'));
            } elseif ($latestTracking->status?->code !== $finalStatusCode) {
                $issues->push($this->issue('final_status_tracking_mismatch', 'Tracking terakhir tidak sama dengan status final shipment.', 'Sinkronkan status tracking terakhir agar sama dengan status shipment final.', 'high'));
            }

            if ($shipment->status?->code === 'delivered' && ! in_array($shipment->payment_status, ['paid', 'settlement'], true)) {
                $issues->push($this->issue('delivered_unpaid', 'Shipment delivered tetapi payment belum settled/paid.', 'Tinjau payment gateway atau lakukan koreksi payment manual.', 'high'));
            }
        }

        if ($shipment->payment_status === 'paid' && ! $shipment->status?->is_final) {
            $issues->push($this->issue('paid_not_final', 'Payment sudah paid tetapi shipment belum final.', 'Pastikan tracking terakhir diperbarui dan shipment ditutup bila sudah selesai.', 'medium'));
        }

        return $issues->unique(fn (array $issue) => $issue['code'])->values();
    }

    private function latestPaymentStatus(Collection $payments): ?array
    {
        $latest = $payments->sortByDesc('created_at')->first();

        if (! $latest) {
            return null;
        }

        return [
            'raw' => $latest->status,
            'normalized' => $this->normalizePaymentStatus($latest->status),
        ];
    }

    private function latestTrackingStatus(Collection $trackings): ?array
    {
        $latest = $trackings->sortByDesc('event_at')->first();

        if (! $latest) {
            return null;
        }

        return $latest->status?->only(['code', 'name', 'is_final']);
    }

    private function recommendedAction(Collection $issues): array
    {
        $actionMap = [
            'missing_payment' => 'reconcile_payment',
            'missing_tracking' => 'sync_tracking',
            'payment_status_mismatch' => 'review_payment_callback',
            'final_status_without_tracking' => 'close_tracking',
            'final_status_tracking_mismatch' => 'correct_tracking_status',
            'delivered_unpaid' => 'review_payment_settlement',
            'paid_not_final' => 'finalize_shipment_status',
        ];

        $firstIssue = $issues->first()['code'] ?? null;
        $hasHighSeverity = $issues->contains(fn (array $issue) => $issue['severity'] === 'high');

        return [
            'action' => $firstIssue ? ($actionMap[$firstIssue] ?? 'manual_review') : 'manual_review',
            'priority' => $hasHighSeverity ? 'high' : 'medium',
            'notes' => 'Gunakan exception ini sebagai daftar kerja koreksi manual harian.',
        ];
    }

    private function issue(string $code, string $message, string $recommendedFix, string $severity): array
    {
        return [
            'code' => $code,
            'message' => $message,
            'recommended_fix' => $recommendedFix,
            'severity' => $severity,
        ];
    }

    private function normalizePaymentStatus(?string $status): ?string
    {
        return match ($status) {
            'settlement' => 'paid',
            'pending' => 'pending',
            'refund' => 'refunded',
            'deny', 'expire', 'cancel', 'failed' => 'failed',
            default => $status,
        };
    }

    private function shipmentOrigin(Shipment $shipment): string
    {
        return $shipment->pricing_mode === 'manual' || $shipment->manual_override_by ? 'manual_override' : 'automatic';
    }

    private function paymentOrigin($payment): string
    {
        if (! $payment) {
            return 'automatic';
        }

        return $payment->manual_override_by ? 'manual_override' : 'automatic';
    }
}