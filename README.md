# Restikey — Sistem Booking Wedding Organizer

Aplikasi web untuk **Restikey Wedding Organizer**. Calon pengantin melihat paket & portofolio, mengecek ketersediaan tanggal, melakukan booking, membayar DP/pelunasan lewat transfer manual, lalu memberi ulasan. Admin mengelola paket, add-on, portofolio, tanggal diblokir, booking, verifikasi pembayaran, ulasan, dan laporan.

Dibangun dengan **Laravel 12, MySQL, Blade, dan Bootstrap 5**, tanpa Node.js/npm. Bootstrap, Bootstrap Icons, dan Chart.js disimpan langsung di `public/vendor/`, jadi tidak ada proses build front-end.

## Fitur

**Customer**
- Daftar/masuk, profil (nama, telepon, alamat, ganti kata sandi)
- Katalog paket & detail paket (fitur, add-on, portofolio, ulasan), galeri portofolio dengan lightbox
- Kalender ketersediaan tanggal per bulan
- Form booking dengan ringkasan harga real-time dan cek tanggal langsung
- Pembayaran manual: transfer ke rekening Restikey lalu unggah bukti (DP minimal 30%, lalu pelunasan)
- Riwayat & detail booking dengan timeline status, pembatalan beralasan, ulasan bintang 1–5
- Dashboard: hitung mundur hari H dan daftar tagihan

**Admin**
- Dashboard: ringkasan, grafik booking & pendapatan 12 bulan, acara terdekat, booking terbaru
- Konfirmasi / tolak / batalkan / tandai selesai booking
- Verifikasi atau tolak bukti pembayaran (pratinjau bukti di modal)
- CRUD paket (+ fitur), add-on, portofolio; blokir tanggal tunggal atau rentang
- Moderasi ulasan (tampil/sembunyikan/hapus)
- Laporan booking & pembayaran dengan ekspor **Excel** dan **PDF**

**Aturan bisnis yang dijaga sistem**
- Satu tanggal hanya untuk satu booking `confirmed`/`paid`/`completed`; booking `pending` tidak mengunci tanggal
- Konfirmasi memakai `DB::transaction` + `lockForUpdate`; booking pending lain di tanggal yang sama otomatis dibatalkan
- Tanggal acara minimal H+30, DP minimal 30%, pelunasan paling lambat H-7
- Booking otomatis **lunas** saat total pembayaran terverifikasi ≥ total harga
- Booking confirmed tanpa DP terverifikasi dibatalkan otomatis setelah 3 hari (scheduler)
- Semua angka di atas diatur di `config/restikey.php` / `.env`, bukan di kode

## Kebutuhan sistem

| Komponen | Versi |
|---|---|
| PHP | 8.2 atau lebih baru, dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `zip`, `xml`, `dom` |
| MySQL / MariaDB | MySQL 8 / MariaDB 10.4+, **engine InnoDB** (sudah dipaksa di `config/database.php`) |
| Composer | 2.x |
| Node.js | **Tidak diperlukan** |

## Instalasi

```bash
# 1. Dependensi PHP
composer install

# 2. Konfigurasi
cp .env.example .env
php artisan key:generate
#    lalu sesuaikan DB_*, MAIL_*, dan RESTIKEY_* (rekening) di .env

# 3. Database (buat dulu database kosong "restikey", utf8mb4)
php artisan migrate --seed

# 4. Symlink storage untuk foto paket & portofolio
php artisan storage:link
```

Jalankan untuk development (tiga terminal):

```bash
php artisan serve          # http://localhost:8000
php artisan queue:work     # mengirim email (antrean)
php artisan schedule:work  # menjalankan auto-cancel harian
```

Tanpa `queue:work`, email tetap tersimpan di tabel `jobs` dan terkirim begitu worker dijalankan. Dengan `MAIL_MAILER=log`, isi email bisa dilihat di `storage/logs/laravel.log`.

## Akun demo (dari seeder)

| Peran | Email | Kata sandi |
|---|---|---|
| Admin | `admin@restikey.com` | `password` |
| Customer | `customer@restikey.com` | `password` |

Seeder juga membuat 4 customer lain, 3 paket (Silver, Gold, Platinum), 6 add-on, 8 portofolio, 5 tanggal diblokir, dan 10 booking dengan berbagai status beserta pembayarannya. Foto demo diambil dari picsum.photos (butuh internet). Ulangi data demo kapan saja dengan `php artisan migrate:fresh --seed`.

## Perintah penting

