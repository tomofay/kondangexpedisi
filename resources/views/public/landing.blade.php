<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kondang Ekspedisi</title>
    <meta name="description" content="Kondang Ekspedisi - layanan pengiriman modern, cepat, aman, dan transparan.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        :root {
            --k-white: #ffffff;
            --k-paper: #f5f9ff;
            --k-line: #d9e7ff;
            --k-blue-900: #0b2b67;
            --k-blue-800: #17479c;
            --k-blue-700: #2769d8;
            --k-blue-500: #4f9bff;
            --k-text: #14284b;
            --k-muted: #60759a;
            --k-shadow: 0 16px 40px rgba(22, 68, 150, 0.14);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--k-text);
            background:
                radial-gradient(860px 360px at 6% -6%, #dbe9ff 0%, transparent 70%),
                radial-gradient(900px 460px at 100% 0%, #e9f2ff 0%, transparent 62%),
                linear-gradient(180deg, #f8fbff 0%, #ffffff 54%);
        }

        h1, h2, h3, h4, .brand-font {
            font-family: 'Sora', sans-serif;
            letter-spacing: -0.02em;
        }

        .navbar-kondang {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.88);
            border-bottom: 1px solid var(--k-line);
        }

        .brand-chip {
            border: 1px solid #c6ddff;
            color: var(--k-blue-800);
            background: #edf4ff;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.11em;
            text-transform: uppercase;
            padding: 0.35rem 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .brand-chip::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: var(--k-blue-500);
            box-shadow: 0 0 10px rgba(79, 155, 255, 0.9);
        }

        .hero-section {
            position: relative;
            overflow: hidden;
            padding-top: 5.5rem;
            padding-bottom: 4.5rem;
        }

        .hero-card {
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(255, 255, 255, 0.95);
            box-shadow: var(--k-shadow);
            border-radius: 28px;
            backdrop-filter: blur(10px);
        }

        .floating-orb {
            position: absolute;
            border-radius: 999px;
            border: 1px solid rgba(39, 105, 216, 0.2);
            animation: orbit 19s linear infinite;
        }

        .floating-orb::before {
            content: '';
            position: absolute;
            top: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: var(--k-blue-500);
            box-shadow: 0 0 14px rgba(79, 155, 255, 0.85);
        }

        .metric-box,
        .surface-box {
            border: 1px solid var(--k-line);
            border-radius: 18px;
            background: var(--k-white);
            box-shadow: 0 8px 24px rgba(11, 43, 103, 0.08);
        }

        .metric-box {
            background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
        }

        .section-title {
            color: var(--k-blue-900);
            font-size: clamp(1.6rem, 2.8vw, 2.1rem);
            margin-bottom: 0.4rem;
        }

        .section-subtitle {
            color: var(--k-muted);
            max-width: 680px;
        }

        .feature-card {
            border: 1px solid var(--k-line);
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(16, 73, 165, 0.14);
        }

        .section-pad {
            padding-top: 3.25rem;
            padding-bottom: 3.25rem;
        }

        .calculator-wrap {
            background: linear-gradient(145deg, var(--k-blue-900) 0%, var(--k-blue-800) 40%, #2d74e2 100%);
            color: #eef5ff;
            border-radius: 24px;
            box-shadow: 0 20px 42px rgba(15, 58, 134, 0.34);
        }

        .calculator-wrap .form-control,
        .calculator-wrap .form-select {
            border-radius: 12px;
            border: 1px solid #a9c8ff;
            min-height: 44px;
        }

        .calculator-wrap .form-check-input:checked {
            background-color: #3f8fff;
            border-color: #3f8fff;
        }

        .timeline-item {
            border: 1px solid var(--k-line);
            border-radius: 14px;
            background: #ffffff;
            padding: 1rem;
            position: relative;
            margin-bottom: 0.75rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 1.2rem;
            width: 12px;
            height: 12px;
            border-radius: 999px;
            background: var(--k-blue-500);
            box-shadow: 0 0 0 4px #eaf3ff;
        }

        .faq-item {
            border: 1px solid var(--k-line);
            border-radius: 14px;
            overflow: hidden;
            background: #ffffff;
        }

        .faq-item .accordion-button {
            font-weight: 700;
            color: var(--k-blue-900);
        }

        .faq-item .accordion-button:not(.collapsed) {
            color: var(--k-blue-900);
            background: #eff5ff;
        }

        .testimonial-card {
            border: 1px solid var(--k-line);
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 10px 20px rgba(11, 43, 103, 0.06);
            height: 100%;
        }

        .contact-card {
            border: 1px solid var(--k-line);
            border-radius: 14px;
            background: #ffffff;
        }

        .reveal {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        .reveal.show {
            opacity: 1;
            transform: translateY(0);
        }

        .btn-kondang-primary {
            background: linear-gradient(90deg, #1e63cf 0%, #448ff9 100%);
            border: none;
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(24, 87, 192, 0.3);
        }

        .btn-kondang-primary:hover {
            color: #ffffff;
            filter: brightness(0.96);
        }

        .btn-kondang-outline {
            border: 1px solid #9fc3ff;
            color: var(--k-blue-800);
            background: #ffffff;
        }

        .footer-wrap {
            border-top: 1px solid var(--k-line);
            background: #ffffff;
            color: #6e84a8;
        }

        @keyframes orbit {
            from { transform: rotate(0); }
            to { transform: rotate(360deg); }
        }

        @media (max-width: 991.98px) {
            .hero-section {
                padding-top: 4.5rem;
                padding-bottom: 2.8rem;
            }

            .section-pad {
                padding-top: 2.35rem;
                padding-bottom: 2.35rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-kondang fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold brand-font text-primary-emphasis" href="#">Kondang Ekspedisi</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="#layanan">Layanan</a></li>
                    <li class="nav-item"><a class="nav-link" href="#kalkulator">Kalkulator</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tracking">Tracking</a></li>
                    <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                    <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a href="{{ route('login') }}" class="btn btn-sm btn-kondang-outline rounded-pill px-3">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <section class="hero-section">
            <div class="container position-relative">
                <div class="floating-orb" style="width: 240px; height: 240px; right: -30px; top: -10px;"></div>
                <div class="floating-orb" style="width: 330px; height: 330px; right: 70px; top: 30px; animation-duration: 26s;"></div>

                <div class="row g-4 align-items-center">
                    <div class="col-lg-6 reveal">
                        <span class="brand-chip mb-3">Ekspedisi Modern</span>
                        <h1 class="display-5 fw-bold text-primary-emphasis mb-3">{{ $hero['title'] ?? 'Kondang Ekspedisi' }}</h1>
                        <p class="lead text-secondary mb-2">{{ $hero['subtitle'] ?? 'Layanan kirim barang cepat, aman, dan terpantau real-time.' }}</p>
                        <p class="text-muted mb-4">{{ $hero['content'] ?? 'Didukung armada aktif, tracking transparan, dan pembayaran online Midtrans Sandbox.' }}</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ $hero['cta_url'] ?? '#tracking' }}" class="btn btn-kondang-primary rounded-pill px-4 py-2 fw-semibold">{{ $hero['cta_label'] ?? 'Lacak Resi' }}</a>
                            <a href="#kalkulator" class="btn btn-kondang-outline rounded-pill px-4 py-2 fw-semibold">Hitung Ongkir</a>
                        </div>
                    </div>

                    <div class="col-lg-6 reveal">
                        <div class="hero-card p-4 p-lg-5">
                            <h2 class="h4 text-primary-emphasis mb-4">Ringkasan Layanan Kondang</h2>
                            <div class="row g-3">
                                @foreach ($statistics as $stat)
                                    <div class="col-12">
                                        <div class="metric-box p-3 p-md-4">
                                            <div class="h3 fw-bold text-primary-emphasis mb-1">{{ $stat['content'] ?? '-' }}</div>
                                            <div class="small text-uppercase fw-semibold text-secondary">{{ $stat['title'] ?? 'Statistik' }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="layanan" class="section-pad">
            <div class="container">
                <div class="mb-4 reveal">
                    <h2 class="section-title">Layanan Pengiriman</h2>
                    <p class="section-subtitle mb-0">Seluruh blok layanan ditarik dari data landing_page_contents agar mudah dikontrol dari backoffice.</p>
                </div>

                <div class="row g-3 g-lg-4">
                    @foreach ($features as $feature)
                        <div class="col-md-6 col-xl-4 reveal">
                            <article class="feature-card p-4">
                                <h3 class="h5 text-primary-emphasis mb-2">{{ $feature['title'] ?? 'Layanan' }}</h3>
                                @if (!empty($feature['subtitle']))
                                    <div class="fw-semibold text-primary mb-2">{{ $feature['subtitle'] }}</div>
                                @endif
                                <p class="text-muted mb-0">{{ $feature['content'] ?? '-' }}</p>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="kalkulator" class="section-pad pt-0">
            <div class="container">
                <div class="calculator-wrap p-4 p-md-5 reveal">
                    <div class="row g-4 align-items-start">
                        <div class="col-lg-5">
                            <h2 class="h3 mb-2">Kalkulator Ongkir Publik</h2>
                            <p class="mb-0 text-white-50">Perhitungan berbasis cabang asal ke cabang tujuan (mengikuti zona masing-masing cabang), termasuk volumetrik dan fallback tarif jika data belum lengkap.</p>
                        </div>

                        <div class="col-lg-7">
                            <form method="GET" action="{{ route('landing') }}#kalkulator" class="row g-3">
                                <input type="hidden" name="quote_submit" value="1">

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Cabang Asal</label>
                                    <select name="origin_branch_id" class="form-select" required>
                                        <option value="">Pilih cabang asal</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}" @selected((string) $quoteInput['origin_branch_id'] === (string) $branch->id)>
                                                {{ $branch->name }} ({{ $branch->zone?->name ?? 'Tanpa Zona' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Cabang Tujuan</label>
                                    <select name="destination_branch_id" class="form-select" required>
                                        <option value="">Pilih cabang tujuan</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}" @selected((string) $quoteInput['destination_branch_id'] === (string) $branch->id)>
                                                {{ $branch->name }} ({{ $branch->zone?->name ?? 'Tanpa Zona' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Jenis Layanan</label>
                                    <select name="service_type" class="form-select" required>
                                        @foreach ($serviceTypes as $serviceType)
                                            <option value="{{ $serviceType }}" @selected($quoteInput['service_type'] === $serviceType)>{{ strtoupper($serviceType) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Berat Aktual (kg)</label>
                                    <input type="number" step="0.01" min="0.1" name="weight_kg" value="{{ $quoteInput['weight_kg'] }}" class="form-control" required>
                                </div>

                                <div class="col-md-2 col-4">
                                    <label class="form-label fw-semibold">P</label>
                                    <input type="number" step="0.01" min="1" name="length_cm" value="{{ $quoteInput['length_cm'] }}" class="form-control">
                                </div>
                                <div class="col-md-2 col-4">
                                    <label class="form-label fw-semibold">L</label>
                                    <input type="number" step="0.01" min="1" name="width_cm" value="{{ $quoteInput['width_cm'] }}" class="form-control">
                                </div>
                                <div class="col-md-2 col-4">
                                    <label class="form-label fw-semibold">T</label>
                                    <input type="number" step="0.01" min="1" name="height_cm" value="{{ $quoteInput['height_cm'] }}" class="form-control">
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="with_insurance" value="1" id="withInsurance" @checked($quoteInput['with_insurance'])>
                                        <label class="form-check-label" for="withInsurance">Tambahkan asuransi sesuai rate card</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-light rounded-pill px-4 fw-semibold">Hitung Ongkir</button>
                                </div>
                            </form>

                            @if ($quoteError)
                                <div class="alert alert-danger mt-3 mb-0">{{ $quoteError }}</div>
                            @endif

                            @if ($quoteResult)
                                <div class="surface-box mt-3 p-3 p-md-4 text-dark">
                                    <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
                                        <div>
                                            <div class="text-uppercase fw-semibold small text-primary">Estimasi Kondang Ekspedisi</div>
                                            <div class="h4 fw-bold text-primary-emphasis mb-0">Rp {{ number_format($quoteResult['total'], 0, ',', '.') }}</div>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                                            <span
                                                class="badge rounded-pill {{ $quoteResult['fallback_message'] ? 'text-bg-warning' : 'text-bg-success' }}"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="{{ $quoteResult['fallback_reason'] ?? 'Tarif ditemukan tepat pada rentang berat tagih untuk layanan yang dipilih.' }}"
                                            >
                                                {{ $quoteResult['fallback_message'] ? 'Tarif fallback terpakai' : 'Tarif sesuai rentang berat' }}
                                            </span>
                                            <div class="small text-secondary">{{ $quoteResult['origin_zone_name'] }} → {{ $quoteResult['destination_zone_name'] }} | {{ strtoupper($quoteResult['service_type']) }}</div>
                                        </div>
                                    </div>
                                    <div class="small text-secondary mt-2">
                                        Rute cabang: {{ $quoteResult['origin_branch_name'] }} → {{ $quoteResult['destination_branch_name'] }}
                                    </div>
                                    <div class="row g-2 mt-2">
                                        <div class="col-sm-6 col-xl-3"><div class="border rounded-3 p-2"><div class="small text-secondary">Berat aktual</div><strong>{{ number_format($quoteResult['actual_weight_kg'], 2) }} kg</strong></div></div>
                                        <div class="col-sm-6 col-xl-3"><div class="border rounded-3 p-2"><div class="small text-secondary">Berat volumetrik</div><strong>{{ number_format($quoteResult['volumetric_weight_kg'], 2) }} kg</strong></div></div>
                                        <div class="col-sm-6 col-xl-3"><div class="border rounded-3 p-2"><div class="small text-secondary">Berat tagih</div><strong>{{ number_format($quoteResult['billable_weight_kg'], 2) }} kg</strong></div></div>
                                        <div class="col-sm-6 col-xl-3"><div class="border rounded-3 p-2"><div class="small text-secondary">Subtotal</div><strong>Rp {{ number_format($quoteResult['subtotal'], 0, ',', '.') }}</strong></div></div>
                                    </div>
                                    <div class="small text-secondary mt-2">Asuransi Rp {{ number_format($quoteResult['insurance'], 0, ',', '.') }} | Admin Rp {{ number_format($quoteResult['admin_fee'], 0, ',', '.') }}</div>
                                    <div class="small text-secondary mt-1">
                                        Rentang rate card terpakai:
                                        {{ number_format($quoteResult['rate_card_min_weight_kg'], 2, '.', '') }}-
                                        {{ $quoteResult['rate_card_max_weight_kg'] !== null ? number_format($quoteResult['rate_card_max_weight_kg'], 2, '.', '') : 'tanpa batas' }} kg
                                    </div>
                                    @if ($quoteResult['fallback_message'])
                                        <div class="alert alert-warning py-2 px-3 mt-3 mb-0 small">{{ $quoteResult['fallback_message'] }}</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="tracking" class="section-pad pt-0">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-4 reveal">
                        <div class="surface-box p-4 h-100">
                            <h2 class="h4 text-primary-emphasis mb-2">Tracking Publik</h2>
                            <p class="text-muted small">Masukkan nomor resi dan nomor HP penerima untuk melihat status paket dengan lebih aman.</p>

                            <form method="GET" action="{{ route('landing') }}#tracking" class="mt-3">
                                <input type="hidden" name="tracking_submit" value="1">
                                <label class="form-label fw-semibold">Nomor Resi</label>
                                <input type="text" name="tracking_number" value="{{ $trackingNumber }}" class="form-control mb-3" placeholder="Contoh: SXP-20260412-001" required>
                                <label class="form-label fw-semibold">No. HP Penerima</label>
                                <input type="text" name="recipient_phone" value="{{ $trackingRecipientPhone ?? '' }}" class="form-control mb-3" placeholder="Contoh: 081220000111" required>
                                <button class="btn btn-kondang-primary rounded-pill w-100 fw-semibold" type="submit">Lacak Paket</button>
                            </form>

                            @if ($trackingError)
                                <div class="alert alert-danger mt-3 mb-0">{{ $trackingError }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-8 reveal">
                        <div class="surface-box p-4">
                            @if ($trackingResult)
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div>
                                        <div class="small fw-semibold text-uppercase text-primary">Resi {{ $trackingResult->tracking_number }}</div>
                                        <h3 class="h5 mb-0 text-primary-emphasis">Status {{ $trackingResult->status?->name ?? '-' }}</h3>
                                    </div>
                                    <span class="badge text-bg-primary rounded-pill px-3 py-2">{{ strtoupper((string) $trackingResult->service_type) }}</span>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-md-4"><div class="border rounded-3 p-2"><div class="small text-secondary">Penerima</div><strong>{{ $trackingResult->recipient_name }}</strong></div></div>
                                    <div class="col-md-4"><div class="border rounded-3 p-2"><div class="small text-secondary">No. HP Terverifikasi</div><strong>{{ $maskedRecipientPhone ?? '-' }}</strong></div></div>
                                    <div class="col-md-4"><div class="border rounded-3 p-2"><div class="small text-secondary">Cabang</div><strong>{{ $trackingResult->branch?->name ?? '-' }}</strong></div></div>
                                    <div class="col-md-12"><div class="border rounded-3 p-2"><div class="small text-secondary">Kurir</div><strong>{{ $trackingResult->courier?->name ?? '-' }}</strong></div></div>
                                </div>

                                <div>
                                    @forelse ($trackingResult->trackings as $event)
                                        <article class="timeline-item ms-2">
                                            <div class="fw-bold text-primary-emphasis">{{ $event->status?->name ?? 'Update Status' }}</div>
                                            <div class="small text-secondary">{{ optional($event->event_at)->format('d M Y H:i') }} | {{ $event->location ?? '-' }}</div>
                                            @if ($event->notes)
                                                <div class="small text-muted mt-1">{{ $event->notes }}</div>
                                            @endif
                                        </article>
                                    @empty
                                        <div class="alert alert-light border mb-0">Belum ada event tracking untuk resi ini.</div>
                                    @endforelse
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="h6 text-secondary mb-1">Timeline tracking akan tampil di sini</div>
                                    <p class="text-muted small mb-0">Masukkan nomor resi dan no. HP penerima pada form di sebelah kiri.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-pad pt-0">
            <div class="container">
                <div class="row g-3">
                    @foreach ($testimonials as $testimonial)
                        <div class="col-md-6 reveal">
                            <blockquote class="testimonial-card p-4 mb-0">
                                <p class="text-muted mb-2">"{{ $testimonial['content'] ?? '-' }}"</p>
                                <footer class="fw-bold text-primary-emphasis">{{ $testimonial['title'] ?? 'Pelanggan' }}</footer>
                            </blockquote>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="faq" class="section-pad pt-0">
            <div class="container">
                <div class="mb-4 reveal">
                    <h2 class="section-title">Pertanyaan Umum</h2>
                    <p class="section-subtitle mb-0">FAQ dinamis dari database untuk memudahkan update informasi operasional.</p>
                </div>

                <div class="accordion reveal" id="faqAccordion">
                    @foreach ($faqs as $faqIndex => $faq)
                        <div class="accordion-item faq-item mb-2">
                            <h3 class="accordion-header" id="faqHeading{{ $faqIndex }}">
                                <button class="accordion-button {{ $faqIndex > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse{{ $faqIndex }}" aria-expanded="{{ $faqIndex === 0 ? 'true' : 'false' }}" aria-controls="faqCollapse{{ $faqIndex }}">
                                    {{ $faq['title'] ?? 'Pertanyaan' }}
                                </button>
                            </h3>
                            <div id="faqCollapse{{ $faqIndex }}" class="accordion-collapse collapse {{ $faqIndex === 0 ? 'show' : '' }}" aria-labelledby="faqHeading{{ $faqIndex }}" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    {{ $faq['content'] ?? '-' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="kontak" class="section-pad pt-0 pb-5">
            <div class="container">
                <div class="surface-box p-4 p-md-5 reveal">
                    <div class="row g-4 align-items-center">
                        <div class="col-lg-7">
                            <h2 class="section-title mb-2">{{ $cta['title'] ?? 'Kirim lebih mudah bersama Kondang Ekspedisi' }}</h2>
                            <p class="section-subtitle mb-3">{{ $cta['subtitle'] ?? 'Mulai order dan pantau progres paket dalam satu aplikasi.' }}</p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ $cta['cta_url'] ?? '/login' }}" class="btn btn-kondang-primary rounded-pill px-4">{{ $cta['cta_label'] ?? 'Mulai Sekarang' }}</a>
                                <a href="#tracking" class="btn btn-kondang-outline rounded-pill px-4">Cek Resi</a>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="row g-2">
                                @foreach ($contacts as $contact)
                                    <div class="col-12">
                                        <div class="contact-card p-3">
                                            <div class="small fw-semibold text-uppercase text-primary">{{ $contact['title'] ?? 'Kontak' }}</div>
                                            <div class="text-secondary-emphasis fw-semibold">{{ $contact['content'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer-wrap py-3">
        <div class="container d-flex flex-column flex-md-row justify-content-between gap-2 small">
            <span>© {{ now()->year }} Kondang Ekspedisi. All rights reserved.</span>
            <span>Modern Blue White Landing Experience</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tooltipTargets = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTargets.forEach((el) => new bootstrap.Tooltip(el));

            const targets = document.querySelectorAll('.reveal');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('show');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15 });

            targets.forEach((el, index) => {
                el.style.transitionDelay = `${Math.min(index * 55, 280)}ms`;
                observer.observe(el);
            });
        });
    </script>
</body>
</html>
