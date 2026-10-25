<x-shop.layout title="Daftar Akun Baru — Klik.Cake Makassar">
    <div class="min-h-[75vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-cream">
        <div class="max-w-md w-full">
            
            {{-- Header Card --}}
            <div class="text-center mb-8">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-blush text-strawberry-deep text-xs font-bold tracking-wide uppercase shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <span>Pelanggan Baru</span>
                </span>
                <h1 class="font-serif font-black text-3xl sm:text-4xl text-cocoa mt-3 tracking-tight">
                    Buat Akun Anda
                </h1>
                <p class="font-sans text-sm text-cocoa-muted mt-2">
                    Daftar untuk kemudahan pemesanan kue segar &amp; memantau riwayat pesanan Anda.
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
                                <p class="font-bold">Mohon perbaiki isian berikut:</p>
                                <ul class="list-disc list-inside mt-1 space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('register') }}" method="POST" class="space-y-4">
                    @csrf

                    {{-- Name Input --}}
                    <div>
                        <label for="name" class="block text-xs font-bold text-cocoa uppercase tracking-wider mb-1.5">
                            Nama Lengkap <span class="text-strawberry-deep">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            placeholder="Contoh: Nurul Aisyah"
                            class="w-full px-4 py-3 rounded-2xl bg-cream/30 border border-blush text-sm text-cocoa placeholder-cocoa-muted/50 focus:outline-none focus:ring-2 focus:ring-strawberry-deep focus:border-transparent transition-all"
                        >
                    </div>

                    {{-- Email Input --}}
                    <div>
                        <label for="email" class="block text-xs font-bold text-cocoa uppercase tracking-wider mb-1.5">
                            Alamat Email <span class="text-strawberry-deep">*</span>
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            required
                            placeholder="nama@email.com"
                            class="w-full px-4 py-3 rounded-2xl bg-cream/30 border border-blush text-sm text-cocoa placeholder-cocoa-muted/50 focus:outline-none focus:ring-2 focus:ring-strawberry-deep focus:border-transparent transition-all"
                        >
                    </div>

                    {{-- Password Input --}}
                    <div>
                        <label for="password" class="block text-xs font-bold text-cocoa uppercase tracking-wider mb-1.5">
                            Password <span class="text-strawberry-deep">*</span>
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            placeholder="Minimal 6 karakter"
                            class="w-full px-4 py-3 rounded-2xl bg-cream/30 border border-blush text-sm text-cocoa placeholder-cocoa-muted/50 focus:outline-none focus:ring-2 focus:ring-strawberry-deep focus:border-transparent transition-all"
                        >
                    </div>

                    {{-- Password Confirmation Input --}}
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-cocoa uppercase tracking-wider mb-1.5">
                            Konfirmasi Password <span class="text-strawberry-deep">*</span>
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            required
                            placeholder="Ulangi password Anda"
                            class="w-full px-4 py-3 rounded-2xl bg-cream/30 border border-blush text-sm text-cocoa placeholder-cocoa-muted/50 focus:outline-none focus:ring-2 focus:ring-strawberry-deep focus:border-transparent transition-all"
                        >
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button
                            type="submit"
                            class="w-full py-3.5 px-6 rounded-full bg-strawberry-deep hover:bg-strawberry text-white font-extrabold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 group cursor-pointer"
                        >
                            <span>Daftar Akun</span>
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </form>

                {{-- Login Link --}}
                <div class="mt-6 text-center text-xs text-cocoa-muted border-t border-blush pt-5">
                    Sudah memiliki akun?
                    <a href="{{ route('login') }}" class="font-bold text-strawberry-deep hover:underline ml-1">
                        Masuk di sini
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-shop.layout>
