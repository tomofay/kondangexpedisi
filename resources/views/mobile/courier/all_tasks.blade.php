@extends('mobile.base')

@section('content')
<div class="space-y-8 animate-slide-up pb-72" x-data="allTasks()">
    <!-- Header -->
    <div class="flex items-center gap-4 px-1">
        <a href="{{ route('courier.tasks') }}" class="w-12 h-12 glass rounded-2xl flex items-center justify-center shadow-sm text-blue-600 tap-scale">
            <i class="bi bi-chevron-left fs-5"></i>
        </a>
        <h2 class="text-xl font-black tracking-tight">Semua Tugas</h2>
        <div class="ml-auto">
            <button @click="selected.length === {{ count($shipments) }} ? selected = [] : selected = {{ json_encode($shipments->pluck('id')->map(fn($id) => (string)$id)->toArray()) }}" 
                    class="px-4 py-2 bg-white dark:bg-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-blue-600 shadow-sm border border-slate-100 dark:border-slate-700 tap-scale">
                <span x-text="selected.length === {{ count($shipments) }} ? 'Batal Semua' : 'Pilih Semua'"></span>
            </button>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="space-y-4">
        <form action="{{ route('courier.shipments.index') }}" method="GET" class="space-y-4">
            <div class="relative">
                <div class="absolute inset-y-0 left-6 flex items-center pointer-events-none text-slate-400">
                    <i class="bi bi-search"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Resi atau Nama..." 
                       class="w-full bg-white dark:bg-slate-900 border-none h-16 pl-14 pr-6 rounded-[2rem] font-bold text-sm shadow-sm focus:ring-2 focus:ring-blue-600 transition-all">
            </div>

            <div class="flex gap-2 overflow-x-auto pb-2 px-1 scrollbar-hide">
                <a href="{{ route('courier.shipments.index') }}" 
                   class="px-6 py-3 rounded-full text-[10px] font-black uppercase tracking-widest whitespace-nowrap transition-all {{ !request('status') ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-white text-slate-400' }}">
                    Semua
                </a>
                @foreach($statuses as $status)
                    <a href="{{ route('courier.shipments.index', ['status' => $status->code]) }}" 
                       class="px-6 py-3 rounded-full text-[10px] font-black uppercase tracking-widest whitespace-nowrap transition-all {{ request('status') === $status->code ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-white text-slate-400' }}">
                        {{ $status->name }}
                    </a>
                @endforeach
            </div>
        </form>
    </div>

    <!-- Bulk Action Bar (Floating) -->
    <div x-show="selected.length > 0" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-20 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         class="fixed bottom-40 left-4 right-4 z-50">
        <div class="bg-slate-900 text-white p-6 rounded-[2.5rem] shadow-2xl flex items-center justify-between border border-white/10">
            <div class="flex flex-col">
                <span class="text-[10px] font-black uppercase tracking-widest text-blue-400" x-text="selected.length + ' Paket Terpilih'"></span>
                <p class="text-xs font-bold">Edit Jamak</p>
            </div>
            <form action="{{ route('courier.shipments.bulk-edit') }}" method="GET">
                <template x-for="id in selected">
                    <input type="hidden" name="shipment_ids[]" :value="id">
                </template>
                <button type="submit" class="bg-blue-600 px-6 h-12 rounded-2xl font-black text-[10px] uppercase tracking-widest tap-scale flex items-center">
                    Update Status <i class="bi bi-pencil-square ml-2"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Task List -->
    <div class="space-y-4">
        @forelse($shipments as $shipment)
            <div class="relative group">
                <div class="glass p-6 rounded-[2.5rem] border-white/40 flex items-center gap-5 transition-all relative overflow-hidden"
                     :class="selected.includes('{{ $shipment->id }}') ? 'border-blue-600 bg-blue-50/50' : ''">
                    
                    <!-- Checkbox Trigger Area -->
                    <label class="absolute inset-0 z-10 cursor-pointer">
                        <input type="checkbox" value="{{ $shipment->id }}" x-model="selected" class="peer hidden">
                    </label>

                    <!-- Custom Checkbox UI -->
                    <div class="w-10 h-10 rounded-2xl border-2 border-slate-200 peer-checked:border-blue-600 peer-checked:bg-blue-600 flex items-center justify-center text-white transition-all shrink-0 z-20 pointer-events-none"
                         :class="selected.includes('{{ $shipment->id }}') ? 'bg-blue-600 border-blue-600' : ''">
                        <i class="bi bi-check-lg text-lg" x-show="selected.includes('{{ $shipment->id }}')"></i>
                    </div>

                    <div class="flex-1 space-y-1 z-20 pointer-events-none">
                        <div class="flex justify-between items-start">
                            <h4 class="text-lg font-black italic tracking-tighter text-blue-600 uppercase">{{ $shipment->tracking_number }}</h4>
                        </div>
                        <p class="text-xs font-bold text-slate-800 dark:text-white">{{ $shipment->recipient_name }}</p>
                        <span class="inline-block px-3 py-1 bg-slate-50 dark:bg-slate-800 rounded-lg text-[8px] font-black uppercase tracking-widest text-slate-500">
                            {{ $shipment->status->name }}
                        </span>
                    </div>

                    <!-- Edit Button (Z-INDEX 30 to stay above the label) -->
                    <a href="{{ route('courier.shipments.edit', $shipment) }}" 
                       class="w-12 h-12 bg-white dark:bg-slate-800 rounded-2xl flex items-center justify-center text-blue-600 shadow-sm border border-slate-100 dark:border-slate-700 z-30 tap-scale relative">
                        <i class="bi bi-pencil-square fs-5"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="text-center py-20 opacity-40">
                <i class="bi bi-inbox fs-1"></i>
                <p class="text-xs font-black uppercase tracking-widest mt-4">Tidak ada paket</p>
            </div>
        @endforelse
    </div>

    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function allTasks() {
        return {
            selected: [],
        }
    }
</script>
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush
@endsection
