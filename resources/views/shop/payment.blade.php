<x-shop.layout :title="'Pembayaran #' . $order->order_number . ' — Klik.Cake Makassar'">
<div class="bg-cream min-h-screen py-8 md:py-12" x-data="{
    expiresAt: new Date('{{ $order->expires_at?->toIso8601String() ?? now()->addHour()->toIso8601String() }}').getTime(),
    timeLeft: '',
    isExpired: {{ $order->isExpired() ? 'true' : 'false' }},
    isChecking: false,
    checkMessage: '',
    checkMessageType: '',
    copied: false,

    init() {
        this.updateCountdown();
        setInterval(() => this.updateCountdown(), 1000);
    },

    updateCountdown() {
        const now = new Date().getTime();
        const diff = this.expiresAt - now;

        if (diff <= 0) {
            this.timeLeft = '00:00:00';
            this.isExpired = true;
            return;
        }

        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        this.timeLeft = 
            String(hours).padStart(2, '0') + ':' +
            String(minutes).padStart(2, '0') + ':' +
            String(seconds).padStart(2, '0');
    },

    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        });
    },

    async checkPayment() {
        this.isChecking = true;
        this.checkMessage = '';
        try {
            const res = await fetch('{{ route('payment.status', $order->order_number) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();

            if (data.status === 'paid' && data.redirect_url) {
                this.checkMessageType = 'success';
                this.checkMessage = 'Pembayaran terverifikasi! Mengalihkan ke halaman lacak...';
                setTimeout(() => window.location.href = data.redirect_url, 1200);
            } else if (data.status === 'expired') {
                this.isExpired = true;
                this.checkMessageType = 'error';
                this.checkMessage = data.message || 'Pesanan telah kedaluwarsa.';
            } else {
                this.checkMessageType = 'warning';
                this.checkMessage = data.message || 'Pembayaran belum terdeteksi. Silakan selesaikan pembayaran lalu coba lagi.';
            }
        } catch (err) {
            this.checkMessageType = 'error';
            this.checkMessage = 'Gagal menghubungi server untuk verifikasi. Silakan coba kembali.';
        } finally {
            this.isChecking = false;
        }
    }
}">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">

        {{-- Top Card: Status & Countdown --}}
        @if($order->isExpired() || $order->internal_status === \App\Enums\InternalOrderStatus::CANCELLED->value)
            <div class="bg-red-50 border border-red-200 rounded-3xl p-6 md:p-8 text-center mb-8 shadow-card">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-serif font-black text-red-800 mb-2">Batas Waktu Pembayaran Habis</h1>
                <p class="text-sm text-red-700 max-w-md mx-auto mb-6">
                    Pesanan <strong>#{{ $order->order_number }}</strong> telah dibatalkan secara otomatis karena melewati batas waktu 1 jam. Kuota slot dan stok telah dikembalikan.
                </p>
                <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-full bg-strawberry-deep text-white font-bold text-sm hover:bg-strawberry transition-all">
                    <span>Pesan Ulang Kue</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        @else
            {{-- Active Payment View --}}
            <div class="bg-white rounded-3xl shadow-card border border-blush overflow-hidden mb-8">
                
                {{-- Header with Timer --}}
                <div class="bg-blush p-6 md:p-8 border-b border-blush-dark text-center">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white text-strawberry-deep text-xs font-bold shadow-sm mb-3">
                        <span class="w-2 h-2 rounded-full bg-butter animate-pulse"></span>
                        <span>Menunggu Pembayaran</span>
                    </div>

                    <h1 class="text-2xl md:text-3xl font-serif font-bold text-cocoa">
                        Selesaikan Pembayaran
                    </h1>
                    <p class="text-xs text-cocoa-muted mt-1">
                        Nomor Pesanan: <strong class="text-cocoa font-mono font-bold">#{{ $order->order_number }}</strong>
                    </p>

                    {{-- Timer Box --}}
                    <div class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white border border-strawberry/20 text-strawberry-deep font-mono font-extrabold text-base md:text-lg shadow-sm">
                        <svg class="w-5 h-5 text-strawberry" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Bayar sebelum</span>
                        <span x-text="timeLeft" class="text-strawberry-deep font-black">00:59:59</span>
                    </div>
                </div>

                {{-- Total Amount Highlight --}}
                <div class="py-5 px-6 md:px-8 bg-cream/50 border-b border-blush flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <span class="text-xs text-cocoa-muted block font-semibold">Total yang Harus Dibayar:</span>
                        <span class="font-serif font-black text-2xl md:text-3xl text-strawberry-deep">
                            Rp{{ number_format($order->total_amount, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-cocoa-muted block font-semibold">Metode Terpilih:</span>
                        <span class="text-xs font-bold uppercase tracking-wider text-cocoa bg-white px-3 py-1 rounded-full border border-blush">
                            {{ strtoupper($selectedMethod) }}
                        </span>
                    </div>
                </div>

                {{-- Payment Methods Navigation Pills --}}
                <div class="p-6 md:p-8">
                    <label class="block text-xs font-bold text-cocoa mb-3">Pilih Saluran Pembayaran:</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-8">
                        @foreach([
                            'qris' => 'QRIS (Instan)',
                            'gopay' => 'GoPay',
                            'shopeepay' => 'ShopeePay',
                            'transfer' => 'Bank Transfer',
                        ] as $mKey => $mLabel)
                            <a href="{{ route('payment.show', ['orderNumber' => $order->order_number, 'method' => $mKey]) }}"
                               class="py-3 px-3 rounded-2xl text-xs font-bold text-center border transition-all flex flex-col items-center justify-center gap-1.5
                               {{ $selectedMethod === $mKey ? 'bg-strawberry-deep text-white border-strawberry-deep shadow-md' : 'bg-cream/40 text-cocoa border-blush hover:bg-blush/60' }}">
                                <span>{{ $mLabel }}</span>
                            </a>
                        @endforeach
                    </div>

                    {{-- Dynamic Payment Display based on Method --}}
                    @if(in_array($selectedMethod, ['qris', 'gopay', 'shopeepay']))
                        {{-- QR Code Display --}}
                        <div class="bg-cream/40 border border-blush rounded-3xl p-6 text-center max-w-md mx-auto mb-8">
                            <span class="text-xs font-bold text-strawberry uppercase tracking-wider block mb-2">Scan QRIS Nasional</span>
                            <p class="text-xs text-cocoa-muted mb-4">Gunakan GoPay, ShopeePay, BCA Mobile, Livin, OVO, Dana, atau m-Banking apa saja.</p>

                            <div class="w-64 h-64 mx-auto p-3 bg-white rounded-2xl border-2 border-blush shadow-inner flex flex-col items-center justify-center relative">
                                @if(!empty($charge['qr_url']))
                                    <img src="{{ $charge['qr_url'] }}" alt="QRIS Midtrans" class="w-full h-full object-contain">
                                @else
                                    {{-- Clean QR mockup svg with Klik.Cake badge --}}
                                    <div class="w-full h-full bg-gray-50 border border-gray-200 rounded-xl p-2 flex flex-col items-center justify-between">
                                        <div class="grid grid-cols-4 gap-2 w-full p-2 opacity-80">
                                            <div class="w-8 h-8 bg-cocoa rounded"></div>
                                            <div class="w-8 h-8 bg-cocoa/30 rounded"></div>
                                            <div class="w-8 h-8 bg-cocoa/60 rounded"></div>
                                            <div class="w-8 h-8 bg-cocoa rounded"></div>
                                            <div class="w-8 h-8 bg-cocoa/40 rounded"></div>
                                            <div class="w-8 h-8 bg-strawberry rounded"></div>
                                            <div class="w-8 h-8 bg-strawberry-deep rounded"></div>
                                            <div class="w-8 h-8 bg-cocoa/50 rounded"></div>
                                        </div>
                                        <div class="px-2 py-1 bg-strawberry-deep text-white text-[10px] font-bold rounded-full">
                                            Klik.Cake QRIS
                                        </div>
                                        <span class="text-[10px] text-cocoa-muted">NMID: ID1020038847192</span>
                                    </div>
                                @endif
                            </div>

                            @if(!empty($charge['redirect_url']))
                                <div class="mt-4">
                                    <a href="{{ $charge['redirect_url'] }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-strawberry-deep font-bold hover:underline">
                                        <span>Buka di Aplikasi E-Wallet</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Virtual Account Display --}}
                        <div class="bg-cream/40 border border-blush rounded-3xl p-6 text-center max-w-md mx-auto mb-8">
                            <span class="text-xs font-bold text-strawberry uppercase tracking-wider block mb-1">Bank BCA Virtual Account</span>
                            <p class="text-xs text-cocoa-muted mb-4">Salin nomor VA di bawah ini dan transfer via m-BCA, KlikBCA, atau ATM BCA.</p>

                            @php
                                $vaNumber = $charge['va_number'] ?? ('70012' . substr((string) $order->id, -5));
                            @endphp

                            <div class="p-4 bg-white rounded-2xl border border-blush flex items-center justify-between gap-3 shadow-sm">
                                <div>
                                    <span class="text-[11px] text-cocoa-muted block text-left">Nomor Virtual Account</span>
                                    <span class="font-mono font-extrabold text-xl text-cocoa tracking-wider">{{ $vaNumber }}</span>
                                </div>
                                <button type="button" @click="copyToClipboard('{{ $vaNumber }}')" class="px-4 py-2 rounded-xl bg-blush text-strawberry-deep font-bold text-xs hover:bg-strawberry hover:text-white transition-all flex items-center gap-1.5">
                                    <span x-text="copied ? 'Tersalin!' : 'Salin'">Salin</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Feedback message for Status Check --}}
                    <div x-show="checkMessage" x-cloak class="mb-6 p-4 rounded-2xl text-center text-xs font-bold"
                         :class="{
                            'bg-green-50 text-green-700 border border-green-200': checkMessageType === 'success',
                            'bg-amber-50 text-amber-700 border border-amber-200': checkMessageType === 'warning',
                            'bg-red-50 text-red-700 border border-red-200': checkMessageType === 'error'
                         }">
                        <span x-text="checkMessage"></span>
                    </div>

                    {{-- "Saya Sudah Bayar" Button --}}
                    <div class="space-y-4">
                        <button type="button" @click="checkPayment()" :disabled="isChecking" class="w-full flex items-center justify-center gap-2 px-8 py-4 rounded-full bg-strawberry-deep text-white font-bold text-base hover:bg-strawberry transition-all shadow-md transform hover:-translate-y-0.5 disabled:opacity-50">
                            <svg x-show="isChecking" class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span x-text="isChecking ? 'Memeriksa Pembayaran...' : 'Saya Sudah Bayar'">Saya Sudah Bayar</span>
                        </button>

                        <div class="text-center">
                            <a href="https://wa.me/{{ config('shop.wa_number') }}?text=Halo%20Pinky%20Cake,%20saya%20sudah%20transfer%20untuk%20pesanan%20%23{{ $order->order_number }}" target="_blank" class="text-xs text-cocoa-muted hover:text-strawberry transition-colors inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                </svg>
                                <span>Ada kendala pembayaran? Hubungi kami via WhatsApp</span>
                            </a>
                        </div>
                    </div>

                </div>

            </div>
        @endif

    </div>
</div>
</x-shop.layout>
