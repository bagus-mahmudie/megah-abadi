<?php

namespace App\Services\Payments;

use App\Models\Order;

class MidtransService
{
    public function __construct()
    {
        \Midtrans\Config::$serverKey    = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized  = true;
        \Midtrans\Config::$is3ds        = true;
    }

    /**
     * Buat Snap transaction dan kembalikan token & redirect URL.
     */
    public function createSnapTransaction(Order $order): array
    {
        /**
         * Normalisasi country code supaya SELALU 3 karakter (ISO 3166-1 alpha-3)
         * Contoh: "ID" -> "IDN"
         */
        $countryCode = strtoupper($order->receiver_country_code ?: 'IDN');

        if (strlen($countryCode) < 3) {
            // kalau cuma 2 huruf atau kurang, paksa jadi IDN
            $countryCode = 'IDN';
        } elseif (strlen($countryCode) > 3) {
            // kalau lebih dari 3 huruf, potong 3 karakter pertama
            $countryCode = substr($countryCode, 0, 3);
        }

        $params = [
            'transaction_details' => [
                // gunakan kode order sendiri
                'order_id'     => $order->code,
                'gross_amount' => (int) round($order->grand_total),
            ],

            'customer_details' => [
                'first_name' => $order->receiver_name,
                'email'      => $order->receiver_email,
                'phone'      => $order->receiver_phone,
                'shipping_address' => [
                    'first_name'   => $order->receiver_name,
                    'address'      => trim($order->receiver_address_line1 . ' ' . $order->receiver_address_line2),
                    'postal_code'  => $order->receiver_postal_code,
                    'country_code' => $countryCode,
                ],
            ],

            'item_details' => $order->items->map(function ($item) {
                return [
                    'id'       => (string) $item->id,
                    'price'    => (int) round($item->price),   // sesuaikan dengan field harga di OrderItem
                    'quantity' => $item->qty,
                    'name'     => substr($item->product_name, 0, 50),
                ];
            })->push([
                'id'       => 'shipping',
                'price'    => (int) round($order->shipping_cost),
                'quantity' => 1,
                'name'     => 'Biaya Pengiriman',
            ])->toArray(),
        ];

        $snapTransaction = \Midtrans\Snap::createTransaction($params);

        return [
            'snap_token'   => $snapTransaction->token ?? $snapTransaction['token'] ?? null,
            'redirect_url' => $snapTransaction->redirect_url ?? $snapTransaction['redirect_url'] ?? null,
        ];
    }
}
