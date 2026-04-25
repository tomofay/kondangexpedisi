<?php

namespace App\Http\Controllers;

use App\Models\ShipmentStatus;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class ShipmentStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(ShipmentStatus::query()->orderBy('sequence')->paginate(15));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validateWorkflowStatusContract($request->input('code'), $request->integer('sequence'), $request->boolean('is_final'));

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:shipment_statuses,code'],
            'name' => ['required', 'string', 'max:255'],
            'sequence' => ['required', 'integer', 'min:1'],
            'is_final' => ['sometimes', 'boolean'],
            'badge_color' => ['required', 'string', 'max:30'],
        ]);

        $status = ShipmentStatus::query()->create($validated);

        return response()->json(['message' => 'Shipment status created.', 'data' => $status], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShipmentStatus $shipmentStatus)
    {
        return response()->json($shipmentStatus->load('shipments', 'trackings'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShipmentStatus $shipmentStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ShipmentStatus $shipmentStatus)
    {
        if ($this->isWorkflowStatus($shipmentStatus->code)) {
            $this->validateWorkflowStatusContract(
                $request->input('code', $shipmentStatus->code),
                $request->has('sequence') ? $request->integer('sequence') : $shipmentStatus->sequence,
                $request->has('is_final') ? $request->boolean('is_final') : $shipmentStatus->is_final
            );

            if ($request->has('code') && $request->input('code') !== $shipmentStatus->code) {
                throw ValidationException::withMessages([
                    'code' => 'Kode status inti tidak boleh diubah.',
                ]);
            }

            if ($request->has('sequence') && $request->integer('sequence') !== $shipmentStatus->sequence) {
                throw ValidationException::withMessages([
                    'sequence' => 'Urutan status inti tidak boleh diubah.',
                ]);
            }

            if ($request->has('is_final') && $request->boolean('is_final') !== $shipmentStatus->is_final) {
                throw ValidationException::withMessages([
                    'is_final' => 'Penanda final status inti tidak boleh diubah.',
                ]);
            }
        }

        $validated = $request->validate([
            'code' => ['sometimes', 'string', 'max:40', 'unique:shipment_statuses,code,' . $shipmentStatus->id],
            'name' => ['sometimes', 'string', 'max:255'],
            'sequence' => ['sometimes', 'integer', 'min:1'],
            'is_final' => ['sometimes', 'boolean'],
            'badge_color' => ['sometimes', 'string', 'max:30'],
        ]);

        $shipmentStatus->update($validated);

        return response()->json(['message' => 'Shipment status updated.', 'data' => $shipmentStatus->fresh()]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShipmentStatus $shipmentStatus)
    {
        if ($this->isWorkflowStatus($shipmentStatus->code)) {
            throw ValidationException::withMessages([
                'shipment_status' => 'Status inti tidak boleh dihapus karena dipakai oleh workflow shipment.',
            ]);
        }

        $shipmentStatus->delete();

        return response()->json(['message' => 'Shipment status deleted.']);
    }

    private function isWorkflowStatus(string $code): bool
    {
        return in_array($code, config('expedition.shipment_statuses', []), true);
    }

    private function validateWorkflowStatusContract(?string $code, ?int $sequence, ?bool $isFinal): void
    {
        if ($code === null) {
            return;
        }

        $workflowCodes = config('expedition.shipment_statuses', []);
        $workflowFinalCodes = config('expedition.shipment_status_flow.final_statuses', ['delivered', 'cancelled', 'returned']);

        if (! in_array($code, $workflowCodes, true)) {
            throw ValidationException::withMessages([
                'code' => 'Hanya status yang sudah didefinisikan dalam workflow yang boleh digunakan.',
            ]);
        }

        $expectedSequence = array_search($code, $workflowCodes, true);
        $expectedSequence = $expectedSequence === false ? null : $expectedSequence + 1;
        $expectedIsFinal = in_array($code, $workflowFinalCodes, true);

        if ($sequence !== null && $expectedSequence !== null && $sequence !== $expectedSequence) {
            throw ValidationException::withMessages([
                'sequence' => 'Urutan status harus mengikuti workflow bisnis yang sudah ditentukan.',
            ]);
        }

        if ($isFinal !== null && $isFinal !== $expectedIsFinal) {
            throw ValidationException::withMessages([
                'is_final' => 'Penanda final harus sesuai dengan workflow status yang telah ditetapkan.',
            ]);
        }
    }
}
