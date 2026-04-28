<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Manager</h2>
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-700">Level 1</span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500">Halo, {{ $user->name }}. Fokus manager: monitoring operasional, kualitas layanan, dan antrian keputusan.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100"><p class="text-xs text-gray-500">Shipment Hari Ini</p><p class="text-2xl font-bold text-gray-900">{{ number_format($metrics['shipments_today']) }}</p></div>
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100"><p class="text-xs text-gray-500">Shipment Berjalan</p><p class="text-2xl font-bold text-sky-700">{{ number_format($metrics['shipments_in_progress']) }}</p></div>
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100"><p class="text-xs text-gray-500">Shipment Overdue</p><p class="text-2xl font-bold text-red-600">{{ number_format($metrics['shipments_overdue']) }}</p></div>
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100"><p class="text-xs text-gray-500">Approval Pending</p><p class="text-2xl font-bold text-amber-600">{{ number_format($metrics['pending_approvals']) }}</p></div>
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100"><p class="text-xs text-gray-500">Payment Pending</p><p class="text-2xl font-bold text-gray-900">{{ number_format($metrics['payments_pending']) }}</p></div>
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100"><p class="text-xs text-gray-500">Settlement Hari Ini</p><p class="text-2xl font-bold text-emerald-700">Rp {{ number_format($metrics['revenue_settlement_today'], 0, ',', '.') }}</p></div>
            </div>

            <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
                <h3 class="font-semibold text-gray-900 mb-3">Aksi Cepat Manager</h3>
                <div class="flex flex-wrap gap-3 text-sm">
                    <a class="px-4 py-2 rounded-md bg-gray-900 text-white" href="{{ route('reports.summary') }}">Summary Operasional</a>
                    <a class="px-4 py-2 rounded-md bg-blue-600 text-white" href="{{ route('reports.daily-reconciliation') }}">Rekonsiliasi Harian</a>
                    <a class="px-4 py-2 rounded-md bg-amber-600 text-white" href="{{ route('approvals.index') }}">Lihat Queue Approval</a>
                    <a class="px-4 py-2 rounded-md bg-emerald-600 text-white" href="{{ route('reports.branch-performance') }}">Kinerja Cabang</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>