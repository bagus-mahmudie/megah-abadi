<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Pending Registration TTL (minutes)
    |--------------------------------------------------------------------------
    | Lama waktu link verifikasi pending berlaku.
    | Atur via ENV: PENDING_TTL_MINUTES
    | Default: 60 (produksi). Saat testing bisa set 1.
    */
    'ttl_minutes' => (int) env('PENDING_TTL_MINUTES', 60),
];