| Perintah | Fungsi |
|---|---|
| `php artisan queue:work` | Memproses antrean email |
| `php artisan schedule:work` | Menjalankan scheduler (development) |
| `php artisan bookings:auto-cancel` | Membatalkan booking confirmed yang lewat batas DP (dijadwalkan tiap hari 01.00) |
| `php artisan test` | Menjalankan seluruh test |

### Menjalankan test

Test memakai database MySQL terpisah agar data development tidak terhapus:

```sql
CREATE DATABASE restikey_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan test
```

## Struktur folder utama

```
app/
├── Exports/ReportExport.php        Ekspor Excel dari view Blade
├── Http/
│   ├── Controllers/                Publik & customer
│   │   └── Admin/                  Panel admin
│   ├── Middleware/EnsureUserIsAdmin.php
│   └── Requests/                   Validasi (Form Request), termasuk Admin/
├── Mail/                           Email antrean (booking & pembayaran)
├── Models/                         Eloquent + konstanta status
│   └── Concerns/DeletesReplacedFiles.php   Hapus file lama saat gambar diganti
├── Policies/BookingPolicy.php      Otorisasi view/cancel/pay/review
├── Rules/AvailableEventDate.php
└── Services/                       Logika bisnis
    ├── AvailabilityService.php     Status tanggal & kalender
    ├── BookingService.php          Buat/konfirmasi/batal/selesai (transaction + lock)
    ├── PaymentService.php          Kirim bukti, verifikasi, tolak
    └── ReportService.php           Query laporan & grafik
config/restikey.php                 Semua angka aturan bisnis & info rekening
lang/id/, lang/id.json              Bahasa Indonesia
public/
├── css/app.css                     Tema Restikey (CSS biasa, tanpa build)
├── js/                             JS per modul (booking-form, lightbox, countdown, ...)
└── vendor/                         Bootstrap 5.3, Bootstrap Icons, Chart.js
resources/views/
├── layouts/app.blade.php           Layout publik & customer
├── layouts/admin.blade.php         Layout admin (sidebar)
├── components/                     x-alert, x-badge-status, x-card, x-confirm-form
└── mail/                           Template email (markdown)
routes/web.php                      Semua route web (+ JSON /api/availability)
routes/console.php                  Command auto-cancel & jadwalnya
```

## Checklist deploy

### 1. Siapkan kode & konfigurasi

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate
```

Di `.env` production:
- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-anda`, `LOG_LEVEL=error`
- `DB_*` ke database production
- `MAIL_*` ke SMTP sungguhan, `MAIL_FROM_ADDRESS` alamat domain Anda
- `RESTIKEY_BANK_*` dengan rekening asli

Tidak ada `npm run build`: aset front-end sudah ada di `public/`.

### 2. Database, storage, cache

```bash
php artisan migrate --force
php artisan db:seed --class=UserSeeder --force   # opsional, lalu segera ganti kata sandi admin
php artisan storage:link
php artisan optimize          # cache config, route, view, event
```

Ulangi `php artisan optimize` setiap kali `.env` atau kode diubah (atau `php artisan optimize:clear` untuk membersihkan).

### 3. Hak akses folder

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 4. Web server

Arahkan **document root ke folder `public/`**, bukan ke root proyek.
- **Shared hosting:** taruh proyek di luar `public_html`, lalu jadikan `public_html` berisi isi folder `public/` (sesuaikan path di `index.php`), atau ubah document root domain ke `.../restikey/public` lewat panel hosting.
- Jika hosting tidak mengizinkan symlink, `storage:link` gagal. Minta penyedia hosting mengaktifkannya atau buat symlink lewat cron/SSH sekali jalan.

### 5. Scheduler (cron)

Tambahkan satu baris cron (setiap menit):

```cron
* * * * * cd /path/ke/restikey && php artisan schedule:run >> /dev/null 2>&1
```

### 6. Queue worker

**VPS — Supervisor** (`/etc/supervisor/conf.d/restikey-worker.conf`):

```ini
[program:restikey-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/ke/restikey/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/ke/restikey/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start restikey-worker:*
```

Setiap deploy ulang, jalankan `php artisan queue:restart` agar worker memuat kode baru.

**Shared hosting tanpa Supervisor:** tambahkan cron yang memproses antrean lalu berhenti:

```cron
* * * * * cd /path/ke/restikey && php artisan queue:work --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

### 7. Cek akhir
- [ ] Buka situs, login admin, ganti kata sandi akun demo (atau hapus akun demo)
- [ ] Buat booking uji → email masuk → konfirmasi → unggah bukti → verifikasi
- [ ] `php artisan schedule:list` menampilkan `bookings:auto-cancel`
- [ ] HTTPS aktif; folder `storage/app/private` (bukti transfer) tidak bisa diakses langsung dari web
