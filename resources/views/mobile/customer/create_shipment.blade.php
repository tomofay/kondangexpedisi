@extends('layouts.mobile')

@section('content')
<div class="space-y-6 pb-20" x-data="bookingForm()">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('customer.dashboard') }}" class="p-2 -ml-2 rounded-full active:bg-slate-200 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <h1 class="text-xl font-bold">Kirim Paket</h1>
    </div>

    <form action="{{ route('customer.shipments.store') }}" method="POST" class="space-y-8">
        @csrf

        <!-- Step 1: Origin & Destination -->
        <div class="space-y-4">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Rute Pengiriman</h2>
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Cabang Asal</label>
                    <select name="branch_id" x-model="form.branch_id" @change="updateQuote()" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 transition-all">
                        <option value="">Pilih Cabang Asal</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->city }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-center -my-2 relative z-10">
                    <div class="p-2 bg-indigo-600 text-white rounded-full shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Cabang Tujuan</label>
                    <select name="destination_branch_id" x-model="form.destination_branch_id" @change="updateQuote()" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 transition-all">
                        <option value="">Pilih Cabang Tujuan</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->city }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Step 2: Sender Details -->
        <div class="space-y-4">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Data Pengirim</h2>
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm space-y-4">
                <input type="text" name="sender_name" value="{{ auth()->user()->name }}" placeholder="Nama Pengirim" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                <input type="tel" name="sender_phone" value="{{ auth()->user()->phone }}" placeholder="No. Telepon" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                <textarea name="sender_address" placeholder="Alamat Lengkap Pengirim" rows="3" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">{{ auth()->user()->address }}</textarea>
            </div>
        </div>

        <!-- Step 3: Recipient Details -->
        <div class="space-y-4">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Data Penerima</h2>
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm space-y-4">
                <input type="text" name="recipient_name" placeholder="Nama Penerima" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                <input type="tel" name="recipient_phone" placeholder="No. Telepon Penerima" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500">
                <textarea name="recipient_address" placeholder="Alamat Lengkap Penerima" rows="3" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
        </div>

        <!-- Step 4: Package Details -->
        <div class="space-y-4">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Informasi Paket</h2>
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Layanan</label>
                    <div class="grid grid-cols-2 gap-2">
                        <template x-for="type in serviceTypes">
                            <button type="button" 
                                    @click="form.service_type = type.id; updateQuote()"
                                    :class="form.service_type === type.id ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-50 dark:bg-slate-800 text-slate-600'"
                                    class="py-3 px-4 rounded-2xl text-xs font-bold transition-all text-center">
                                <span x-text="type.name"></span>
                            </button>
                        </template>
                    </div>
                    <input type="hidden" name="service_type" x-model="form.service_type">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Berat (KG)</label>
                    <div class="flex items-center gap-4 bg-slate-50 dark:bg-slate-800 rounded-2xl px-4 py-1">
                        <button type="button" @click="form.total_weight_kg = Math.max(0.1, parseFloat(form.total_weight_kg) - 0.5).toFixed(1); updateQuote()" class="p-2 text-indigo-600 font-bold text-xl">-</button>
                        <input type="number" name="total_weight_kg" x-model="form.total_weight_kg" step="0.1" @input="updateQuote()" class="w-full bg-transparent border-none text-center font-bold text-lg focus:ring-0">
                        <button type="button" @click="form.total_weight_kg = (parseFloat(form.total_weight_kg) + 0.5).toFixed(1); updateQuote()" class="p-2 text-indigo-600 font-bold text-xl">+</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Summary (Floating Sticky) -->
        <div class="fixed bottom-0 left-0 right-0 p-4 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-t border-slate-100 dark:border-slate-800 z-50">
            <div class="max-w-md mx-auto flex items-center justify-between gap-4">
                <div class="space-y-0.5">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Estimasi</div>
                    <div class="text-xl font-black text-indigo-600" x-text="loading ? '...' : formatRupiah(quote.total_amount)">Rp 0</div>
                </div>
                <button type="submit" 
                        :disabled="loading || !quote.total_amount"
                        class="bg-indigo-600 text-white font-bold py-4 px-8 rounded-2xl shadow-lg shadow-indigo-200 dark:shadow-none active:scale-95 disabled:opacity-50 disabled:grayscale transition-all flex-1">
                    Booking Sekarang
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function bookingForm() {
        return {
            form: {
                branch_id: '',
                destination_branch_id: '',
                service_type: 'regular',
                total_weight_kg: 1.0,
                insurance_amount: 0
            },
            serviceTypes: [
                { id: 'regular', name: 'Regular' },
                { id: 'express', name: 'Express' },
                { id: 'economy', name: 'Economy' },
                { id: 'same_day', name: 'Same Day' }
            ],
            quote: {
                total_amount: 0
            },
            loading: false,
            updateQuote() {
                if (!this.form.branch_id || !this.form.destination_branch_id) return;
                
                this.loading = true;
                const params = new URLSearchParams(this.form);
                
                fetch(`{{ route('customer.shipments.quote') }}?${params.toString()}`)
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            this.quote = res.data;
                        }
                    })
                    .finally(() => this.loading = false);
            },
            formatRupiah(amount) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    maximumFractionDigits: 0
                }).format(amount);
            }
        }
    }
</script>
@endsection
