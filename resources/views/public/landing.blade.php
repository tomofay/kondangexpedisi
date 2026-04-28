<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kondang Ekspedisi - Solusi Logistik Terpercaya & Cepat</title>
    <meta name="description" content="Layanan pengiriman paket profesional dengan sistem pelacakan real-time dan jangkauan nasional.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --primary: #0061FF;
            --primary-dark: #004ecc;
            --secondary: #0B2B67;
            --text-main: #172B4D;
            --text-muted: #6B778C;
            --bg-light: #F4F7FA;
            --white: #FFFFFF;
            --k-shadow: 0 20px 40px rgba(0, 97, 255, 0.08);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-main);
            background-color: var(--white);
            overflow-x: hidden;
        }

        h1, h2, h3, h4, .font-sora { font-family: 'Sora', sans-serif; }

        /* Smooth Navbar */
        .navbar {
            padding: 1.25rem 0;
            transition: all 0.3s ease;
            background: transparent;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 0.8rem 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        }

        .navbar-brand { font-weight: 800; font-size: 1.5rem; color: var(--secondary) !important; }
        .navbar-brand span { color: var(--primary); }

        .nav-link { font-weight: 700; color: var(--secondary) !important; margin: 0 10px; }

        /* Hero Style */
        .hero-wrap {
            padding: 180px 0 120px;
            background: radial-gradient(circle at 100% 0%, #F0F7FF 0%, #FFFFFF 60%);
            position: relative;
        }

        .hero-title { font-size: 4rem; font-weight: 800; line-height: 1.1; color: var(--secondary); letter-spacing: -1.5px; }
        .hero-title span { color: var(--primary); }

        /* Professional Action Card */
        .action-container {
            background: white;
            border-radius: 30px;
            padding: 2.5rem;
            box-shadow: 0 30px 60px rgba(0, 97, 255, 0.12);
            border: 1px solid #E2E8F0;
        }

        .nav-tabs-clean {
            border: none;
            background: #F8FAFC;
            padding: 6px;
            border-radius: 16px;
            margin-bottom: 2rem;
            display: flex;
        }

        .nav-tabs-clean .nav-link {
            border: none;
            border-radius: 12px;
            padding: 12px;
            color: var(--text-muted);
            font-weight: 700;
            flex: 1;
            transition: 0.3s;
        }

        .nav-tabs-clean .nav-link.active {
            background: white;
            color: var(--primary);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .input-clean-group {
            background: #F8FAFC;
            border-radius: 18px;
            padding: 4px 8px;
            display: flex;
            align-items: center;
            border: none;
            transition: 0.3s;
        }

        .input-clean-group:focus-within {
            background: white;
            box-shadow: 0 0 0 4px rgba(0, 97, 255, 0.08);
        }

        .input-clean-group i { color: var(--primary); margin-right: 12px; font-size: 1.1rem; }
        .input-clean-group input, .input-clean-group select {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            font-weight: 600;
            width: 100%;
            padding: 12px 0;
            color: var(--secondary);
            font-size: 1rem;
        }

        .input-clean-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230061FF' class='bi bi-chevron-down' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 40px;
        }

        .btn-kondang-solid {
            background: var(--primary);
            color: white;
            padding: 1.1rem;
            border-radius: 14px;
            font-weight: 800;
            border: none;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(0, 97, 255, 0.2);
        }

        .btn-kondang-solid:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 15px 30px rgba(0, 97, 255, 0.3); }

        /* Dynamic Result */
        .dynamic-result {
            display: none;
            margin-top: 2rem;
            padding: 1.5rem;
            border-radius: 24px;
            background: #F0F7FF;
            border: 1px solid rgba(0, 97, 255, 0.1);
        }

        .timeline-line {
            border-left: 2px dashed var(--primary);
            margin-left: 1rem;
            padding-left: 2rem;
            position: relative;
        }

        .timeline-point {
            position: relative;
            padding-bottom: 1.5rem;
        }

        .timeline-dot {
            position: absolute;
            left: -39px;
            top: 5px;
            width: 14px;
            height: 14px;
            background: var(--primary);
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 4px #EBF3FF;
        }

        /* Features Section */
        .card-service {
            border: none;
            border-radius: 30px;
            padding: 3rem 2rem;
            transition: all 0.4s ease;
            background: #F8FAFC;
            height: 100%;
            border: 1px solid transparent;
        }

        .card-service:hover {
            background: white;
            transform: translateY(-15px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.05);
            border-color: #EBF3FF;
        }

        .icon-circle {
            width: 70px;
            height: 70px;
            background: white;
            color: var(--primary);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 2rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.03);
        }

        /* Section Typography */
        .section-tag {
            text-transform: uppercase;
            font-weight: 800;
            font-size: 0.85rem;
            color: var(--primary);
            letter-spacing: 2px;
            display: block;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 1.5rem;
        }

        /* Stats Strip */
        .stats-strip {
            background: var(--secondary);
            color: white;
            padding: 60px 0;
        }

        /* Footer Modern */
        .footer-main {
            background: #051937;
            color: white;
            padding: 100px 0 40px;
            margin-top: 100px;
        }

        .footer-link {
            color: #A0AEC0;
            text-decoration: none;
            transition: 0.3s;
            display: block;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .footer-link:hover { color: white; padding-left: 5px; }

        .social-btn {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 10px;
            transition: 0.3s;
        }

        .social-btn:hover { background: var(--primary); color: white; transform: translateY(-3px); }

        @media (max-width: 991.98px) {
            .hero-title { font-size: 3rem; }
            .action-container { margin-top: 3rem; }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top" id="topNav">
        <div class="container">
            <a class="navbar-brand" href="/">KONDANG<span>EKSPEDISI</span></a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                <i class="bi bi-list fs-1"></i>
            </button>
            <div class="collapse navbar-collapse" id="navContent">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#layanan">Layanan</a></li>
                    <li class="nav-item"><a class="nav-link" href="#estimasi">Cek Tarif</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tentang">Tentang Kami</a></li>
                    <li class="nav-item ms-lg-4">
                        <a href="{{ route('login') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">Dashboard Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-wrap">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-5 animate__animated animate__fadeIn">
                    <span class="section-tag">Logistik Generasi Baru</span>
                    <h1 class="hero-title">Kirim Paket Secepat <span>Kilat.</span></h1>
                    <p class="text-muted fs-5 mb-5">Keamanan paket Anda adalah prioritas kami. Didukung sistem tracking tercanggih untuk ketenangan pikiran Anda.</p>
                    
                    <div class="row g-4 mb-4">
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary-light text-primary p-2 rounded-3" style="background-color: #EBF3FF;"><i class="bi bi-shield-fill-check fs-4"></i></div>
                                <span class="fw-bold small">Fully Insured</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary-light text-primary p-2 rounded-3" style="background-color: #EBF3FF;"><i class="bi bi-geo-alt-fill fs-4"></i></div>
                                <span class="fw-bold small">National Coverage</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="action-container animate__animated animate__fadeInRight">
                        <nav class="nav-tabs-clean" role="tablist">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pnl-track">Lacak Paket</button>
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pnl-quote">Cek Tarif</button>
                        </nav>

                        <div class="tab-content">
                            <!-- Form Lacak -->
                            <div class="tab-pane fade show active" id="pnl-track">
                                <form id="formLacak">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="small fw-extrabold text-muted mb-2">NOMOR RESI</label>
                                            <div class="input-clean-group"><i class="bi bi-upc-scan"></i><input type="text" name="tracking_number" placeholder="KND-2026-..." required></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small fw-extrabold text-muted mb-2">TELEPON PENERIMA</label>
                                            <div class="input-clean-group"><i class="bi bi-phone"></i><input type="text" name="recipient_phone" placeholder="08XXXXXXXXXX" required></div>
                                        </div>
                                        <div class="col-12"><button type="submit" class="btn-kondang-solid w-100">LACAK PENGIRIMAN SEKARANG</button></div>
                                    </div>
                                </form>
                                <div id="hasilLacak" class="dynamic-result"></div>
                            </div>

                            <!-- Form Tarif -->
                            <div class="tab-pane fade" id="pnl-quote">
                                <form id="formTarif">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="small fw-extrabold text-muted mb-2">ASAL PENGIRIMAN</label>
                                            <div class="input-clean-group"><i class="bi bi-geo-fill"></i>
                                                <select name="origin_branch_id" required>
                                                    <option value="">Pilih Kota Asal</option>
                                                    @foreach($branches as $b) <option value="{{$b->id}}">{{$b->city}}</option> @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small fw-extrabold text-muted mb-2">TUJUAN PENGIRIMAN</label>
                                            <div class="input-clean-group"><i class="bi bi-send-fill"></i>
                                                <select name="destination_branch_id" required>
                                                    <option value="">Pilih Kota Tujuan</option>
                                                    @foreach($branches as $b) <option value="{{$b->id}}">{{$b->city}}</option> @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small fw-extrabold text-muted mb-2">BERAT ESTIMASI (KG)</label>
                                            <div class="input-clean-group"><i class="bi bi-box-seam-fill"></i><input type="number" name="weight_kg" value="1" min="1"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small fw-extrabold text-muted mb-2">PILIH LAYANAN</label>
                                            <div class="input-clean-group"><i class="bi bi-lightning-charge-fill"></i>
                                                <select name="service_type">
                                                    @foreach($serviceTypes as $s) <option value="{{$s}}">{{strtoupper($s)}}</option> @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12"><button type="submit" class="btn-kondang-solid w-100">HITUNG ESTIMASI ONGKIR</button></div>
                                    </div>
                                </form>
                                <div id="hasilTarif" class="dynamic-result"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Strip -->
    <div class="stats-strip">
        <div class="container text-center">
            <div class="row g-4">
                <div class="col-md-4">
                    <h2 class="fw-bold mb-0">2.5K+</h2>
                    <p class="text-white-50 mb-0 small">Paket Berhasil Terkirim</p>
                </div>
                <div class="col-md-4 border-start border-end border-white border-opacity-10">
                    <h2 class="fw-bold mb-0">500+</h2>
                    <p class="text-white-50 mb-0 small">Armada Kurir Profesional</p>
                </div>
                <div class="col-md-4">
                    <h2 class="fw-bold mb-0">120+</h2>
                    <p class="text-white-50 mb-0 small">Titik Distribusi Nasional</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Services -->
    <section id="layanan" class="py-5 my-5">
        <div class="container py-lg-5">
            <div class="text-center mb-5">
                <span class="section-tag">Layanan Kami</span>
                <h2 class="section-title">Pilihan Pengiriman <span>Eksklusif</span></h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card-service text-center">
                        <div class="icon-circle"><i class="bi bi-lightning-charge"></i></div>
                        <h4 class="fw-bold mb-3">Kondang Express</h4>
                        <p class="text-muted small">Prioritas pengiriman kilat dengan jaminan tiba di hari yang sama atau keesokan harinya untuk wilayah tertentu.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-service text-center">
                        <div class="icon-circle"><i class="bi bi-truck"></i></div>
                        <h4 class="fw-bold mb-3">Kondang Reguler</h4>
                        <p class="text-muted small">Layanan pengiriman paket standar dengan biaya terjangkau dan jangkauan pengiriman paling luas di Indonesia.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-service text-center">
                        <div class="icon-circle"><i class="bi bi-box-seam"></i></div>
                        <h4 class="fw-bold mb-3">Kondang Cargo</h4>
                        <p class="text-muted small">Solusi logistik untuk pengiriman barang dalam jumlah besar dengan tarif per-kilo yang lebih ekonomis.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Us -->
    <section id="tentang" class="py-5 bg-light rounded-5 mx-2 mx-lg-5">
        <div class="container py-lg-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1578575437130-527eed3abbec?q=80&w=2070&auto=format&fit=crop" class="img-fluid rounded-5 shadow-lg" alt="Warehouse">
                </div>
                <div class="col-lg-6">
                    <span class="section-tag">Kenapa Kami?</span>
                    <h2 class="section-title">Keunggulan <span>Kondang Ekspedisi</span></h2>
                    <div class="d-grid gap-4 mt-5">
                        <div class="d-flex gap-4">
                            <div class="icon-circle m-0 bg-white shadow-sm text-primary"><i class="bi bi-lightning-charge-fill"></i></div>
                            <div>
                                <h5 class="fw-bold">Pengiriman Super Kilat</h5>
                                <p class="text-muted small">Optimasi rute distribusi kami memastikan paket Anda tiba lebih cepat dari estimasi standar, bahkan untuk pengiriman antar pulau.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-4">
                            <div class="icon-circle m-0 bg-white shadow-sm text-primary"><i class="bi bi-shield-lock-fill"></i></div>
                            <div>
                                <h5 class="fw-bold">Garansi Keamanan Barang</h5>
                                <p class="text-muted small">Kami memberikan perlindungan penuh terhadap risiko kerusakan atau kehilangan. Setiap paket ditangani dengan standar prosedur keamanan tinggi.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-4">
                            <div class="icon-circle m-0 bg-white shadow-sm text-primary"><i class="bi bi-geo-alt-fill"></i></div>
                            <div>
                                <h5 class="fw-bold">Jangkauan Luas & Presisi</h5>
                                <p class="text-muted small">Titik distribusi yang tersebar hingga pelosok daerah memungkinkan kami menjangkau alamat tersulit dengan akurasi pengantaran yang tinggi.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-main" id="kontak">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <h2 class="fw-bold mb-4">KONDANG<span>EKSPEDISI</span></h2>
                    <p class="text-white-50 mb-5">Menyediakan solusi logistik terpercaya dan transparan untuk menghubungkan setiap sudut Indonesia.</p>
                    <div class="d-flex">
                        <a href="#" class="social-btn"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-facebook"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 offset-lg-1">
                    <h6 class="fw-bold mb-4 text-uppercase">Perusahaan</h6>
                    <a href="#" class="footer-link">Tentang Kami</a>
                    <a href="#" class="footer-link">Layanan</a>
                    <a href="#" class="footer-link">Karir</a>
                    <a href="#" class="footer-link">Partner</a>
                </div>
                <div class="col-lg-2">
                    <h6 class="fw-bold mb-4 text-uppercase">Bantuan</h6>
                    <a href="#pnl-track" class="footer-link">Lacak Resi</a>
                    <a href="#" class="footer-link">FAQ</a>
                    <a href="#" class="footer-link">Syarat & Ketentuan</a>
                    <a href="#" class="footer-link">Privasi</a>
                </div>
                <div class="col-lg-3">
                    <h6 class="fw-bold mb-4 text-uppercase">Kontak</h6>
                    <div class="d-flex gap-3 mb-4">
                        <i class="bi bi-geo-alt-fill text-primary fs-5"></i>
                        <span class="text-white-50 small">Jl. Medan Merdeka Selatan No. 10, Jakarta Pusat</span>
                    </div>
                    <div class="d-flex gap-3 mb-4">
                        <i class="bi bi-telephone-fill text-primary fs-5"></i>
                        <span class="text-white-50 small">(021) 555-1000</span>
                    </div>
                    <div class="d-flex gap-3">
                        <i class="bi bi-envelope-fill text-primary fs-5"></i>
                        <span class="text-white-50 small">cs@kondang.co.id</span>
                    </div>
                </div>
            </div>
            <hr class="border-light opacity-10 my-5">
            <div class="d-flex flex-wrap justify-content-between gap-3 text-white-50 small">
                <span>© {{ now()->year }} Kondang Ekspedisi. Hak Cipta Dilindungi.</span>
                <span>Powered by Logistics Technology</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        const setupAjax = (formId, url, resultId, renderFn) => {
            const form = document.getElementById(formId);
            if(!form) return;
            
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = e.target.querySelector('button');
                const resultBox = document.getElementById(resultId);
                const originalText = btn.innerHTML;
                
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> PROSES...';
                resultBox.style.display = 'none';

                try {
                    const params = Object.fromEntries(new FormData(e.target));
                    const { data } = await axios.get(url, { params });
                    resultBox.innerHTML = renderFn(data);
                    resultBox.className = 'dynamic-result animate__animated animate__fadeIn';
                    resultBox.style.display = 'block';
                } catch (err) {
                    resultBox.innerHTML = `<div class="text-danger fw-bold small"><i class="bi bi-info-circle-fill me-2"></i>${err.response?.data?.error || 'Gagal mengambil data. Periksa input Anda.'}</div>`;
                    resultBox.style.display = 'block';
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });
        };

        setupAjax('formLacak', '/api/track', 'hasilLacak', (d) => `
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <span class="badge bg-primary rounded-pill px-3 py-2">${d.status_name}</span>
                <small class="fw-bold text-muted">${d.tracking_number}</small>
            </div>
            <div class="timeline-line">
                ${d.trackings.map(t => `
                    <div class="timeline-point">
                        <div class="timeline-dot"></div>
                        <div class="fw-bold small text-secondary">${t.status}</div>
                        <div style="font-size:0.75rem" class="text-muted">${t.time} • ${t.location}</div>
                    </div>
                `).join('')}
            </div>
        `);

        setupAjax('formTarif', '/api/quote', 'hasilTarif', (d) => `
            <div class="text-center">
                <div class="text-muted small fw-bold mb-1 text-uppercase">ESTIMASI BIAYA PENGIRIMAN</div>
                <div class="h2 fw-bold text-primary mb-0">Rp ${new Intl.NumberFormat('id-ID').format(d.total)}</div>
                <div class="mt-2 small text-muted">Layanan: <strong>${d.service_type}</strong> | Berat: <strong>${d.weight} Kg</strong></div>
            </div>
        `);

        window.addEventListener('scroll', () => {
            const nav = document.getElementById('topNav');
            window.scrollY > 50 ? nav.classList.add('scrolled') : nav.classList.remove('scrolled');
        });
    </script>
</body>
</html>
