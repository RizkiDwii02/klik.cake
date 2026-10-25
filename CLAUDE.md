# CLAUDE.md — Klik.Cake by Pinky Cake & Bakery

Panduan referensi arsitektur, kepemilikan kode, token desain, alur bisnis, dan perintah operasional untuk proyek Klik.Cake.

---

## 1. Stack Teknologi

- **Backend:** PHP 8.2+ (XAMPP: `C:\xampp\php\php.exe`), Laravel 11.x
- **Database:** MariaDB / MySQL 8 (InnoDB, utf8mb4) di XAMPP (`C:\xampp\mysql`)
  - Dev DB: `klikcake`
  - Test DB: `klikcake_test` (dikelola via `phpunit.xml` & `.env.testing`)
- **Frontend:** Blade + Alpine.js, Tailwind CSS v3 via Vite v6
- **Font:** Fraunces (serif untuk judul & nama produk) + Nunito (sans untuk teks, harga, tombol)
- **Payment Gateway:** Midtrans (library `midtrans/midtrans-php`), dikontrakkan via `app/Contracts/PaymentGateway.php`
- **Testing:** PHPUnit 11 (seluruh test diisolasi ke database terpisah `klikcake_test`)
- **Admin Panel:** Filament (direncanakan aktif pada Fase 4)

---

## 2. Struktur Folder & Kepemilikan Sub-Agent

| Sub-Agent | Area Folder & File yang Dimiliki |
| :--- | :--- |
| **Database, Core & Backend Builder** | `database/`<br>`app/Models/`<br>`app/Enums/`<br>`app/Services/`<br>`app/Contracts/`<br>`routes/webhooks.php`<br>`tests/Unit/`<br>`tests/Feature/Core/` |
| **Frontend Pages Builder** | `resources/views/shop/`<br>`resources/views/components/`<br>`resources/css/`<br>`resources/js/`<br>`app/Http/Controllers/Shop/`<br>`routes/web.php`<br>`tailwind.config.js`, `vite.config.js` |
| **Admin & Panel Builder** | `app/Filament/`<br>`resources/views/filament/` *(Hanya aktif di Fase 4)* |

*Catatan:* `app/Contracts/` dimiliki Core. Sub-agent lain hanya membaca interface.

---

## 3. Token Desain (Tailwind & CSS)

| Token | Nilai Hex | Fungsi & Ketentuan |
| :--- | :--- | :--- |
| `cream` | `#FFF8F0` | Latar belakang utama seluruh halaman |
| `strawberry` | `#E5527A` | Aksen brand dan elemen visual sekunder |
| `strawberry-deep` | `#B82850` | Tombol CTA utama (kontras teks putih lolos WCAG AA & cocok dengan PDF) |
| `blush` | `#FDE3EA` | Latar kartu, section, keranjang, dan placeholder produk |
| `butter` | `#F4B942` | Badge "Fresh Bake", bintang rating, dan aksen promo |
| `cocoa` | `#3B2320` | Warna teks utama untuk judul serif dan paragraf |
| `cocoa-muted` | `#7A5C58` | Warna teks sekunder, petunjuk, dan keterangan porsi |

---

## 4. Aturan UI & Pengalaman Pelanggan

1. **Prinsip Tampilan:** Hangat, pastel, dan menggoda sesuai desain pada `docs/ui-8-screen.pdf`. Dilarang menggunakan tampilan default Breeze/Filament/Bootstrap untuk halaman toko.
2. **Bahasa & Nada:** Bahasa Indonesia santai tapi sopan, ramah khas Makassar.
3. **Format Mata Uang:** Semua harga di database disimpan sebagai `unsignedBigInteger` Rupiah, diformat di UI sebagai `Rp185.000` (tanpa desimal/sen).
4. **Mobile First:** Komponen dirancang responsif, didukung bottom bar navigasi di layar kecil dan tombol WhatsApp melayang di kanan bawah.
5. **Placeholder Gambar:** Jika file foto belum tersedia, gunakan `<x-product-image>` yang merender ilustrasi SVG kue bergaya blush, tanpa menggunakan foto stok generik eksternal.

---

## 5. Status Enums & Pemetaan Status

