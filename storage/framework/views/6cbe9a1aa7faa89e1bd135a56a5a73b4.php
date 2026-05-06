<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'Kondang Ekspedisi')); ?></title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        <!-- Scripts -->
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

        <style>
            :root {
                --primary: #0061FF;
                --primary-dark: #004ecc;
                --secondary: #0B2B67;
                --bg-body: #F4F7FA;
                --sidebar-width: 260px;
                --sidebar-collapsed-width: 80px;
            }

            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background-color: var(--bg-body);
                color: #172B4D;
                margin: 0;
            }

            /* Sidebar Styles */
            #sidebar {
                width: var(--sidebar-width);
                height: 100vh;
                position: fixed;
                left: 0;
                top: 0;
                background: white;
                border-right: 1px solid #E2E8F0;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 1000;
                overflow-x: hidden;
            }

            #sidebar.collapsed {
                width: var(--sidebar-collapsed-width);
            }

            .sidebar-header {
                padding: 1.5rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                white-space: nowrap;
            }

            .brand-logo {
                font-family: 'Sora', sans-serif;
                font-weight: 800;
                font-size: 1.25rem;
                color: var(--primary);
                text-decoration: none;
                opacity: 1;
                transition: opacity 0.2s;
            }

            #sidebar.collapsed .brand-logo {
                opacity: 0;
                pointer-events: none;
            }

            .nav-menu {
                list-style: none;
                padding: 0.5rem;
                margin: 0;
            }

            .nav-item {
                margin-bottom: 0.25rem;
            }

            .nav-link-custom {
                display: flex;
                align-items: center;
                padding: 0.75rem 1rem;
                color: #6B778C;
                text-decoration: none !important;
                border-radius: 12px;
                font-weight: 600;
                transition: all 0.2s;
                white-space: nowrap;
                gap: 12px;
            }

            .nav-link-custom:hover, .nav-link-custom.active {
                background: #F0F7FF;
                color: var(--primary);
            }

            .nav-link-custom.active {
                background: var(--primary);
                color: white;
            }

            .nav-link-custom i {
                font-size: 1.25rem;
                min-width: 24px;
                text-align: center;
            }

            #sidebar.collapsed .nav-text {
                display: none;
            }

            /* Main Content Styles */
            #main-wrapper {
                margin-left: var(--sidebar-width);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                min-height: 100vh;
            }

            #main-wrapper.expanded {
                margin-left: var(--sidebar-collapsed-width);
            }

            .top-navbar {
                background: white;
                height: 70px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 2rem;
                border-bottom: 1px solid #E2E8F0;
                position: sticky;
                top: 0;
                z-index: 999;
            }

            .toggle-sidebar {
                background: #F4F7FA;
                border: none;
                width: 40px;
                height: 40px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                color: var(--secondary);
                transition: 0.2s;
            }

            .toggle-sidebar:hover {
                background: #EBF3FF;
                color: var(--primary);
            }

            .content-area {
                padding: 2rem;
            }

            @media (max-width: 768px) {
                #sidebar {
                    left: -100%;
                }
                #sidebar.mobile-show {
                    left: 0;
                    width: var(--sidebar-width);
                }
                #main-wrapper {
                    margin-left: 0;
                }
                .content-area {
                    padding: 1rem;
                }
                .top-navbar {
                    padding: 0 1rem;
                }
            }
        </style>
    </head>
    <body>
        <!-- Sidebar -->
        <aside id="sidebar">
            <div class="sidebar-header">
                <a href="/" class="brand-logo">KONDANG<span>EKSPEDISI</span></a>
                <button class="toggle-sidebar d-md-none" onclick="toggleMobileSidebar()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?php echo e(route('dashboard')); ?>" class="nav-link-custom <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                        <i class="bi bi-grid-fill"></i>
                        <span class="nav-text">Ringkasan</span>
                    </a>
                </li>
                
                <?php if(auth()->user()->role === 'admin' || auth()->user()->role === 'manager'): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('approvals.index')); ?>" class="nav-link-custom <?php echo e(request()->routeIs('approvals.*') ? 'active' : ''); ?>">
                        <i class="bi bi-patch-check-fill"></i>
                        <span class="nav-text">Approval</span>
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a href="<?php echo e(route('shipments.index')); ?>" class="nav-link-custom <?php echo e(request()->routeIs('shipments.*') ? 'active' : ''); ?>">
                        <i class="bi bi-box-seam-fill"></i>
                        <span class="nav-text">Pengiriman</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo e(route('payments.index')); ?>" class="nav-link-custom <?php echo e(request()->routeIs('payments.*') ? 'active' : ''); ?>">
                        <i class="bi bi-credit-card-fill"></i>
                        <span class="nav-text">Pembayaran</span>
                    </a>
                </li>

                <?php if(auth()->user()->role === 'admin' || auth()->user()->role === 'manager'): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('rate-cards.index')); ?>" class="nav-link-custom <?php echo e(request()->routeIs('rate-cards.*') ? 'active' : ''); ?>">
                        <i class="bi bi-tag-fill"></i>
                        <span class="nav-text">Rate Card</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo e(route('vehicles.index')); ?>" class="nav-link-custom <?php echo e(request()->routeIs('vehicles.*') ? 'active' : ''); ?>">
                        <i class="bi bi-truck-front-fill"></i>
                        <span class="nav-text">Armada</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if(auth()->user()->role === 'admin'): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('branches.index')); ?>" class="nav-link-custom <?php echo e(request()->routeIs('branches.*') ? 'active' : ''); ?>">
                        <i class="bi bi-building-fill"></i>
                        <span class="nav-text">Cabang & Zona</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if(auth()->user()->role === 'admin' || auth()->user()->role === 'manager'): ?>
                <li class="nav-item">
                    <a href="<?php echo e(auth()->user()->role === 'manager' ? route('dashboard') . '#view-users' : url('/users')); ?>" class="nav-link-custom <?php echo e(request()->is('users*') ? 'active' : ''); ?>">
                        <i class="bi bi-people-fill"></i>
                        <span class="nav-text">Manajemen User</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if(auth()->user()->role === 'admin' || auth()->user()->role === 'manager'): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('reports.summary')); ?>" class="nav-link-custom <?php echo e(request()->routeIs('reports.*') ? 'active' : ''); ?>">
                        <i class="bi bi-file-earmark-bar-graph-fill"></i>
                        <span class="nav-text">Laporan</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <div style="position: absolute; bottom: 1rem; width: 100%; padding: 0.5rem;">
                <form action="<?php echo e(route('logout')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="nav-link-custom w-100 border-0 bg-transparent text-danger" style="color: #dc3545 !important;">
                        <i class="bi bi-box-arrow-left"></i>
                        <span class="nav-text">Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div id="main-wrapper">
            <header class="top-navbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="toggle-sidebar d-none d-md-flex" onclick="toggleSidebar()">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <button class="toggle-sidebar d-md-none" onclick="toggleMobileSidebar()">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <?php if(isset($header)): ?>
                        <div class="m-0 p-0">
                            <?php echo e($header); ?>

                        </div>
                    <?php endif; ?>
                </div>

                <a href="<?php echo e(route('profile.edit')); ?>" class="d-flex align-items-center gap-3 text-decoration-none">
                    <div class="d-none d-sm-flex flex-column align-items-end text-end">
                        <span class="fw-bold text-dark" style="font-size: 0.9rem;"><?php echo e(auth()->user()->name); ?></span>
                        <span class="fw-bold text-primary text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;"><?php echo e(auth()->user()->role); ?></span>
                    </div>
                    <div class="bg-primary text-white fw-bold d-flex align-items-center justify-content-center shadow-sm overflow-hidden" style="width: 42px; height: 42px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,97,255,0.2);">
                        <?php if(auth()->user()->photo): ?>
                            <img src="<?php echo e(asset('storage/' . auth()->user()->photo)); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <?php echo e(substr(auth()->user()->name, 0, 1)); ?>

                        <?php endif; ?>
                    </div>
                </a>
            </header>

            <main class="content-area">
                <?php echo e($slot); ?>

            </main>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                const wrapper = document.getElementById('main-wrapper');
                sidebar.classList.toggle('collapsed');
                wrapper.classList.toggle('expanded');
                
                // Save preference
                localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
            }

            function toggleMobileSidebar() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('mobile-show');
            }

            // Restore preference
            document.addEventListener('DOMContentLoaded', () => {
                if (localStorage.getItem('sidebar-collapsed') === 'true') {
                    const sidebar = document.getElementById('sidebar');
                    const wrapper = document.getElementById('main-wrapper');
                    sidebar.classList.add('collapsed');
                    wrapper.classList.add('expanded');
                }

                // Global Session Alerts
                <?php if(session('success')): ?>
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: "<?php echo e(session('success')); ?>", timer: 3000, showConfirmButton: false });
                <?php endif; ?>
                <?php if(session('error')): ?>
                    Swal.fire({ icon: 'error', title: 'Error', text: "<?php echo e(session('error')); ?>" });
                <?php endif; ?>
                <?php if(session('warning')): ?>
                    Swal.fire({ icon: 'warning', title: 'Perhatian', text: "<?php echo e(session('warning')); ?>" });
                <?php endif; ?>
            });
        </script>
        <?php echo $__env->yieldPushContent('scripts'); ?>
    </body>
</html><?php /**PATH C:\Users\toram\OneDrive\Desktop\projek\ekspedisi-online\resources\views/layouts/app.blade.php ENDPATH**/ ?>