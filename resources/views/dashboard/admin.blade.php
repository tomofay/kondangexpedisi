<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Admin</h2>
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">Level 2</span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500">Halo, {{ $user->name }}. Fokus admin: governance, approval sensitif, audit, dan kontrol konfigurasi.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100"><p class="text-xs text-gray-500">Shipment Hari Ini</p><p class="text-2xl font-bold text-gray-900">{{ number_format($metrics['shipments_today']) }}</p></div>
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100"><p class="text-xs text-gray-500">Approval Pending</p><p class="text-2xl font-bold text-amber-600">{{ number_format($metrics['pending_approvals']) }}</p></div>
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100"><p class="text-xs text-gray-500">Error Belum Selesai</p><p class="text-2xl font-bold text-red-600">{{ number_format($metrics['errors_unresolved']) }}</p></div>
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100"><p class="text-xs text-gray-500">Manual Correction Log</p><p class="text-2xl font-bold text-purple-700">{{ number_format($metrics['manual_correction_logs']) }}</p></div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="font-semibold text-gray-900 mb-3">Kontrol Master Data</h3>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li>Total User: <strong>{{ number_format($metrics['users_total']) }}</strong></li>
                        <li>Total Cabang: <strong>{{ number_format($metrics['branches_total']) }}</strong></li>
                        <li>Total Rate Card: <strong>{{ number_format($metrics['rate_cards_total']) }}</strong></li>
                    </ul>
                </div>

                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 xl:col-span-2">
                    <h3 class="font-semibold text-gray-900 mb-3">Aksi Cepat Admin</h3>
                    <div class="flex flex-wrap gap-3 text-sm">
                        <a class="px-4 py-2 rounded-md bg-gray-900 text-white" href="{{ route('approvals.index') }}">Queue Approval Sensitif</a>
                        <a class="px-4 py-2 rounded-md bg-blue-600 text-white" href="{{ route('reports.daily-reconciliation') }}">Rekonsiliasi Harian</a>
                        <a class="px-4 py-2 rounded-md bg-emerald-600 text-white" href="{{ route('audit-logs.manual-corrections') }}">Manual Correction Logs</a>
                        <a class="px-4 py-2 rounded-md bg-amber-600 text-white" href="{{ route('error-logs.unresolved-queue') }}">Unresolved Error Queue</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>