### Status Internal (`App\Enums\InternalOrderStatus`):
1. `Pesanan masuk` (`ORDER_PLACED`)
2. `Pembayaran terverifikasi` (`PAYMENT_VERIFIED`)
3. `Masuk jadwal` (`SCHEDULED`)
4. `Dipanggang` (`BAKING`)
5. `Dikemas` (`PACKED`)
6. `Dikirim / Siap diambil` (`SHIPPED_OR_READY`)
7. `Selesai` (`COMPLETED`)
8. `Dibatalkan` (`CANCELLED`)

### Pemetaan ke 4 Langkah Pelanggan (`App\Enums\CustomerOrderStatus` - Screen 07):
- **1. Diterima:** `Pesanan masuk`, `Pembayaran terverifikasi`, `Masuk jadwal`
- **2. Dipanggang:** `Dipanggang`
- **3. Siap:** `Dikemas`, `Dikirim / Siap diambil`
- **4. Selesai:** `Selesai`

---

## 6. Ringkasan 8 Screen (Sitemap)

- **Screen 01 (Homepage):** Hero kue unggulan, banner Fresh Bake hari ini, kategori roti/cake/pastry/snackbox, carousel best seller, 3 langkah cara pesan.
- **Screen 02 (Katalog / Koleksi):** Breadcrumb, filter kategori/harga/acara/tanggal, grid kartu 2 kolom di mobile, sort & pagination.
- **Screen 03 (Detail Produk):** Galeri foto, varian ukuran/rasa, input ucapan kartu (maks 40 karakter), pemilih tanggal & slot ketersediaan, info porsi/bahan/alergen/daya tahan.
- **Screen 04 (Keranjang):** Daftar item varian, ucapan, jadwal slot, qty stepper, catatan pesanan, subtotal & CTA Checkout.
- **Screen 05 (Checkout):** Data pemesan (Nama, WA, Email), metode Delivery / Pick-up, pilih zona ongkir Makassar / jam ambil, input kode promo.
- **Screen 06 (Pembayaran):** Pilihan QRIS, GoPay, ShopeePay, VA Transfer, QR/nomor VA, batas waktu bayar (1 jam), tombol konfirmasi bayar.
- **Screen 07 (Konfirmasi & Lacak Pesanan):** Nomor pesanan unik (`KC-YYYYMMDD-XXXX`), 4 langkah progress pelacak, rincian jadwal, tombol chat WA CS.
- **Screen 08 (Dashboard Admin):** Ringkasan pesanan baru, kalender slot Fresh Bake (kuota & booked), manajemen status pesanan & produk.

---

## 7. Aturan Bisnis & Service Core

1. **`SlotAvailabilityService`:**
   - Standar lead time: minimal H-1 (H+1 hari kalender).
   - Custom cake lead time: minimal H-3.
   - Cut-off jam: pukul **18.00 WITA**. Pemesanan untuk esok hari yang dilakukan pada/setelah pukul 18.00 dialihkan ke H+2.
   - Penahanan slot: menggunakan atomic conditional update `booked = booked + ? WHERE booked + ? <= capacity` di dalam `DB::transaction()`. Tidak menggunakan pembacaan manual untuk mencegah race condition.
   - Melebihi kuota melempar `App\Exceptions\SlotFullException` dan service menyediakan saran slot terdekat.
2. **`PricingService`:**
   - Menghitung subtotal dari produk/varian.
   - Menghitung ongkos kirim sesuai zona (`DeliveryZone`).
   - Menghitung diskon kupon (`PromoCode`) baik persen (dengan limit maksimum) maupun nominal tetap.
3. **`PaymentGateway`:**
   - Abstraksi interface `App\Contracts\PaymentGateway`.
   - Implementasi `MidtransGateway` untuk environment produksi/dev nyata.
   - Implementasi `FakeGateway` untuk automated tests dan pengujian offline.

---

## 8. Perintah Development & Pengujian

### Menjalankan Server & Build:
```bash
# Menjalankan MySQL di XAMPP
# C:\xampp\mysql_start.bat

# Menjalankan Vite build
npm run build

# Menjalankan development server
php artisan serve
```

### Database & Migrasi:
```bash
# Migrasi dan seeding database dev (klikcake)
php artisan migrate --seed
```

### Menjalankan Pengujian (Database Terpisah):
```bash
# Menjalankan seluruh test suite di database klikcake_test
php artisan test
```

### Styleguide:
Buka di browser saat server menyala:
```
http://localhost:8000/styleguide
```
*(Hanya aktif pada `APP_ENV=local`)*
