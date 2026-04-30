<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\Branch;
use App\Models\ShipmentStatus;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CustomerPortalController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $this->resolveCustomer($request);

        $stats = [
            'shipments_total' => $customer->shipments()->count(),
            'payments_total' => $customer->payments()->count(),
            'pending_shipments' => $customer->shipments()->where('payment_status', 'pending')->count(),
            'recent_shipments' => $customer->shipments()->with('status')->latest()->limit(5)->get(),
        ];

        return view('mobile.customer.dashboard', compact('customer', 'stats'));
    }

    public function track(Request $request, Shipment $shipment): View
    {
        $customer = $this->resolveCustomer($request);
        abort_unless((int) $shipment->customer_id === (int) $customer->id, 403);

        $shipment->load(['branch', 'courier', 'status', 'items', 'payments', 'trackings.status']);

        return view('mobile.customer.tracking', compact('shipment'));
    }

    public function create(Request $request): View
    {
        $this->resolveCustomer($request);
        $branches = Branch::query()->where('is_active', true)->get();
        return view('mobile.customer.create_shipment', compact('branches'));
    }

    public function quote(Request $request, ShipmentService $shipmentService): JsonResponse
    {
        $customer = $this->resolveCustomer($request);
        
        try {
            $quote = $shipmentService->calculateTotalAmount([
                'branch_id' => $request->integer('branch_id'),
                'destination_branch_id' => $request->integer('destination_branch_id'),
                'total_weight_kg' => $request->float('total_weight_kg'),
                'service_type' => $request->string('service_type', 'regular'),
                'insurance_amount' => $request->float('insurance_amount', 0),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $quote
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function store(Request $request, ShipmentService $shipmentService): RedirectResponse
    {
        $customer = $this->resolveCustomer($request);

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'destination_branch_id' => 'required|exists:branches,id',
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:20',
            'sender_address' => 'required|string|max:500',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'recipient_address' => 'required|string|max:500',
            'service_type' => 'required|in:regular,express,economy,same_day',
            'total_weight_kg' => 'required|numeric|min:0.1',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $shipment = DB::transaction(function () use ($customer, $validated, $shipmentService) {
                // Calculate final pricing
                $pricing = $shipmentService->calculateTotalAmount($validated);

                $statusId = ShipmentStatus::query()->where('code', 'pending')->value('id');

                $shipment = Shipment::query()->create([
                    'tracking_number' => $shipmentService->generateTrackingNumber($validated['branch_id']),
                    'customer_id' => $customer->id,
                    'branch_id' => $validated['branch_id'],
                    'destination_branch_id' => $validated['destination_branch_id'],
                    'status_id' => $statusId,
                    'sender_name' => $validated['sender_name'],
                    'sender_phone' => $validated['sender_phone'],
                    'sender_address' => $validated['sender_address'],
                    'recipient_name' => $validated['recipient_name'],
                    'recipient_phone' => $validated['recipient_phone'],
                    'recipient_address' => $validated['recipient_address'],
                    'service_type' => $validated['service_type'],
                    'total_weight_kg' => $validated['total_weight_kg'],
                    'subtotal_amount' => $pricing['subtotal_amount'],
                    'insurance_amount' => $pricing['insurance_amount'],
                    'admin_fee' => $pricing['admin_fee'],
                    'total_amount' => $pricing['total_amount'],
                    'auto_subtotal_amount' => $pricing['subtotal_amount'],
                    'auto_insurance_amount' => $pricing['insurance_amount'],
                    'auto_admin_fee' => $pricing['admin_fee'],
                    'auto_total_amount' => $pricing['total_amount'],
                    'payment_status' => 'pending',
                    'pricing_mode' => $pricing['calculation_mode'] === 'fallback_estimate' ? 'manual' : 'auto',
                    'pricing_approval_status' => $pricing['requires_manual_approval'] ? 'pending' : 'not_required',
                    'notes' => $validated['notes'],
                    'current_status_at' => now(),
                ]);

                // Initial tracking
                $shipment->trackings()->create([
                    'status_id' => $statusId,
                    'location' => Branch::query()->find($validated['branch_id'])->name,
                    'notes' => 'Order dibuat oleh customer via mobile portal.',
                    'event_at' => now(),
                ]);

                return $shipment;
            });

            return redirect()->route('customer.shipments.track', $shipment)
                ->with('success', 'Pemesanan berhasil dibuat! Silakan lakukan pembayaran.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal membuat pemesanan: ' . $e->getMessage());
        }
    }

    private function resolveCustomer(Request $request): Customer
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'customer', 403);

        $customer = $user->customer;
        abort_unless($customer, 404, 'Profil customer belum terhubung.');

        return $customer;
    }
}
