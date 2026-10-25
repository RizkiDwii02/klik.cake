# Klik.Cake E-Commerce — by Pinky Cake and Bakery

Platform e-commerce B2C untuk pemesanan roti & kue segar (*fresh bake to order*) khas Makassar. Dibangun menggunakan Laravel 11, Blade, Alpine.js, Tailwind CSS, MySQL 8/MariaDB di XAMPP, Filament Admin v5, dan Midtrans Payment Gateway.

---

## Daftar Isi
1. [Fitur Utama](#fitur-utama)
2. [Prasyarat Sistem](#prasyarat-sistem)
3. [Panduan Instalasi & Menjalankan di XAMPP](#panduan-instalasi--menjalankan-di-xampp)
4. [Alur Demo 5 Menit (Panduan Evaluasi)](#alur-demo-5-menit-panduan-evaluasi)
5. [Fitur Nyata (Real) vs Sederhana (Simplified)](#fitur-nyata-real-vs-sederhana-simplified)
6. [Panduan Kesiapan Produksi (Deployment)](#panduan-kesiapan-produksi-deployment)
7. [Checklist Go-Live](#checklist-go-live)
8. [Pengujian Otomatis (Testing)](#pengujian-otomatis-testing)

---

## 1. Fitur Utama

- **8 Screen UI Lengkap:**
  - `Screen 01` Homepage (Hero, Koleksi Kategori, Best Seller, Cara Pesan, Testimoni, Footer).
  - `Screen 02` Katalog & Koleksi (Filter samping kategori, rentang harga, acara, tanggal ketersediaan, urutkan, dan paginasi).
  - `Screen 03` Detail Produk (Galeri thumbnail, varian ukuran interaktif dengan update harga dinamis via Alpine.js, kartu ucapan kustom maks. 40 karakter, pemilih tanggal & slot waktu *fresh bake* dengan kuota real-time, tab porsi/alergen/daya tahan).
  - `Screen 04` Keranjang Belanja (Berbasis session, penyesuaian kuantitas, catatan khusus, subtotal akurat).
  - `Screen 05` Checkout (Validasi FormRequest ketat, pilihan Delivery vs Pick-up, perhitungan ongkir per kecamatan Makassar, kode promo server-calculated, pencegahan manipulasi harga klien).
  - `Screen 06` Pembayaran (Midtrans Core API untuk QRIS, GoPay, ShopeePay, BCA VA, hitung mundur batas waktu 1 jam, tombol cek status pembayaran idempoten, fallback Snap).
  - `Screen 07` Konfirmasi & Lacak Pesanan (Timeline progres pesanan multi-tahap, rincian pengiriman, tautan WhatsApp konfirmasi otomatis, pembatalan otomatis jika kedaluwarsa).
  - `Screen 08` Admin Panel `/admin` (Filament v5: Dashboard metrik & tren penjualan, Kalender Kuota Fresh Bake 14-hari interaktif, Order Resource dengan workflow status sekuensial, Catalog & Variant Manager, Delivery Zones, Promo Codes).

---

## 2. Prasyarat Sistem

- **OS:** Windows 10/11 (atau Linux/macOS)
- **Web Server & Database:** XAMPP dengan Apache & MySQL / MariaDB (InnoDB, utf8mb4)
- **PHP:** Versi 8.2+ dengan ekstensi aktif:
  - `php_intl` *(wajib untuk Filament v5)*
  - `php_pdo_mysql`
  - `php_mbstring`
  - `php_openssl`
  - `php_curl`
- **Node.js:** Versi 18+ & NPM
- **Composer:** Versi 2+

---

## 3. Panduan Instalasi & Menjalankan di XAMPP

### Langkah 1: Kloning & Masuk ke Direktori Proyek
```powershell
cd "c:\Users\ASUS\Documents\Klik.Cake E-Commerce"
```

### Langkah 2: Aktifkan Ekstensi PHP `intl` di XAMPP
Buka file `C:\xampp\php\php.ini`, cari baris:
```ini
;extension=intl
```
Hapus tanda titik koma (`;`) di depannya sehingga menjadi:
```ini
extension=intl
```
Simpan file dan restart Apache jika sedang berjalan.

### Langkah 3: Konfigurasi Database MySQL
1. Buka XAMPP Control Panel dan nyalakan **MySQL** dan **Apache**.
2. Buat database baru bernama `klikcake` (dan `klikcake_test` untuk unit testing):
```sql
CREATE DATABASE klikcake CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE klikcake_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Langkah 4: Setup File Lingkungan (`.env`)
Salin file `.env.example` menjadi `.env`:
```powershell
cp .env.example .env
```
Pastikan pengaturan koneksi database dan kredensial Midtrans Sandbox terisi:
```env
APP_NAME="Klik.Cake"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Makassar

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=klikcake
DB_USERNAME=root
DB_PASSWORD=

MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxx
MIDTRANS_IS_PRODUCTION=false
WA_NUMBER=6281234567890
```

### Langkah 5: Pasang Dependensi & Generate App Key
```powershell
$env:Path = "C:\xampp\php;C:\Program Files\nodejs;" + $env:Path
composer install
php artisan key:generate
npm install
npm run build
```

### Langkah 6: Jalankan Migrasi & Seeder Master Data
```powershell
php artisan migrate:fresh --seed
```
*Seeder akan mengisi katalog kue Makassar (Strawberry Cream Cake, Bolu Gulung Pandan, Chocolate Fudge, Red Velvet, dll.), zona pengiriman Makassar, kode promo (`KLIKMANIS`, `FRESHBAKE`), dan kuota slot waktu 14 hari ke depan.*

### Langkah 7: Buat Akun Administrator Filament
Jalankan perintah interaktif Filament untuk membuat akun admin pertama:
```powershell
php artisan make:filament-user
```
Masukkan nama, email (misal: `admin@klikcake.com`), dan password Anda.

### Langkah 8: Jalankan Server Lokal
```powershell
php artisan serve --host=127.0.0.1 --port=8000
```
- Toko Pelanggan: [http://127.0.0.1:8000](http://127.0.0.1:8000)
- Panel Admin: [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin)

---

## 4. Alur Demo 5 Menit (Panduan Evaluasi)

Ikuti skenario berikut untuk menguji seluruh sistem dari hulu ke hilir dalam 5 menit:

1. **Jelajahi Homepage (Screen 01):**
   - Buka `http://127.0.0.1:8000`. Perhatikan nuansa pastel hangat (*Strawberry, Cream, Blush, Butter, Cocoa*), banner Fresh Bake, 4 kategori, dan Best Seller.
   - Klik kartu produk unggulan **"Strawberry Cream Cake"**.

2. **Kustomisasi Detail Produk (Screen 03):**
   - Pilih ukuran cake (misal: **20 cm**). Harga akan terbarukan secara otomatis menjadi Rp245.000 via Alpine.js.
   - Ketik ucapan di atas kue (misal: `"Selamat Ulang Tahun Ibu Tercinta"`). Penghitung karakter akan menampilkan sisa karakter dari batas 40.
   - Pilih tanggal pengantaran (besok/lusa) dan slot jam (misal: *Pagi 09:00 - 12:00*).
   - Klik **"Tambah ke Keranjang"**.

3. **Keranjang & Checkout (Screen 04 & 05):**
   - Di halaman keranjang, periksa item lalu klik **"Lanjut ke Checkout"**.
   - Isi formulir pemesan: Nama, WhatsApp (`0812xxxxxxxx`), pilih metode **Delivery**, dan pilih Kecamatan (misal: *Panakkukang*). Biaya ongkir Rp15.000 otomatis ditambahkan.
   - Masukkan kode promo `KLIKMANIS` lalu klik **Terapkan**. Diskon 10% langsung dihitung oleh server.
   - Klik **"Bayar Sekarang"**.

4. **Simulasi Pembayaran Midtrans (Screen 06):**
   - Sistem mengarahkan ke halaman pembayaran dengan timer 1 jam.
   - Metode QRIS / Virtual Account BCA ditampilkan dengan QR Code dinamis atau nomor VA.
   - Klik tombol **"Saya Sudah Bayar"**. Sistem melakukan query status real-time ke Midtrans.
   - *(Opsional: Buka Midtrans Simulator atau trigger webhook untuk mengubah status menjadi terverifikasi).*

5. **Lacak Pesanan (Screen 07):**
   - Akses `http://127.0.0.1:8000/lacak` atau klik lacak pesanan. Masukkan nomor pesanan Anda.
   - Timeline progres pesanan menampilkan status aktual (*Menunggu Pembayaran / Pembayaran Terverifikasi / Dipanggang / Siap / Selesai*).
   - Klik tombol konfirmasi WhatsApp untuk mencoba tautan `wa.me` yang sudah terisi template pesan otomatis.

6. **Operasional Admin di Panel Filament (Screen 08):**
   - Masuk ke `http://127.0.0.1:8000/admin` menggunakan akun admin Anda.
   - Buka **Dashboard**: Pantau kartu statistik pesanan baru, omset hari ini, dan produk terlaris.
   - Buka **Kalender Fresh Bake**: Lihat grid 14 hari kuota Pagi & Sore. Coba ubah kuota slot langsung dari tabel inline.
   - Buka **Pesanan (Orders)**: Buka detail pesanan yang baru dibuat, gunakan aksi **Ubah Status Pesanan** atau **Verifikasi Pembayaran Manual**.
   - Buka kembali halaman Lacak Pelanggan di tab baru: status pesanan langsung berubah secara real-time.

---

## 5. Fitur Nyata (Real) vs Sederhana (Simplified)

| Komponen | Status Implementasi | Catatan Arsitektur |
| :--- | :--- | :--- |
| **Penyimpanan Database** | 🟢 **100% Real (MySQL/MariaDB)** | Skema InnoDB relasional lengkap, strict foreign keys, indexing, dan soft-lock concurrency. |
| **Reservasi Kuota & Stok** | 🟢 **100% Real (Atomic Updates)** | Menggunakan query atomik berkuota (`booked_count < max_capacity`) dan *pessimistic locking* (`lockForUpdate`). |
| **Payment Gateway** | 🟢 **100% Real (Midtrans Core API & Snap)** | Transaksi Midtrans nyata via Sandbox API; mendukung QRIS, GoPay, ShopeePay, dan BCA VA. |
| **Verifikasi Webhook** | 🟢 **100% Real (SHA512 Signature)** | `signature_key` diverifikasi dengan SHA512, pencocokan nominal `gross_amount`, serta pemrosesan idempoten. |
| **Manajemen Admin** | 🟢 **100% Real (Filament v5)** | Autentikasi sesi mandiri, otorisasi di server, auditing log status (`OrderStatusLog`). |
| **Perhitungan Harga Server** | 🟢 **100% Real (Anti-Tamper)** | Input harga dan diskon dari browser diabaikan sepenuhnya; server menghitung ulang dari DB. |
| **Notifikasi WhatsApp** | 🟡 **Sederhana (Deep Link `wa.me`)** | Menggunakan format link dinamis `https://wa.me/{nomor}?text=...`. Untuk produksi dapat dihubungkan ke WhatsApp Business Cloud API (Meta/Twilio/Fonnte). |
| **Pengiriman Email** | 🟡 **Sederhana (Log Driver / Opsional)** | Kolom email bersifat opsional bagi pelanggan; driver email default Laravel siap dihubungkan ke SMTP/Resend/Mailgun. |

---

## 6. Panduan Kesiapan Produksi (Deployment)

### A. Rekomendasi Lingkungan Hosting
XAMPP ditujukan khusus untuk **lingkungan pengembangan lokal**. Untuk lingkungan produksi publik, gunakan:
1. **Cloud VPS (Linux Ubuntu 22.04 / 24.04 LTS atau Debian):** Nginx + PHP-FPM 8.2+ + MySQL 8 / MariaDB (Direkomendasikan untuk performa optimal dan isolasi daemon).
2. **Platform-as-a-Service (PaaS):** AWS, Google Cloud Run / Compute Engine, DigitalOcean App Platform, atau Laravel Cloud.
3. **Managed Web Hosting / cPanel:** Hosting yang menyediakan PHP 8.2+, ekstensi `intl`, akses terminal SSH/CLI, serta cron job scheduler.

### B. Konfigurasi Variabel Lingkungan Produksi (`.env`)
Di server produksi, buat file `.env` mandiri yang terpisah:

```env
APP_NAME="Klik.Cake"
APP_ENV=production
APP_KEY=base64:GENERATE_KEY_BARU_DI_SERVER
APP_DEBUG=false
APP_URL=https://klikcake.com
APP_TIMEZONE=Asia/Makassar

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=klikcake_production
DB_USERNAME=klikcake_user
DB_PASSWORD=PASSWORD_DATABASE_KUAT_DAN_ACAK

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true

CACHE_STORE=database
QUEUE_CONNECTION=database

# Kredensial Midtrans Production
MIDTRANS_SERVER_KEY=Mid-server-KUNCI_PRODUKSI_ANDA
MIDTRANS_CLIENT_KEY=Mid-client-KUNCI_PRODUKSI_ANDA
MIDTRANS_IS_PRODUCTION=true

WA_NUMBER=6281234567890
```

### C. Eksekusi Migrasi Produksi (Tanpa Seeder Dev)
Jangan menjalankan seeder pengujian di database produksi. Jalankan hanya migrasi skema tabel:
```bash
php artisan migrate --force
```
Jika ingin menginisialisasi master data murni (zona pengiriman dan kategori awal tanpa pesanan dummy), buat seeder khusus master data (`MasterDataSeeder`) lalu jalankan:
```bash
php artisan db:seed --class=MasterDataSeeder --force
```

### D. Optimasi Cache Laravel di Server Produksi
Jalankan kompilasi cache untuk performa respons milidetik:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```
*Catatan: Setiap kali melakukan perubahan file konfigurasi atau rute di produksi, bersihkan dan buat ulang cache dengan `php artisan optimize`.*

### E. Mendaftarkan Webhook URL di Dashboard Midtrans Production
1. Masuk ke [Midtrans Production Dashboard](https://dashboard.midtrans.com/).
2. Buka menu **Settings** > **Configuration**.
3. Masukkan URL webhook HTTPS domain produksi Anda pada kolom **Payment Notification URL**:
   ```
   https://klikcake.com/webhooks/midtrans
   ```
4. Pastikan opsi status notifikasi aktif dan simpan pengaturan.

### F. Setup Laravel Task Scheduler di Server
Agar pesanan yang tidak dibayar dalam 1 jam otomatis dibatalkan (`orders:expire`), jalankan scheduler setiap menit:

- **Di Server Linux (Crontab):**
  Edit crontab dengan perintah `crontab -e`:
  ```cron
  * * * * * cd /path/to/klikcake && php artisan schedule:run >> /dev/null 2>&1
  ```

- **Di Windows Server / XAMPP (Task Scheduler):**
  ```powershell
  schtasks /create /tn "KlikCake_Scheduler" /tr "C:\xampp\php\php.exe \"C:\Users\ASUS\Documents\Klik.Cake E-Commerce\artisan\" schedule:run" /sc minute /mo 1 /ru "SYSTEM"
  ```

### G. Strategi Backup Database MySQL Berkala
Lakukan pencadangan database terjadwal menggunakan `mysqldump`:
```bash
# Backup manual
mysqldump -u klikcake_user -p --single-transaction --quick --lock-tables=false klikcake_production > backup_klikcake_$(date +%Y%m%d_%H%M%S).sql

# Atau buat skrip cron harian untuk menyimpan cadangan ke cloud storage/S3
```

---

## 7. Checklist Go-Live

Sebelum membuka akses toko ke pelanggan umum, pastikan seluruh daftar periksa berikut telah terpenuhi:

- [ ] **Domain & SSL:** Domain menggunakan sertifikat SSL aktif (`https://`) dan seluruh rute otomatis redirect ke HTTPS.
- [ ] **Mode Produksi:** `APP_ENV=production` dan `APP_DEBUG=false` sudah aktif di `.env`. Pastikan tidak ada pesan error debug Laravel yang bocor ke publik.
- [ ] **Kredensial Gateway:** Menggunakan `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` asli produksi (berawalan `Mid-`, bukan `SB-Mid-`). Nilai `MIDTRANS_IS_PRODUCTION=true`.
- [ ] **Pendaftaran Webhook:** URL `https://namadomain.com/webhooks/midtrans` telah terdaftar dan diverifikasi di dashboard Midtrans Production.
- [ ] **Admin Superuser:** Akun admin produksi telah dibuat dengan password unik dan kuat via `php artisan make:filament-user`.
- [ ] **Cache Terkompilasi:** `php artisan optimize` telah dijalankan.
- [ ] **Scheduler Berjalan:** Cron job atau background task `schedule:run` terbukti aktif mengeksekusi `orders:expire` setiap menit.
- [ ] **Uji Transaksi Nyata:** Lakukan 1 transaksi riil dengan nominal kecil (misalnya produk uji seharga Rp10.000) menggunakan QRIS/Gopay sungguhan untuk memverifikasi aliran dana, respon webhook callback, dan perubahan status order.

---

## 8. Pengujian Otomatis (Testing)

Proyek ini dilengkapi dengan rangkaian pengujian komprehensif (Unit & Feature Test) yang mencakup logika bisnis harga, pencegahan manipulasi harga, reservasi kuota konkuren, batas waktu kadaluwarsa, webhook signature verification, otorisasi admin, dan alur checkout.

Rangkaian tes berjalan di database terpisah (`klikcake_test`) sesuai pengaturan `phpunit.xml`:

```powershell
$env:Path = "C:\xampp\php;C:\Program Files\nodejs;" + $env:Path
php artisan test
```

### Ringkasan Status Test Terkini:
```text
Pass: 37 passed (211 assertions)
Execution Time: ~5.6s
Status: 100% Green
```
Semua build frontend Vite:
```powershell
npm run build
# Status: 0 errors, compiled in ~1.5s
```
#   k l i k . c a k e  
 