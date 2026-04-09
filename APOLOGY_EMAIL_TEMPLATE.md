PERMOHONAN MAAF - EMAIL TEMPLATE UNTUK CPANEL
===============================================

PANDUAN PENGGUNAAN:
1. Salin email addresses dari emails.txt ke bidang "To Addresses"
2. Gunakan HTML Template di bawah ini untuk body email
3. Ganti {{REREGISTER_URL}} dengan link format: https://yourdomain.com/checkout/reregister/TOKEN
   (Contoh: https://lariyuk.com/checkout/reregister/abc123def456)

=====================================
SUBJECT: 🙏 Permohonan Maaf - Sistem Error Registrasi
=====================================

EMAIL HEADER (Text/Plain):
---------------------------------------------

Halo,

Kami dengan sangat menyesal harus memberitahukan bahwa terjadi kesalahan sistem pada platform pendaftaran kami yang menyebabkan data registrasi Anda untuk beberapa event telah terhapus tanpa sengaja.

=====================================
HTML TEMPLATE (Gunakan ini di cPanel):
=====================================

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Maaf - Sistem Error Registrasi</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9fafb;
        }
        .email-wrapper {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            font-size: 18px;
            color: #1f2937;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .section p {
            margin: 0 0 12px 0;
            color: #4b5563;
            font-size: 14px;
            line-height: 1.6;
        }
        .highlight-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .highlight-box strong {
            color: #92400e;
        }
        .button-wrapper {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
        }
        .button:hover {
            background-color: #059669;
        }
        .url-box {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 12px;
            margin: 15px 0;
            border-radius: 6px;
            word-break: break-all;
            font-size: 12px;
            font-family: monospace;
            color: #374151;
        }
        .warning-box {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .warning-box strong {
            color: #7f1d1d;
        }
        .warning-box li {
            margin: 5px 0;
            color: #7f1d1d;
            font-size: 13px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-wrapper">
            <!-- Header -->
            <div class="header">
                <h1>🙏 Permohonan Maaf</h1>
                <p>Sistem Error Registrasi</p>
            </div>

            <!-- Content -->
            <div class="content">
                <p>Halo,</p>

                <p>Kami dengan sangat menyesal harus memberitahukan bahwa terjadi <strong>kesalahan sistem</strong> pada platform pendaftaran kami yang menyebabkan data registrasi Anda untuk beberapa event <strong>terhapus tanpa sengaja</strong>.</p>

                <!-- What Happened -->
                <div class="section">
                    <h2>📋 Apa yang terjadi?</h2>
                    <p>Data registrasi dan pembayaran Anda telah terhapus karena kesalahan teknis dalam sistem kami. <strong>Pembayaran Anda telah kami verifikasi dan masih valid</strong>, sehingga Anda dapat melakukan registrasi ulang tanpa perlu membayar kembali.</p>
                </div>

                <div class="highlight-box">
                    <strong>✅ Kabar baik:</strong> Pembayaran Anda masih tercatat dan terverifikasi. Anda tidak perlu membayar lagi!
                </div>

                <!-- Action Required -->
                <div class="section">
                    <h2>✍️ Apa yang perlu Anda lakukan?</h2>
                    <p>Kami telah menyediakan link khusus untuk Anda melakukan registrasi ulang <strong>hanya sekali</strong> dengan data yang sama. Setelah itu, data Anda akan aman dan sistem tidak akan menghapus lagi.</p>
                </div>

                <!-- CTA Button -->
                <div class="button-wrapper">
                    <a href="{{REREGISTER_URL}}" class="button">Klik di sini untuk Re-register</a>
                </div>

                <!-- URL Backup -->
                <p style="text-align: center; font-size: 13px; color: #6b7280; margin-top: 15px;">Atau salin-tempel link ini di browser:</p>
                <div class="url-box">{{REREGISTER_URL}}</div>

                <!-- Warnings -->
                <div class="warning-box">
                    <strong>⚠️ Perhatian Penting:</strong>
                    <ul>
                        <li><strong>Link ini hanya berlaku sekali</strong> dan akan otomatis tidak aktif setelah digunakan</li>
                        <li>Link ini berlaku selama <strong>7 hari</strong></li>
                        <li>Sebaiknya gunakan email yang sama saat menerima link ini</li>
                        <li>Pastikan data yang Anda masukkan sudah <strong>benar</strong> sebelum submit</li>
                    </ul>
                </div>

                <!-- Support -->
                <div class="section">
                    <h2>💬 Butuh bantuan?</h2>
                    <p>Jika Anda mengalami kendala atau pertanyaan, silakan hubungi tim support kami melalui:</p>
                    <p style="font-size: 13px;">
                        📱 WhatsApp: <strong>[Nomor WhatsApp Anda]</strong><br>
                        📧 Email: <strong>[Email Support Anda]</strong><br>
                        ⏰ Jam Operasional: [Jam Operasional Anda]
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p><strong>Kami sangat minta maaf atas ketidaknyamanan ini</strong></p>
                <p>dan berterima kasih atas kesabaran Anda.</p>
                <p style="margin-top: 15px; color: #9ca3af;">
                    <strong>Tim Panitia</strong>
                </p>
            </div>
        </div>
    </div>
</body>
</html>

=====================================
LANGKAH-LANGKAH CPANEL:
=====================================

1. GENERATE TOKENS (Di PHP CLI atau Artisan):
   
   Jalankan command ini dari folder project:
   ```
   php artisan tinker
   ```
   
   Kemudian copy-paste kode berikut untuk setiap email:
   
   ```php
   use App\Models\ApologyToken;
   
   // Generate token untuk satu email
   $token = \Str::random(32);
   ApologyToken::create([
       'email' => 'email@example.com',
       'token' => $token,
       'expires_at' => now()->addDays(7),
   ]);
   
   echo urldecode(sprintf("https://yourdomain.com/checkout/reregister/%s", $token));
   ```

2. ATAU JIKA ANDA INGIN BATCH (from emails.txt):
   
   ```php
   use App\Models\ApologyToken;
   
   $emails = [
       'email1@example.com',
       'email2@example.com',
       // ... tulis semua email dari emails.txt
   ];
   
   foreach ($emails as $email) {
       $token = \Str::random(32);
       ApologyToken::create([
           'email' => $email,
           'token' => $token,
           'expires_at' => now()->addDays(7),
       ]);
       echo "[OK] $email - Token: $token\n";
   }
   ```

3. KIRIM EMAIL DARI CPANEL:
   - Buka Email Accounts > Email Marketing (atau Mail > Forwarders)
   - Gunakan "Mass Mail" atau feature serupa
   - Paste HTML template di atas
   - Ganti {{REREGISTER_URL}} dengan URL lengkap dari step #1
   - Ganti [Nomor WhatsApp Anda], [Email Support], dll dengan data Anda

4. ALTERNATIF: GUNAKAN LARAVEL QUEUE
   
   Buat Command di app/Console/Commands/SendApologyEmails.php:
   
   ```php
   php artisan make:command SendApologyEmails
   ```
   
   Kemudian modify command untuk loop emails dan send:
   ```php
   Mail::to($email)->queue(new ApologyEmail($email, $token));
   ```

=====================================
NOTES:
=====================================

- Pastikan database migration sudah dijalankan: php artisan migrate
- Users akan lihat custom re-register form untuk input ulang data
- Registered counter di Event table TIDAK akan increment (sesuai requirement)
- Setiap token hanya bisa digunakan sekali
- Token valid selama 7 hari

=====================================
