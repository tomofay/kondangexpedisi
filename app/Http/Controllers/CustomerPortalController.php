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
    public function search(Request $request): RedirectResponse
    {
        $customer = $this->resolveCustomer($request);
        $trackingNumber = $request->query('tracking_number') ?? $request->query('q');
        
        if (!$trackingNumber) {
            return redirect()->back()->with('error', 'Masukkan nomor resi.');
        }

        $shipment = $customer->shipments()
            ->where('tracking_number', $trackingNumber)
            ->first();
        
        if (!$shipment) {
            return redirect()->route('customer.shipments.index', ['q' => $trackingNumber])
                ->with('error', 'Nomor resi tidak ditemukan dalam daftar paket Anda.');
        }
        
        return redirect()->route('customer.shipments.track', $shipment);
    }

    public function index(Request $request): View
    {
        $customer = $this->resolveCustomer($request);

        $stats = [
            'shipments_total' => $customer->shipments()->count(),
            'payments_total' => $customer->shipments()->where('status_id', 8)->count(),
            'pending_shipments' => $customer->shipments()->where('payment_status', 'pending')->count(),
            'recent_shipments' => $customer->shipments()->where('status_id', '!=', 8)->with('status')->latest()->limit(3)->get(),
        ];

        return view('mobile.customer.dashboard', compact('customer', 'stats'));
    }

    public function list(Request $request): View
    {
        $customer = $this->resolveCustomer($request);
        $query = $customer->shipments()->with('status')->latest();

        // Search by tracking number or recipient name
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($search) use ($q) {
                $search->where('tracking_number', 'like', '%' . $q . '%')
                      ->orWhere('recipient_name', 'like', '%' . $q . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status_id', $request->status);
        }

        $shipments = $query->paginate(10);
        $statuses = ShipmentStatus::all();

        return view('mobile.customer.shipments_index', compact('shipments', 'statuses'));
    }

    public function track(Request $request, Shipment $shipment): View
    {
        $customer = $this->resolveCustomer($request);
        abort_unless((int) $shipment->customer_id === (int) $customer->id, 403);

        $shipment->load(['branch', 'courier', 'status', 'items', 'payments', 'trackings.status', 'trackings.proofs']);

        return view('mobile.customer.tracking', compact('shipment'));
    }

    public function create(Request $request): View
    {
        $customer = $this->resolveCustomer($request);
        $branches = Branch::query()->where('is_active', true)->get();
        return view('mobile.customer.create_shipment', compact('branches', 'customer'));
    }

    public function rates(Request $request): JsonResponse
    {
        $this->resolveCustomer($request);
        $origin = $request->integer('origin_branch_id');
        $destination = $request->integer('destination_branch_id');

        if (!$origin || !$destination) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $rates = \App\Models\RateCard::where('origin_branch_id', $origin)
            ->where('destination_branch_id', $destination)
            ->where('is_active', true)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $rates
        ]);
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
            'item_name' => 'required|string|max:255',
            'total_items' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $shipment = $shipmentService->createShipment(array_merge($validated, [
                'customer_id' => $customer->id,
            ]), $request->user());

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
