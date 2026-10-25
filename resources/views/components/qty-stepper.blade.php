@props([
    'min' => 1,
    'max' => 99,
    'initial' => 1,
    'name' => 'quantity',
])

<div
    x-data="{
        qty: {{ $initial }},
        min: {{ $min }},
        max: {{ $max }},
        decrement() {
            if (this.qty > this.min) {
                this.qty--;
                this.$dispatch('qty-changed', { qty: this.qty });
            }
        },
        increment() {
            if (this.qty < this.max) {
                this.qty++;
                this.$dispatch('qty-changed', { qty: this.qty });
            }
        }
    }"
    {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full bg-blush border border-blush-dark p-1 select-none']) }}
>
    <button
        type="button"
        @click="decrement()"
        :disabled="qty <= min"
        class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-cocoa bg-white hover:bg-strawberry-light disabled:opacity-40 disabled:hover:bg-white transition-colors cursor-pointer shadow-sm"
        aria-label="Kurangi kuantitas"
    >
        &minus;
    </button>

    <input
        type="hidden"
        name="{{ $name }}"
        :value="qty"
    />

    <span
        x-text="qty"
        class="w-10 text-center font-extrabold text-sm md:text-base text-cocoa"
    >
        {{ $initial }}
    </span>

    <button
        type="button"
        @click="increment()"
        :disabled="qty >= max"
        class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-cocoa bg-white hover:bg-strawberry-light disabled:opacity-40 disabled:hover:bg-white transition-colors cursor-pointer shadow-sm"
        aria-label="Tambah kuantitas"
    >
        &plus;
    </button>
</div>
