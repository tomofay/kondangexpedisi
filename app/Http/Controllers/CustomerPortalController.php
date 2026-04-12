<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerPortalController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        return response()->json([
            'customer' => $customer,
            'shipments_total' => $customer->shipments()->count(),
            'payments_total' => $customer->payments()->count(),
            'pending_shipments' => $customer->shipments()->where('payment_status', 'pending')->count(),
            'recent_shipments' => $customer->shipments()->with('status')->latest()->limit(5)->get(),
            'recent_payments' => $customer->payments()->latest()->limit(5)->get(),
        ]);
    }

    public function shipments(Request $request): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        $query = $customer->shipments()->with(['status', 'branch', 'trackings.status']);

        if ($request->filled('status')) {
            $query->whereHas('status', fn ($statusQuery) => $statusQuery->where('code', $request->string('status')));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->string('from'));
        }

        if ($request->filled('until')) {
            $query->whereDate('created_at', '<=', $request->string('until'));
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function payments(Request $request): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        $query = $customer->payments()->with(['shipment.status']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->string('from'));
        }

        if ($request->filled('until')) {
            $query->whereDate('created_at', '<=', $request->string('until'));
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function shipmentDetail(Request $request, Shipment $shipment): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        abort_unless((int) $shipment->customer_id === (int) $customer->id, 403, 'Shipment ini bukan milik customer saat ini.');

        return response()->json($shipment->load(['branch', 'courier', 'status', 'items', 'payments', 'trackings.status']));
    }

    public function paymentDetail(Request $request, Payment $payment): JsonResponse
    {
        $customer = $this->resolveCustomer($request);

        abort_unless((int) $payment->customer_id === (int) $customer->id, 403, 'Payment ini bukan milik customer saat ini.');

        return response()->json($payment->load(['shipment.status', 'shipment.trackings.status']));
    }

    private function resolveCustomer(Request $request): Customer
    {
        $user = $request->user();

        abort_unless($user && $user->role === 'customer', 403, 'Akses customer portal hanya untuk role customer.');

        $customer = $user->customer;

        abort_unless($customer, 404, 'Profil customer belum terhubung dengan akun ini.');

        return $customer;
    }
}
