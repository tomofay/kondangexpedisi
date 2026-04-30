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

class CourierPortalController extends Controller
{
    public function tasks(Request $request): View
    {
        $courier = $request->user();
        
        $tasks = Shipment::query()
            ->where('courier_id', $courier->id)
            ->with(['status', 'branch', 'trackings.status'])
            ->latest()
            ->get();

        return view('mobile.courier.tasks', compact('tasks'));
    }

    public function edit(Shipment $shipment): View
    {
        abort_unless((int) $shipment->courier_id === (int) auth()->id(), 403);
        
        $shipment->load(['status', 'trackings.status', 'branch']);
        
        return view('mobile.courier.update_status', compact('shipment'));
    }

    public function update(Request $request, Shipment $shipment, ShipmentService $shipmentService): RedirectResponse
    {
        abort_unless((int) $shipment->courier_id === (int) auth()->id(), 403);
        
        $validated = $request->validate([
            'status_code' => 'required|string|exists:shipment_statuses,code',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
            'proof_photo' => 'nullable|image|max:5120', // Max 5MB
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
}
