<x-filament-panels::page>
    <div class="space-y-6">
        
        {{-- Explanation Card --}}
        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-pink-50 dark:bg-pink-950/40 text-[#E5527A] flex items-center justify-center flex-shrink-0 font-bold">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="text-sm">
                    <h3 class="font-bold text-gray-900 dark:text-gray-100">Manajemen Kuota Fresh Bake Harian</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-xs mt-1 leading-relaxed">
                        Pantau dan atur kapasitas produksi dapur Pinky Cake untuk setiap slot waktu (10.00, 13.00, 16.00 WITA) selama 14 hari ke depan.
                        Mengurangi kapasitas slot di bawah jumlah terpesan (atau menjadi 0) akan membuat slot tersebut <strong>PENUH</strong> dan langsung tidak dapat dipilih oleh pelanggan di toko online.
                    </p>
                </div>
            </div>
        </div>

        {{-- 14-Day Calendar Table --}}
        <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
            <table class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/60 text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 w-44">Tanggal / Hari</th>
                        <th class="py-3.5 px-4 text-center">Slot 10.00 – 12.00 WITA</th>
                        <th class="py-3.5 px-4 text-center">Slot 13.00 – 15.00 WITA</th>
                        <th class="py-3.5 px-4 text-center">Slot 16.00 – 18.00 WITA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($this->daysData as $day)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors {{ $day['is_today'] ? 'bg-amber-50/30 dark:bg-amber-950/20' : '' }}">
                            
                            {{-- Date Column --}}
                            <td class="py-4 px-4 align-top">
                                <div class="font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                    <span>{{ $day['formatted_date'] }}</span>
                                    @if($day['is_today'])
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200 uppercase">
                                            Hari Ini
                                        </span>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 block mt-0.5">
                                    {{ $day['day_name'] }}
                                </span>
                            </td>

                            {{-- 3 Slots Columns --}}
                            @foreach(['10:00', '13:00', '16:00'] as $slotKey)
                                @php
                                    $slot = $day['slots'][$slotKey];
                                    $isFull = $slot['is_full'];
                                @endphp
                                <td class="py-3 px-3 align-top">
                                    <div class="rounded-xl p-3 border transition-all text-xs
                                        {{ $isFull
                                            ? 'bg-rose-50/70 border-rose-200 dark:bg-rose-950/30 dark:border-rose-900/50'
                                            : 'bg-gray-50/60 border-gray-200 dark:bg-gray-800/40 dark:border-gray-800'
                                        }}">
                                        
                                        {{-- Status Pill & Counter --}}
                                        <div class="flex items-center justify-between gap-2 mb-2">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider
                                                {{ $isFull
                                                    ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300'
                                                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300'
                                                }}">
                                                {{ $isFull ? 'PENUH' : 'TERSEDIA' }}
                                            </span>
                                            <span class="font-mono font-bold text-gray-700 dark:text-gray-300">
                                                {{ $slot['booked'] }} / {{ $slot['capacity'] }} kue
                                            </span>
                                        </div>

                                        {{-- Remaining Info --}}
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mb-3">
                                            Sisa kuota: <strong class="{{ $isFull ? 'text-rose-600' : 'text-emerald-600' }}">{{ $slot['remaining'] }}</strong>
                                        </div>

                                        {{-- Capacity Input Form --}}
                                        <div class="flex items-center gap-1.5 pt-2 border-t border-gray-200 dark:border-gray-700/60" x-data="{ cap: {{ $slot['capacity'] }} }">
                                            <label class="text-[10px] text-gray-500 uppercase font-bold">Kapasitas:</label>
                                            <input
                                                type="number"
                                                min="0"
                                                max="99"
                                                x-model="cap"
                                                class="w-14 h-7 text-xs font-bold text-center rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-[#E5527A] focus:ring-[#E5527A] p-0.5"
                                            >
                                            <button
                                                type="button"
                                                @click="$wire.updateSlotCapacity('{{ $day['date'] }}', '{{ $slotKey }}', parseInt(cap))"
                                                class="h-7 px-2.5 rounded-lg bg-[#E5527A] hover:bg-[#B82850] text-white text-[11px] font-bold transition-colors"
                                                title="Simpan Kapasitas"
                                            >
                                                Ubah
                                            </button>
                                        </div>

                                    </div>
                                </td>
                            @endforeach

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</x-filament-panels::page>
