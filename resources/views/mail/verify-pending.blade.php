@component('mail::message')
# Verifikasi Email

Hai {{ $pending->name ?? 'Pengguna' }},

Klik tombol di bawah untuk menyelesaikan pendaftaran.

@component('mail::button', ['url' => $signedUrl])
Verifikasi & Aktifkan Akun
@endcomponent

Link ini akan kedaluwarsa dalam 60 menit.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
