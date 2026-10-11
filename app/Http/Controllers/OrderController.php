<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\Payments\MidtransService;
use App\Services\Shipping\BiteshipClient;

class OrderController extends Controller
{
    /**
     * Halaman daftar semua pesanan user (dengan tab: semua, belum bayar, dsb)
     */
    public function index(Request $request, BiteshipClient $biteship)
    {
        $user = $request->user();

        $allOrders = Order::with([
                'items.product.images',
                'items.product.category',
                'shipment',
            ])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        // Kelompokkan untuk tiap tab
        $tabs = [
            'all' => [
                'label'  => 'Semua',
                'orders' => $allOrders,
            ],
            'unpaid' => [
                'label'  => 'Belum Bayar',
                'orders' => $allOrders->where('payment_status', 'unpaid'),
            ],
            'shipping' => [
                // SEDANG DIKEMAS (belum di-pickup)
                'label'  => 'Sedang Dikemas',
                'orders' => $allOrders->filter(function ($order) {
                    $shipmentStatus = optional($order->shipment)->status;

                    // belum ada shipment, tapi sudah menunggu pengiriman
                    if (! $shipmentStatus) {
                        return $order->status === 'awaiting_shipment';
                    }

                    // shipment sudah ada tapi masih confirmed/pending
                    return in_array($shipmentStatus, ['pending', 'confirmed']);
                }),
            ],
            'shipped' => [
                // DIKIRIM (sudah di-pickup tapi belum delivered)
                'label'  => 'Dikirim',
                'orders' => $allOrders->filter(function ($order) {
                    $status = optional($order->shipment)->status;

                    return in_array($status, [
                        'picked',
                        'picked_up',
                        'droppingOff',
                        'dropping_off',
                        'on_delivery',
                    ]);
                }),
            ],
            'completed' => [
                'label'  => 'Selesai',
                'orders' => $allOrders->filter(function ($order) {
                    $status = optional($order->shipment)->status;

                    return $status === 'delivered' || $order->status === 'completed';
                }),
            ],
            'cancelled' => [
                'label'  => 'Dibatalkan',
                'orders' => $allOrders->where('status', 'cancelled'),
            ],
        ];

        $activeTab = $request->query('tab', 'all');
        if (! array_key_exists($activeTab, $tabs)) {
            $activeTab = 'all';
        }

        $orders = $tabs[$activeTab]['orders'];

         /*
        |--------------------------------------------------------------------------
        |  BAGIAN BARU: tracking Biteship untuk order yang sedang dilihat detailnya
        |--------------------------------------------------------------------------
        |  Query string yang kita pakai sudah ada di view:
        |  ?tab=xxx&order={id}
        */
        $selectedOrder           = null;
        $selectedTracking        = null;   // full response dari Biteship
        $selectedTrackingStatus  = null;   // status pengiriman (delivered, on_delivery, dll)
        $selectedTrackingHistory = [];     // array history untuk timeline

        $selectedOrderId = $request->query('order');

        if ($selectedOrderId) {
            // cari order yang dipilih dari koleksi allOrders
            $selectedOrder = $allOrders->firstWhere('id', (int) $selectedOrderId);

            if ($selectedOrder && $selectedOrder->shipment) {
                $shipment = $selectedOrder->shipment;

                // prioritas pakai waybill (nomor resi), kalau kosong pakai courier_tracking_code
                $trackingCode = $shipment->waybill_id ?: $shipment->courier_tracking_code;

                if ($trackingCode) {
                    $result = $biteship->getTracking($trackingCode);

                    // struktur BiteshipClient::request() -> success / code / error / dst
                    if (! isset($result['success']) || $result['success'] === true) {
                        $selectedTracking        = $result;
                        $selectedTrackingStatus  = $result['status']  ?? null;
                        $selectedTrackingHistory = $result['history'] ?? [];
                    } else {
                        \Log::warning('Biteship tracking failed', [
                            'order_id'   => $selectedOrder->id,
                            'shipment_id'=> $shipment->id ?? null,
                            'code'       => $trackingCode,
                            'result'     => $result,
                        ]);
                    }
                }
            }
        }

        // Hitung jumlah pesanan per tab untuk badge
        foreach ($tabs as $key => $tab) {
            $tabs[$key]['count'] = $tab['orders']->count();
        }

        return view('orders.index', [
            'title'             => 'Semua Pesanan',
            'tabs'              => $tabs,
            'orders'            => $orders,
            'activeTab'         => $activeTab,
            'selectedOrder'     => $selectedOrder,
            'midtransClientKey' => config('midtrans.client_key'),

            // >>>> tambahan untuk detail + tracking
            'selectedOrder'           => $selectedOrder,
            'selectedTracking'        => $selectedTracking,
            'selectedTrackingStatus'  => $selectedTrackingStatus,
            'selectedTrackingHistory' => $selectedTrackingHistory,
        ]);
    }


    /**
     * Endpoint AJAX: generate Snap token & mulai pembayaran
     * dipakai oleh tombol "Bayar" di kartu pesanan.
     */
    public function pay(Request $request, Order $order, MidtransService $midtrans)
    {
        $user = $request->user();

        // pastikan pesanan milik user yang login
        if ($order->user_id !== $user->id) {
            abort(403);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'message' => 'Pesanan ini sudah dibayar.',
            ], 422);
        }

        // buat Snap transaction untuk order ini
        $result = $midtrans->createSnapTransaction($order);

        $order->payment_snap_token   = $result['snap_token'] ?? null;
        $order->payment_redirect_url = $result['redirect_url'] ?? null;
        $order->payment_expired_at   = now()->addDay(); // bisa disesuaikan kalau mau
        $order->save();

        return response()->json([
            'snap_token'   => $order->payment_snap_token,
            'redirect_url' => $order->payment_redirect_url,
        ]);
    }
}
