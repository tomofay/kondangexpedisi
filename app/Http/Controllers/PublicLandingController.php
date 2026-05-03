<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\LandingPageContent;
use App\Models\RateCard;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PublicLandingController extends Controller
{
    /**
     * Render the landing page.
     */
    public function __invoke(Request $request): View
    {
        $contents = LandingPageContent::query()
            ->where('is_active', true)
            ->orderBy('section')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $serviceTypes = RateCard::query()
            ->where('is_active', true)
            ->select('service_type')
            ->distinct()
            ->orderBy('service_type')
            ->pluck('service_type');

        return view('public.landing', [
            'hero' => $this->firstOrDefault($contents, 'hero', [
                'title' => 'Kondang Ekspedisi',
                'subtitle' => 'Solusi Logistik Cerdas & Terpercaya.',
                'content' => 'Layanan pengiriman paket dengan keamanan maksimal dan sistem pelacakan paling presisi.',
            ]),
            'branches' => $branches,
            'serviceTypes' => $serviceTypes,
            'features' => $this->collectionOrDefault($contents, 'feature', []),
            'testimonials' => $this->collectionOrDefault($contents, 'testimonial', []),
            'faqs' => $this->collectionOrDefault($contents, 'faq', []),
            'statistics' => $this->collectionOrDefault($contents, 'statistic', []),
        ]);
    }

    /**
     * AJAX: Track shipment.
     */
    public function track(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tracking_number' => ['required', 'string', 'max:40'],
            'recipient_phone' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $shipment = Shipment::query()
            ->where('tracking_number', $request->tracking_number)
            ->with(['status', 'trackings.status'])
            ->first();

        if (! $shipment || $this->normalizePhone($request->recipient_phone) !== $this->normalizePhone($shipment->recipient_phone)) {
            return response()->json(['error' => 'Data tidak ditemukan. Cek kembali nomor resi dan HP Anda.'], 404);
        }

        return response()->json([
            'tracking_number' => $shipment->tracking_number,
            'status_name' => $shipment->status->name,
            'service_type' => strtoupper($shipment->service_type),
            'trackings' => $shipment->trackings->map(fn ($t) => [
                'status' => $t->status->name,
                'location' => $t->location,
                'time' => $t->event_at->format('d M, H:i'),
                'notes' => $t->notes,
            ]),
        ]);
    }

    /**
     * AJAX: Calculate shipping fee.
     */
    public function quote(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'origin_branch_id' => ['required', 'exists:branches,id'],
            'destination_branch_id' => ['required', 'exists:branches,id'],
            'weight_kg' => ['required', 'numeric', 'min:0.1'],
            'service_type' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $serviceType = $request->service_type ?: 'regular';
        $weight = (float) $request->weight_kg;

        $rateCard = RateCard::where('origin_branch_id', $request->origin_branch_id)
            ->where('destination_branch_id', $request->destination_branch_id)
            ->where('service_type', $serviceType)
            ->where('is_active', true)
            ->where('min_weight_kg', '<=', $weight)
            ->where(function ($q) use ($weight) {
                $q->whereNull('max_weight_kg')->orWhere('max_weight_kg', '>=', $weight);
            })
            ->orderByDesc('min_weight_kg')
            ->first();

        if (!$rateCard) {
            // Fallback if no rate card found
            $basePrice = 15000;
            $perKg = 7000;
            $total = $basePrice + ($perKg * $weight);
        } else {
            $total = ((float) $rateCard->base_price) + ((float) $rateCard->per_kg_price * $weight);
        }

        return response()->json([
            'total' => (int) round($total + 2500), // Incl admin fee
            'service_type' => strtoupper($serviceType),
            'weight' => $weight,
        ]);
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (str_starts_with($digits, '62')) $digits = '0' . substr($digits, 2);
        return $digits;
    }

    private function firstOrDefault(Collection $contents, string $section, array $default): array
    {
        $item = $contents->get($section)?->first();
        if (! $item) return $default;
        return [
            'title' => $item->title ?: ($default['title'] ?? null),
            'subtitle' => $item->subtitle ?: ($default['subtitle'] ?? null),
            'content' => $item->content ?: ($default['content'] ?? null),
        ];
    }

    private function collectionOrDefault(Collection $contents, string $section, array $default): Collection
    {
        $items = $contents->get($section);
        return ($items && $items->isNotEmpty()) ? $items : collect($default);
    }
}
