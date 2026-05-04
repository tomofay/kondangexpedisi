<?php
namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentStatus;
use App\Models\ShipmentTrackingProof;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class CourierPortalController extends Controller
{
    public function tasks(Request $request): View
    {
        $courier = $request->user();
        
        // Dashboard: Only show max 3 active (non-delivered) shipments
        $tasks = Shipment::query()
            ->where('courier_id', $courier->id)
            ->where('status_id', '!=', 8) // Exclude delivered
            ->with(['status', 'branch'])
            ->latest()
            ->take(3)
            ->get();

        $stats = [
            'pending' => Shipment::where('courier_id', $courier->id)->where('status_id', '!=', 8)->count(),
            'completed' => Shipment::where('courier_id', $courier->id)->where('status_id', 8)->count(),
        ];

        return view('mobile.courier.tasks', compact('tasks', 'stats'));
    }

    public function index(Request $request): View
    {
        $courier = $request->user();
        $query = Shipment::query()
            ->where('courier_id', $courier->id)
            ->where('status_id', '!=', 8) // Exclude delivered
            ->with(['status', 'branch']);

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('tracking_number', 'like', '%' . $request->search . '%')
                  ->orWhere('recipient_name', 'like', '%' . $request->search . '%');
            });
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->whereHas('status', function($q) use ($request) {
                $q->where('code', $request->status);
            });
        }

        $shipments = $query->latest()->get();
        $statuses = ShipmentStatus::where('code', '!=', 'delivered')->get();

        return view('mobile.courier.all_tasks', compact('shipments', 'statuses'));
    }

    public function bulkUpdate(Request $request, ShipmentService $shipmentService): RedirectResponse
    {
        $request->validate([
            'shipment_ids' => 'required|array',
            'shipment_ids.*' => 'exists:shipments,id',
            'status_code' => 'required|string|exists:shipment_statuses,code',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $updatedCount = 0;
            DB::transaction(function () use ($request, $shipmentService, &$updatedCount) {
                foreach ($request->shipment_ids as $id) {
                    $shipment = Shipment::with('status')->findOrFail($id);
                    
                    // Security check
                    if ((int) $shipment->courier_id !== (int) auth()->id()) continue;

                    // Skip if already in this status to avoid validation errors
                    if ($shipment->status->code === $request->status_code) continue;

                    $shipmentService->transitionStatus(
                        $shipment,
                        $request->status_code,
                        auth()->id(),
                        $request->location,
                        $request->notes
                    );
                    $updatedCount++;
                }
            });

            if ($updatedCount === 0) {
                return redirect()->route('courier.shipments.index')->with('info', 'Semua paket terpilih sudah berada pada status tersebut.');
            }

            return redirect()->route('courier.shipments.index')->with('success', $updatedCount . ' paket berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui paket: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Shipment $shipment): View
    {
        abort_unless((int) $shipment->courier_id === (int) auth()->id(), 403);
        
        $shipment->load(['status', 'trackings.status', 'branch']);
        $courier = auth()->user()->load('branch');
        
        return view('mobile.courier.update_status', compact('shipment', 'courier'));
    }

    public function bulkEdit(Request $request): View
    {
        $request->validate([
            'shipment_ids' => 'required|array',
            'shipment_ids.*' => 'exists:shipments,id',
        ]);

        $shipments = Shipment::whereIn('id', $request->shipment_ids)
            ->where('courier_id', auth()->id())
            ->with(['status', 'branch'])
            ->get();

        abort_if($shipments->isEmpty(), 403);
        
        $courier = auth()->user()->load('branch');
        
        return view('mobile.courier.bulk_update_status', compact('shipments', 'courier'));
    }

    public function update(Request $request, Shipment $shipment, ShipmentService $shipmentService): RedirectResponse
    {
        abort_unless((int) $shipment->courier_id === (int) auth()->id(), 403);
        
        $validated = $request->validate([
            'status_code' => 'required|string|exists:shipment_statuses,code',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
            'proof_photo' => [
                Rule::requiredIf(fn () => in_array($request->status_code, ['delivered', 'failed_delivery', 'returned'])),
                'nullable',
                'image', 
                'max:5120'
            ],
            'gps_lat' => 'nullable|numeric',
            'gps_lng' => 'nullable|numeric',
        ]);

        try {
            DB::transaction(function () use ($shipment, $validated, $shipmentService, $request) {
                // 1. Transition Status
                $shipmentService->transitionStatus(
                    $shipment,
                    $validated['status_code'],
                    auth()->id(),
                    $validated['location'],
                    $validated['notes'],
                    false, // forceTransition
                    null,  // overrideReason
                    $validated['gps_lat'] ?? null,
                    $validated['gps_lng'] ?? null
                );

                // 2. Handle Proof Photo if uploaded
                if ($request->hasFile('proof_photo')) {
                    $path = $request->file('proof_photo')->store('operational-proofs', 'public');
                    
                    $latestTracking = $shipment->trackings()->latest('id')->first();

                    ShipmentTrackingProof::query()->create([
                        'shipment_id' => $shipment->id,
                        'tracking_id' => $latestTracking?->id,
                        'uploaded_by' => auth()->id(),
                        'proof_type' => in_array($validated['status_code'], ['delivered', 'received_by_recipient']) ? 'recipient_signature' : 'handover_photo',
                        'file_path' => $path,
                        'file_mime' => $request->file('proof_photo')->getMimeType(),
                        'file_size' => $request->file('proof_photo')->getSize(),
                        'file_hash' => hash_file('sha256', $request->file('proof_photo')->getRealPath()),
                        'captured_at' => now(),
                        'gps_lat' => $validated['gps_lat'] ?? null,
                        'gps_lng' => $validated['gps_lng'] ?? null,
                        'notes' => $validated['notes'],
                    ]);
                }
            });

            return redirect()->route('courier.tasks')->with('success', 'Status berhasil diperbarui.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage())->withInput();
        }
    }

    public function claim(Request $request): RedirectResponse
    {
        $request->validate([
            'tracking_number' => 'required|string|exists:shipments,tracking_number',
        ]);

        try {
            $shipment = Shipment::where('tracking_number', $request->tracking_number)->firstOrFail();
            
            // Assign to current courier
            $shipment->update([
                'courier_id' => auth()->id()
            ]);

            return redirect()->route('courier.shipments.edit', $shipment)
                ->with('success', 'Paket berhasil diambil alih.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengambil paket: ' . $e->getMessage());
        }
    }
}
