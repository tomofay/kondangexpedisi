<?php

namespace App\Http\Controllers;

use App\Models\LandingPageContent;
use App\Models\Branch;
use App\Models\RateCard;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PublicLandingController extends Controller
{
    public function __invoke(Request $request): View
    {
        $contents = LandingPageContent::query()
            ->where('is_active', true)
            ->orderBy('section')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        $branches = Branch::query()
            ->with('zone')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $serviceTypes = RateCard::query()
            ->where('is_active', true)
            ->select('service_type')
            ->distinct()
            ->orderBy('service_type')
            ->pluck('service_type');

        $trackingNumber = trim((string) $request->query('tracking_number', ''));
        $trackingRecipientPhone = trim((string) $request->query('recipient_phone', ''));
        $trackingResult = null;
        $trackingError = null;
        $maskedRecipientPhone = null;

        if ($request->has('tracking_submit')) {
            $trackingValidator = Validator::make([
                'tracking_number' => $trackingNumber,
                'recipient_phone' => $trackingRecipientPhone,
            ], [
                'tracking_number' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9\-]+$/'],
                'recipient_phone' => ['required', 'string', 'min:8', 'max:20', 'regex:/^[0-9+\-\s().]+$/'],
            ], [
                'tracking_number.required' => 'Nomor resi wajib diisi.',
                'tracking_number.regex' => 'Format nomor resi tidak valid.',
                'recipient_phone.required' => 'Nomor HP penerima wajib diisi.',
                'recipient_phone.regex' => 'Format nomor HP penerima tidak valid.',
            ]);

            if ($trackingValidator->fails()) {
                $trackingError = $trackingValidator->errors()->first();
            } else {
                $shipment = Shipment::query()
                    ->where('tracking_number', $trackingNumber)
                    ->with(['branch', 'courier', 'status', 'trackings.status'])
                    ->first();

                $inputPhone = $this->normalizePhone($trackingRecipientPhone);
                $shipmentPhone = $this->normalizePhone($shipment?->recipient_phone);

                if (! $shipment || $inputPhone === '' || $shipmentPhone === '' || $inputPhone !== $shipmentPhone) {
                    // Keep error generic to reduce account/data enumeration risk.
                    $trackingError = 'Data tidak cocok. Pastikan nomor resi dan nomor HP penerima benar.';
                } else {
                    $shipment->setRelation(
                        'trackings',
                        $shipment->trackings->sortBy('event_at')->values()
                    );
                    $trackingResult = $shipment;
                    $maskedRecipientPhone = $this->maskPhone($shipment->recipient_phone);
                }
            }
        }

        $quoteInput = [
            'origin_branch_id' => $request->query('origin_branch_id'),
            'destination_branch_id' => $request->query('destination_branch_id'),
            'service_type' => $request->query('service_type', 'regular'),
            'weight_kg' => $request->query('weight_kg'),
            'length_cm' => $request->query('length_cm'),
            'width_cm' => $request->query('width_cm'),
            'height_cm' => $request->query('height_cm'),
            'with_insurance' => (bool) $request->query('with_insurance', false),
        ];

        $quoteResult = null;
        $quoteError = null;

        if ($request->has('quote_submit')) {
            $validator = Validator::make($quoteInput, [
                'origin_branch_id' => ['required', 'integer', 'exists:branches,id'],
                'destination_branch_id' => ['required', 'integer', 'exists:branches,id'],
                'service_type' => ['required', 'string', 'max:30'],
                'weight_kg' => ['required', 'numeric', 'min:0.1', 'max:1000'],
                'length_cm' => ['nullable', 'numeric', 'min:1', 'max:500'],
                'width_cm' => ['nullable', 'numeric', 'min:1', 'max:500'],
                'height_cm' => ['nullable', 'numeric', 'min:1', 'max:500'],
                'with_insurance' => ['boolean'],
            ]);

            if ($validator->fails()) {
                $quoteError = $validator->errors()->first();
            } else {
                $originBranch = $branches->firstWhere('id', (int) $quoteInput['origin_branch_id']);
                $destinationBranch = $branches->firstWhere('id', (int) $quoteInput['destination_branch_id']);

                if (! $originBranch || ! $destinationBranch) {
                    $quoteError = 'Cabang asal atau cabang tujuan tidak ditemukan.';
                } elseif (! $originBranch->zone || ! $destinationBranch->zone) {
                    $quoteError = 'Cabang asal/tujuan belum memiliki zona aktif. Hubungi admin untuk melengkapi data cabang.';
                } else {
                    $originZone = $originBranch->zone;
                    $destinationZone = $destinationBranch->zone;
                    $weight = (float) $quoteInput['weight_kg'];
                    $length = (float) ($quoteInput['length_cm'] ?: 0);
                    $width = (float) ($quoteInput['width_cm'] ?: 0);
                    $height = (float) ($quoteInput['height_cm'] ?: 0);
                    $volumetricWeight = ($length > 0 && $width > 0 && $height > 0)
                        ? round(($length * $width * $height) / 5000, 2)
                        : 0;
                    $billableWeight = max($weight, $volumetricWeight, 1);

                    $baseQuery = RateCard::query()
                        ->where('is_active', true)
                        ->where('origin_zone_id', $originZone->id)
                        ->where('destination_zone_id', $destinationZone->id)
                        ->where('service_type', (string) $quoteInput['service_type']);

                    $rateCard = (clone $baseQuery)
                        ->where('min_weight_kg', '<=', $billableWeight)
                        ->where(function ($query) use ($billableWeight) {
                            $query->whereNull('max_weight_kg')
                                ->orWhere('max_weight_kg', '>=', $billableWeight);
                        })
                        ->orderByDesc('min_weight_kg')
                        ->first();

                    $fallbackMessage = null;
                    $fallbackReason = null;

                    if (! $rateCard) {
                        $rateCard = (clone $baseQuery)->orderBy('min_weight_kg')->first();

                        if ($rateCard) {
                            $fallbackMessage = 'Tarif persis untuk berat ini belum ada. Sistem memakai tarif terdekat pada zona dan layanan yang sama.';
                            $fallbackReason = 'Tidak ada rate card yang mencakup berat tagih saat ini untuk layanan ini, sehingga sistem memakai tarif terdekat pada layanan yang sama.';
                        }
                    }

                    if (! $rateCard) {
                        $rateCard = RateCard::query()
                            ->where('is_active', true)
                            ->where('origin_zone_id', $originZone->id)
                            ->where('destination_zone_id', $destinationZone->id)
                            ->orderBy('service_type')
                            ->orderBy('min_weight_kg')
                            ->first();

                        if ($rateCard) {
                            $fallbackMessage = 'Layanan ini belum punya rate card aktif. Sistem memakai layanan tarif lain pada zona yang sama.';
                            $fallbackReason = 'Tidak ada rate card aktif untuk layanan yang dipilih pada rute ini, sehingga sistem memakai layanan lain yang tersedia di rute yang sama.';
                        }
                    }

                    if (! $rateCard) {
                        $quoteError = 'Data tarif untuk rute zona asal ke zona tujuan ini belum tersedia. Hubungi admin untuk melengkapi rate card.';
                    } else {
                        $zoneMultiplier = (float) $destinationZone->multiplier;
                        $subtotal = (
                            ((float) $rateCard->base_price * $zoneMultiplier) +
                            ((float) $rateCard->per_kg_price * $billableWeight)
                        );

                        $insurance = $quoteInput['with_insurance']
                            ? (float) $rateCard->insurance_fee
                            : 0;
                        $adminFee = 2500;

                        $quoteResult = [
                            'origin_branch_name' => $originBranch->name,
                            'destination_branch_name' => $destinationBranch->name,
                            'origin_zone_name' => $originZone->name,
                            'destination_zone_name' => $destinationZone->name,
                            'service_type' => $rateCard->service_type,
                            'rate_card_min_weight_kg' => (float) $rateCard->min_weight_kg,
                            'rate_card_max_weight_kg' => $rateCard->max_weight_kg !== null ? (float) $rateCard->max_weight_kg : null,
                            'actual_weight_kg' => $weight,
                            'volumetric_weight_kg' => $volumetricWeight,
                            'billable_weight_kg' => round($billableWeight, 2),
                            'subtotal' => (int) round($subtotal),
                            'insurance' => (int) round($insurance),
                            'admin_fee' => $adminFee,
                            'total' => (int) round($subtotal + $insurance + $adminFee),
                            'fallback_message' => $fallbackMessage,
                            'fallback_reason' => $fallbackReason,
                        ];
                    }
                }
            }
        }

        return view('public.landing', [
            'hero' => $this->firstOrDefault($contents, 'hero', [
                'title' => 'Kondang Ekspedisi',
                'subtitle' => 'Layanan kirim barang cepat, aman, dan terpantau real-time.',
                'content' => 'Didukung armada aktif, tracking transparan, dan pembayaran online Midtrans Sandbox.',
                'cta_label' => 'Lacak Resi',
                'cta_url' => '#tracking',
            ]),
            'features' => $this->collectionOrDefault($contents, 'feature', [
                ['title' => 'Pickup Harian', 'content' => 'Tim kurir aktif menjemput paket setiap hari kerja.'],
                ['title' => 'Tracking Akurat', 'content' => 'Timeline status pengiriman ditampilkan real-time.'],
                ['title' => 'Pembayaran Digital', 'content' => 'Dukungan pembayaran online melalui Midtrans.'],
            ]),
            'testimonials' => $this->collectionOrDefault($contents, 'testimonial', [
                ['title' => 'UMKM Fashion', 'content' => 'Paket sampai cepat dan update statusnya jelas.'],
                ['title' => 'Toko Elektronik', 'content' => 'Dashboard operasional membantu pantau order harian.'],
            ]),
            'faqs' => $this->collectionOrDefault($contents, 'faq', [
                ['title' => 'Bagaimana cara cek resi?', 'content' => 'Masukkan nomor resi pada form tracking publik di halaman ini.'],
                ['title' => 'Apakah bisa bayar online?', 'content' => 'Bisa, pembayaran terhubung melalui Midtrans Sandbox.'],
            ]),
            'cta' => $this->firstOrDefault($contents, 'cta', [
                'title' => 'Kirim lebih mudah bersama Kondang Ekspedisi',
                'subtitle' => 'Mulai order dan pantau progres paket dari satu dashboard.',
                'cta_label' => 'Mulai Sekarang',
                'cta_url' => '/login',
            ]),
            'contacts' => $this->collectionOrDefault($contents, 'contact', [
                ['title' => 'CS Pusat', 'content' => '0812-0000-1234'],
                ['title' => 'Email', 'content' => 'halo@kondangekspedisi.test'],
            ]),
            'statistics' => $this->collectionOrDefault($contents, 'statistic', [
                ['title' => 'Shipment Terselesaikan', 'content' => '1500+'],
                ['title' => 'Kota Terlayani', 'content' => '80+'],
                ['title' => 'Akurasi Tracking', 'content' => '99.2%'],
            ]),
            'branches' => $branches,
            'serviceTypes' => $serviceTypes,
            'quoteInput' => $quoteInput,
            'quoteResult' => $quoteResult,
            'quoteError' => $quoteError,
            'trackingNumber' => $trackingNumber,
            'trackingRecipientPhone' => $trackingRecipientPhone,
            'trackingResult' => $trackingResult,
            'maskedRecipientPhone' => $maskedRecipientPhone,
            'trackingError' => $trackingError,
        ]);
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (! $digits) {
            return '';
        }

        if (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    private function maskPhone(?string $phone): string
    {
        $normalized = $this->normalizePhone($phone);

        if ($normalized === '') {
            return '-';
        }

        $length = strlen($normalized);

        if ($length <= 5) {
            return str_repeat('*', $length);
        }

        return substr($normalized, 0, 4).str_repeat('*', max($length - 7, 2)).substr($normalized, -3);
    }

    private function firstOrDefault(Collection $contents, string $section, array $default): array
    {
        $item = $contents->get($section)?->first();

        if (! $item) {
            return $default;
        }

        return [
            'title' => $item->title ?: ($default['title'] ?? null),
            'subtitle' => $item->subtitle ?: ($default['subtitle'] ?? null),
            'content' => $item->content ?: ($default['content'] ?? null),
            'cta_label' => $item->cta_label ?: ($default['cta_label'] ?? null),
            'cta_url' => $item->cta_url ?: ($default['cta_url'] ?? null),
            'image_url' => $item->image_url,
            'metadata' => $item->metadata,
        ];
    }

    private function collectionOrDefault(Collection $contents, string $section, array $default): Collection
    {
        $items = $contents->get($section);

        if (! $items || $items->isEmpty()) {
            return collect($default);
        }

        return $items->map(fn (LandingPageContent $item) => [
            'title' => $item->title,
            'subtitle' => $item->subtitle,
            'content' => $item->content,
            'cta_label' => $item->cta_label,
            'cta_url' => $item->cta_url,
            'image_url' => $item->image_url,
            'metadata' => $item->metadata,
        ]);
    }
}