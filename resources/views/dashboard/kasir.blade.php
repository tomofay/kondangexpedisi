<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Kasir</h2>
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-teal-100 text-teal-700">Level 3</span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500">Halo, {{ $user->name }}. Fokus kasir: operasional transaksi cabang, shipment cabang, dan status pembayaran.</p>
                <p class="text-xs text-gray-400 mt-2">Cabang: {{ $metrics['branch_name'] ?? '-' }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100"><p class="text-xs text-gray-500">Shipment Hari Ini</p><p class="text-2xl font-bold text-gray-900">{{ number_format($metrics['shipments_today']) }}</p></div>
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100"><p class="text-xs text-gray-500">Shipment Pending/Transit</p><p class="text-2xl font-bold text-sky-700">{{ number_format($metrics['shipments_pending']) }}</p></div>
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100"><p class="text-xs text-gray-500">Payment Pending</p><p class="text-2xl font-bold text-amber-600">{{ number_format($metrics['payments_pending']) }}</p></div>
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100"><p class="text-xs text-gray-500">Settlement Hari Ini</p><p class="text-2xl font-bold text-emerald-700">{{ number_format($metrics['payments_settlement_today']) }}</p></div>
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 xl:col-span-2"><p class="text-xs text-gray-500">Nominal Settlement Hari Ini</p><p class="text-2xl font-bold text-emerald-700">Rp {{ number_format($metrics['revenue_settlement_today'], 0, ',', '.') }}</p></div>
            </div>

            <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
                <h3 class="font-semibold text-gray-900 mb-3">Aksi Cepat Kasir</h3>
                <div class="flex flex-wrap gap-3 text-sm">
                    <a class="px-4 py-2 rounded-md bg-gray-900 text-white" href="{{ route('shipments.index') }}">Kelola Shipment Cabang</a>
                    <a class="px-4 py-2 rounded-md bg-blue-600 text-white" href="{{ route('payments.index') }}">Kelola Payment</a>
                    <a class="px-4 py-2 rounded-md bg-amber-600 text-white" href="{{ route('reports.payment-overview') }}">Ringkasan Payment</a>
                    <a class="px-4 py-2 rounded-md bg-emerald-600 text-white" href="{{ route('reports.daily-reconciliation') }}">Rekonsiliasi Harian</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>