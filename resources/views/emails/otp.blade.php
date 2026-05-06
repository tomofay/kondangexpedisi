<x-mail::message>
# Konfirmasi Keamanan Akun

Halo,

@php
    $action = match($type) {
        'password' => 'melakukan perubahan kata sandi',
        'registration' => 'memverifikasi pendaftaran akun baru',
        default => 'melakukan perubahan alamat email',
    };
@endphp

Kami menerima permintaan untuk **{{ $action }}** pada sistem Kondang Ekspedisi. Untuk menjaga keamanan akun Anda, silakan gunakan kode verifikasi di bawah ini:

<x-mail::panel>
<div style="text-align: center; letter-spacing: 5px;">
{{ $code }}
</div>
</x-mail::panel>

Kode ini bersifat rahasia dan akan kedaluwarsa dalam **10 menit**. Mohon tidak memberikan kode ini kepada siapapun demi keamanan data Anda.

Jika Anda tidak merasa melakukan permintaan ini, Anda dapat mengabaikan email ini dengan aman. Keamanan akun Anda adalah prioritas kami.

Salam hangat,<br>
**Manajemen Kondang Ekspedisi**
</x-mail::message>
