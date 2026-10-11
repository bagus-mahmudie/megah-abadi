<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class BiteshipClient
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        // ambil langsung dari .env (punya kamu sudah benar)
        $this->apiKey  = config('services.biteship.api_key', env('BITESHIP_API_KEY'));
        $this->baseUrl = rtrim(config('services.biteship.base_url', env('BITESHIP_BASE_URL', 'https://api.biteship.com/v1')), '/');
    }

    /**
     * Cek tarif ongkir.
     *
     * @param array $payload sesuai docs Biteship /v1/rates/couriers
     */
    public function getRates(array $payload): array
    {
        return $this->request('POST', '/rates/couriers', $payload);
    }

        /**
     * Search area (provinsi/kota/kecamatan/kode pos) dari Biteship Maps API.
     *
     * @see https://biteship.com/id/docs/api/maps/search_area
     */
    public function searchAreas(string $input, string $type = 'multi', string $countries = 'ID'): array
{
    $payload = [
        'input'     => $input,
        'countries' => $countries,
        'type'      => $type,
    ];

    return $this->request('GET', '/maps/areas', $payload);
}



    /**
     * Membuat order pengiriman ke Biteship.
     */
    public function createOrder(array $payload): array
    {
        return $this->request('POST', '/orders', $payload);
    }

    /**
     * Ambil status tracking.
     */
    public function getTracking(string $courierTrackingCode): array
    {
        return $this->request('GET', "/trackings/{$courierTrackingCode}");
    }

    /**
     * Helper request ke Biteship (pakai cURL).
     */
    protected function request(string $method, string $uri, array $payload = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($uri, '/');

        Log::info('Biteship REQUEST', [
            'url'     => $url,
            'method'  => $method,
            'payload' => $payload,
        ]);

        try {

            // ⚡ BAGIAN BARU DIMULAI DI SINI
            $options = [];

            if (strtoupper($method) === 'GET') {
                // GET memakai query string
                $options['query'] = $payload;
            } else {
                // POST/PUT/PATCH tetap memakai JSON body
                $options['json'] = $payload;
            }

            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Accept'        => 'application/json',
            ])->send($method, $url, $options);
            // ⚡ BAGIAN BARU BERAKHIR DI SINI

            $body = $response->json();

            Log::info('Biteship RESPONSE', [
                'url'    => $url,
                'status' => $response->status(),
                'body'   => $body,
            ]);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'code'    => $response->status(),
                    'error'   => $body['error'] ?? $response->reason(),
                    'data'    => $body,
                ];
            }

            if (! is_array($body)) {
                $body = [];
            }

            $body['success'] = true;

            return $body;

        } catch (\Throwable $e) {
            Log::error('Biteship HTTP error', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'code'    => 500,
                'error'   => $e->getMessage(),
                'data'    => null,
            ];
        }
    }


    public function createShipmentFromOrder($order)
    {
        try {
            // 1. Alamat penerima
            $receiver = [
                'name'       => $order->receiver_name,
                'phone'      => $order->receiver_phone,
                'address'    => $order->receiver_address_line1,
                'postal_code'=> $order->receiver_postal_code,
            ];

            // 2. Barang
            $items = [];
            foreach ($order->items as $item) {
                $items[] = [
                    'name'     => $item->product_name,
                    'value'    => (float) $item->price,
                    'quantity' => (int) $item->qty,
                    'weight'   => (int) ($item->weight_gram ?: 200), // gram
                ];
            }

            // 3. Ambil courier_company & courier_type dari shipping_courier_code
            $courierCompany = null;
            $courierType    = null;

            if (!empty($order->shipping_courier_code)) {
                [$courierCompany, $courierType] = array_pad(
                    explode(':', $order->shipping_courier_code, 2),
                    2,
                    null
                );
            }

            if (!$courierCompany || !$courierType) {
                Log::error('Missing courier company/type for Biteship order', [
                    'order_id'             => $order->id,
                    'shipping_courier_code'=> $order->shipping_courier_code,
                ]);

                return null;
            }

            // 4. Payload ke Biteship (mengikuti dokumentasi resmi)
            $payload = [
                // shipper / origin
                'origin_contact_name'  => 'Admin Store',
                'origin_contact_phone' => '08123456789',
                'origin_address'       => 'Alamat toko kamu',
                'origin_postal_code'   => 11470, // TODO: ganti ke kode pos gudang

                // destination
                'destination_contact_name'  => $receiver['name'],
                'destination_contact_phone' => $receiver['phone'],
                'destination_address'       => $receiver['address'],
                'destination_postal_code'   => $receiver['postal_code'],

                // courier info (WAJIB)
                'courier_company' => $courierCompany, // contoh: "sicepat"
                'courier_type'    => $courierType,    // contoh: "reg"

                // pengiriman langsung
                'delivery_type'   => 'now',

                // optional
                'order_note'      => $order->customer_notes,
                'reference_id'    => $order->code,

                // items
                'items'           => $items,
            ];

            Log::info('Biteship REQUEST', [
                'url'     => $this->baseUrl . '/orders',
                'method'  => 'POST',
                'payload' => $payload,
            ]);

            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Accept'        => 'application/json',
            ])->post($this->baseUrl . '/orders', $payload);

            $body = $response->json();

            Log::info('Biteship RESPONSE', [
                'url'    => $this->baseUrl . '/orders',
                'status' => $response->status(),
                'body'   => $body,
            ]);

            if (!$response->successful() || !($body['success'] ?? false)) {
                Log::error('Biteship create order FAILED', [
                    'order_id' => $order->id,
                    'code'     => $response->status(),
                    'body'     => $body,
                ]);

                return null;
            }

            // success -> balikin response untuk disimpan di tabel shipments
            return $body;

        } catch (\Throwable $e) {
            Log::error('Failed create shipment on Biteship', [
                'order_id' => $order->id ?? null,
                'error'    => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function getTrackingById(string $trackingId): array
    {
        return $this->request('GET', "/trackings/{$trackingId}");
    }

    public function getTrackingPublic(string $waybillId, string $courierCompany): array
    {
        // courier code biasanya = company (jne, sicepat, anteraja, dll)
        return $this->request('GET', "/trackings/{$waybillId}/couriers/{$courierCompany}");
    }


}
