<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\LandingPageContent;
use App\Models\Payment;
use App\Models\RateCard;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTrashController extends Controller
{
    private const RESOURCE_MAP = [
        'users' => [User::class, 'name'],
        'branches' => [Branch::class, 'name'],
        'zones' => [Zone::class, 'name'],
        'rate_cards' => [RateCard::class, 'service_type'],
        'vehicles' => [Vehicle::class, 'name'],
        'shipments' => [Shipment::class, 'tracking_number'],
        'payments' => [Payment::class, 'method'],
        'landing_contents' => [LandingPageContent::class, 'title'],
    ];

    public function index(Request $request): JsonResponse
    {
        $type = $request->input('type');
        $summary = [];
        $recent = collect();

        foreach ($this->resourceMap($type) as $resourceType => [$class, $labelColumn]) {
            $trashedQuery = $class::query()->onlyTrashed();
            $summary[$resourceType] = $trashedQuery->count();

            $recent = $recent->concat(
                $trashedQuery
                    ->latest('deleted_at')
                    ->limit(5)
                    ->get()
                    ->map(function ($item) use ($resourceType, $labelColumn) {
                        return [
                            'type' => $resourceType,
                            'id' => $item->id,
                            'label' => $item->{$labelColumn} ?? ($item->tracking_number ?? ('#'.$item->id)),
                            'deleted_at' => optional($item->deleted_at)->format('Y-m-d H:i:s'),
                        ];
                    })
            );
        }

        return response()->json([
            'summary' => $summary,
            'recent' => $recent->sortByDesc('deleted_at')->take(20)->values(),
            'total' => array_sum($summary),
        ]);
    }

    public function restore(Request $request, string $type, int $id): JsonResponse
    {
        [$class] = $this->resolveResource($type);

        $model = $class::query()->withTrashed()->findOrFail($id);
        $model->restore();

        return response()->json([
            'message' => 'Data restored.',
            'data' => $model->fresh(),
        ]);
    }

    private function resourceMap(?string $type = null): array
    {
        if ($type && isset(self::RESOURCE_MAP[$type])) {
            return [$type => self::RESOURCE_MAP[$type]];
        }

        return self::RESOURCE_MAP;
    }

    private function resolveResource(string $type): array
    {
        abort_unless(isset(self::RESOURCE_MAP[$type]), 404, 'Tipe data tidak dikenali.');

        return self::RESOURCE_MAP[$type];
    }
}
