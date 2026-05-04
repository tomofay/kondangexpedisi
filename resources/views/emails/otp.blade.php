<x-mail::message>
# Verifikasi Keamanan

Halo,

Kami menerima permintaan untuk melakukan **{{ $type === 'password' ? 'Perubahan Password' : 'Perubahan Email' }}** pada akun Kondang Ekspedisi Anda.

Silakan gunakan kode OTP di bawah ini untuk melanjutkan proses verifikasi:

<x-mail::panel>
# {{ $code }}
</x-mail::panel>

Kode ini akan kedaluwarsa dalam **10 menit**. Jangan berikan kode ini kepada siapa pun, termasuk pihak Kondang Ekspedisi.

Jika Anda tidak merasa melakukan permintaan ini, silakan abaikan email ini atau hubungi tim dukungan kami jika Anda merasa ada aktivitas mencurigakan.

Terima kasih,<br>
**Tim Kondang Ekspedisi**
</x-mail::message>
