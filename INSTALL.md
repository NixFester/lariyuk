# 🏃 LariYuk – Panduan Instalasi Laravel

## Prasyarat

| Software | Versi Minimum | Cek |
|---|---|---|
| PHP | 8.2+ | `php -v` |
| Composer | 2.x | `composer -V` |
| MySQL / MariaDB | 8.0+ | `mysql --version` |
| Node.js (opsional) | 18+ | `node -v` |

---

## Langkah 1 — Buat Project Laravel Baru

```bash
composer create-project laravel/laravel lariyuk
cd lariyuk
```

---

## Langkah 2 — Salin File Proyek Ini

Salin semua file dari folder ini ke dalam project Laravel yang baru dibuat:

```
lariyuk/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php
│   │   │   ├── EventController.php
│   │   │   ├── RegistrationController.php
│   │   │   └── Admin/
│   │   │       ├── AuthController.php
│   │   │       ├── EventController.php
│   │   │       └── RegistrationController.php
│   │   └── Middleware/
│   │       └── AdminMiddleware.php
│   └── Models/
│       ├── Admin.php
│       ├── Event.php
│       ├── EventCategory.php
│       ├── EventHighlight.php
│       └── Registration.php
├── database/
│   ├── migrations/  (3 file migration)
│   └── seeders/
│       └── DatabaseSeeder.php
├── resources/views/
│   ├── layouts/
│   │   ├── app.blade.php
│   │   └── admin.blade.php
│   ├── home.blade.php
│   ├── events/
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   ├── checkout/
│   │   ├── show.blade.php
│   │   └── success.blade.php
│   └── admin/
│       ├── login.blade.php
│       ├── dashboard.blade.php
│       ├── events/
│       │   ├── index.blade.php
│       │   └── form.blade.php
│       └── registrations/
│           ├── index.blade.php
│           └── show.blade.php
├── routes/
│   └── web.php
└── config/
    └── auth.php  (ganti file aslinya)
```

---

## Langkah 3 — Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Buka `.env` dan isi:

```env
APP_NAME=LariYuk
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lariyuk
DB_USERNAME=root
DB_PASSWORD=password_anda
```

---

## Langkah 4 — Buat Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE lariyuk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

---

## Langkah 5 — Daftarkan Middleware Admin

Buka `bootstrap/app.php` dan tambahkan alias middleware. Ganti isinya dengan file `bootstrap_app_snippet.php` yang sudah disediakan, **atau** tambahkan baris ini:

```php
// Di dalam ->withMiddleware(function (Middleware $middleware) {
$middleware->alias([
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
]);
```

---

## Langkah 6 — Jalankan Migration & Seeder

```bash
php artisan migrate
php artisan db:seed
```

Seeder akan membuat:
- ✅ Admin: `admin@lariyuk.id` / `password123`
- ✅ 3 sample event dengan kategori & harga
- ✅ Harga early bird otomatis (–10%)

---

## Langkah 7 — Storage untuk Upload Foto

```bash
php artisan storage:link
```

---

## Langkah 8 — Jalankan Server

```bash
php artisan serve
```

Buka browser:

| URL | Keterangan |
|---|---|
| `http://localhost:8000` | Halaman utama / event |
| `http://localhost:8000/admin/login` | Login admin |
| `http://localhost:8000/admin/dashboard` | Dashboard admin |

---

## Fitur Lengkap

### 🎽 BIB Nomor Dada
- **No. KTP** dan **Nama Peserta** dipisah menjadi field berbeda
- No. KTP hanya untuk verifikasi identitas (tidak dicetak di BIB)
- Nama Peserta tampil di BIB, format: `5K-0042`

### 👕 Ukuran Kaos
- Chart ukuran kaos tersedia di halaman detail event (tabel XS–XXL)
- Di form checkout ada accordion untuk referensi cepat

### 🩸 Golongan Darah
- Pilihan: A, B, AB, O (dengan sub ± positif/negatif)
- Tersimpan di database & tampil di export

### 📊 Export Spreadsheet
- Admin → Pendaftar → tombol **Export ke Spreadsheet**
- Output: file `.csv` UTF-8 (dengan BOM untuk Excel)
- Buka langsung di Excel, Google Sheets, atau LibreOffice

### 🐦 Early Bird (–10%)
- Atur tanggal berakhir early bird di form admin event
- Harga early bird = harga normal × 0.90 (dihitung otomatis)
- Badge "Early Bird" muncul di kartu event & halaman detail
- Harga normal dicoret, harga EB ditampilkan berwarna kuning

### 🔐 Admin Panel
Admin dapat:
- **Tambah / Edit / Hapus** event (termasuk foto, deskripsi, harga)
- **Kelola kategori** per event (nama + harga)
- **Kelola highlights** (poin keuntungan peserta)
- **Lihat & hapus** data pendaftar
- **Export CSV** semua pendaftar (filter per event & status)

---

## Perintah Berguna

```bash
# Reset & isi ulang database
php artisan migrate:fresh --seed

# Lihat semua route
php artisan route:list

# Clear semua cache
php artisan optimize:clear
```

---

## Integrasi Pembayaran (Xendit)

Saat ini pembayaran masimock. Untuk integrasi nyata:

1. Daftar di [xendit.co](https://xendit.co) dan ambil API key
2. Install SDK: `composer require xendit/xendit-php`
3. Di `RegistrationController::store()`, ganti bagian `payment_status => 'paid'` dengan call ke Xendit Invoice API
4. Buat webhook route untuk menerima konfirmasi pembayaran dari Xendit


---

## 📧 Setup Email Tiket (Test Mode)

Secara default email menggunakan driver `log` — tiket "dikirim" ke file log, tidak ke inbox sungguhan.

### Cara melihat tiket di log:
```bash
tail -f storage/logs/laravel.log
```
Cari baris `[App\Mail\TicketMail]` untuk melihat isi HTML tiket.

### Cara preview tiket di browser (tanpa kirim email):
Tambahkan route sementara di `routes/web.php`:
```php
// DEVELOPMENT ONLY — hapus sebelum production
Route::get('/dev/ticket/{invoice}', function ($invoice) {
    $reg = \App\Models\Registration::with(['event','category'])
           ->where('invoice_number', $invoice)->firstOrFail();
    return new \App\Mail\TicketMail($reg);
});
```
Buka: `http://localhost:8000/dev/ticket/INV-XXXXXXXX-XXXXXX`

### Cara kirim ke email sungguhan:
Edit `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=emailkamu@gmail.com
MAIL_PASSWORD=app_password_gmail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@lariyuk.id
MAIL_FROM_NAME="LariYuk"
```
Untuk testing gratis, gunakan [Mailtrap.io](https://mailtrap.io):
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=username_mailtrap
MAIL_PASSWORD=password_mailtrap
```
