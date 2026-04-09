@component('mail::message')
# 🙏 Permohonan Maaf - Sistem Error Registrasi

Halo {{ $email }},

Kami dengan sangat menyesal harus memberitahukan bahwa terjadi **kesalahan sistem** pada platform pendaftaran kami yang menyebabkan data registrasi Anda untuk beberapa event **terhapus tanpa sengaja**.

## Apa yang terjadi?
Data registrasi dan pembayaran Anda telah dihapus karena kesalahan teknis dalam sistem kami. **Pembayaran Anda telah kami verifikasi dan masih valid**, sehingga Anda dapat melakukan registrasi ulang tanpa perlu membayar kembali.

## Apa yang perlu Anda lakukan?
Kami telah menyediakan link khusus untuk Anda melakukan registrasi ulang **hanya sekali** dengan data yang sama. Setelah itu, data Anda akan aman dan sistem tidak akan menghapus lagi.

@component('mail::button', ['url' => $reregisterUrl, 'color' => 'success'])
Klik di sini untuk Re-register (Registrasi Ulang)
@endcomponent

Atau salin-tempel link ini di browser Anda:
{{ $reregisterUrl }}

## Perhatian
- Link ini **hanya berlaku sekali** dan akan otomatis tidak aktif setelah digunakan
- Link ini berlaku selama **7 hari**
- Sebaiknya gunakan email yang sama ({{ $email }})

## Bantuan
Jika Anda mengalami kendala atau pertanyaan, silakan hubungi tim support kami melalui WhatsApp atau email.

Kami sangat minta maaf atas ketidaknyamanan ini dan berterima kasih atas kesabaran Anda.

**Tim Panitia**
@endcomponent
