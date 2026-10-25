<x-shop.layout title="Masuk ke Akun — Klik.Cake Makassar">
    <div class="min-h-[75vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-cream">
        <div class="max-w-md w-full">
            
            {{-- Header Card --}}
            <div class="text-center mb-8">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-blush text-strawberry-deep text-xs font-bold tracking-wide uppercase shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Akses Pelanggan & Admin</span>
                </span>
                <h1 class="font-serif font-black text-3xl sm:text-4xl text-cocoa mt-3 tracking-tight">
                    Selamat Datang
                </h1>
                <p class="font-sans text-sm text-cocoa-muted mt-2">
                    Masuk untuk memantau pesanan kue segar Anda atau mengelola operasional toko.
                </p>
            </div>

            {{-- Form Card --}}
            <div class="bg-white rounded-3xl p-7 sm:p-8 shadow-card border border-blush">
                
                {{-- Flash error if any --}}
                @if ($errors->any())
                    <div class="mb-5 p-4 rounded-2xl bg-red-50 border border-red-200 text-xs text-red-700">
                        <div class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="font-bold">Gagal masuk:</p>
                                <ul class="list-disc list-inside mt-1 space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf

                    {{-- Email Input --}}
                    <div>
                        <label for="email" class="block text-xs font-bold text-cocoa uppercase tracking-wider mb-2">
                            Alamat Email <span class="text-strawberry-deep">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-cocoa-muted">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                placeholder="nama@email.com"
                                class="w-full pl-10 pr-4 py-3 rounded-2xl bg-cream/30 border border-blush text-sm text-cocoa placeholder-cocoa-muted/50 focus:outline-none focus:ring-2 focus:ring-strawberry-deep focus:border-transparent transition-all"
                            >
                        </div>
                    </div>

                    {{-- Password Input --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-xs font-bold text-cocoa uppercase tracking-wider">
                                Password <span class="text-strawberry-deep">*</span>
                            </label>
                        </div>
                        <div class="relative" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-cocoa-muted">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input
                                :type="show ? 'text' : 'password'"
                                name="password"
                                id="password"
                                required
                                placeholder="••••••••"
                                class="w-full pl-10 pr-10 py-3 rounded-2xl bg-cream/30 border border-blush text-sm text-cocoa placeholder-cocoa-muted/50 focus:outline-none focus:ring-2 focus:ring-strawberry-deep focus:border-transparent transition-all"
                            >
                            <button
                                type="button"
                                @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-cocoa-muted hover:text-cocoa focus:outline-none"
                            >
                                <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center justify-between text-xs">
                        <label class="flex items-center gap-2 cursor-pointer text-cocoa font-medium select-none">
                            <input
                                type="checkbox"
                                name="remember"
                                class="w-4 h-4 rounded text-strawberry-deep focus:ring-strawberry-deep border-blush"
                            >
                            <span>Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    {{-- Submit Button --}}
                    <button
                        type="submit"
                        class="w-full py-3.5 px-6 rounded-full bg-strawberry-deep hover:bg-strawberry text-white font-extrabold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 group cursor-pointer"
                    >
                        <span>Masuk ke Akun</span>
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </form>

                {{-- Divider --}}
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-blush"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="bg-white px-3 text-cocoa-muted font-bold">atau</span>
                    </div>
                </div>

                {{-- Register Link --}}
                <div class="text-center">
                    <p class="text-xs text-cocoa-muted">
                        Belum punya akun pelanggan?
                        <a href="{{ route('register') }}" class="font-bold text-strawberry-deep hover:underline ml-1">
                            Daftar akun baru
                        </a>
                    </p>
                </div>

                {{-- Admin Direct Connect Box --}}
                <div class="mt-6 p-4 rounded-2xl bg-blush/40 border border-blush text-center">
                    <p class="text-[11px] font-bold text-cocoa">
                        🔐 Terhubung untuk Administrator Toko
                    </p>
                    <p class="text-[11px] text-cocoa-muted mt-0.5 leading-relaxed">
                        Jika Anda login menggunakan akun admin, Anda akan langsung dialihkan ke Panel Admin Filament.
                    </p>
                    <a href="{{ url('/admin/login') }}" class="inline-block mt-2 text-[11px] font-extrabold text-strawberry-deep hover:underline">
                        Langsung buka login khusus /admin &rarr;
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-shop.layout>
