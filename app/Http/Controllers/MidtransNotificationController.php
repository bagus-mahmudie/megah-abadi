<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class MidtransNotificationController extends Controller
{
    public function handle(Request $request)
    {
        // load config midtrans
        \Midtrans\Config::$serverKey    = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized  = true;
        \Midtrans\Config::$is3ds        = true;

        // baca notifikasi dari Midtrans
        $notification = new \Midtrans\Notification();

        $orderId           = $notification->order_id;          // ini = $order->code
        $transactionStatus = $notification->transaction_status;
        $fraudStatus       = $notification->fraud_status;

        $order = Order::where('code', $orderId)->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // simpan info transaksi
        $order->payment_transaction_id = $notification->transaction_id ?? $order->payment_transaction_id;

        // mapping status midtrans -> status order kamu
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $order->payment_status = 'pending';
                $order->status         = 'pending';
            } else if ($fraudStatus == 'accept') {
                $order->payment_status = 'paid';
                $order->status         = 'awaiting_shipment'; // atau 'processing'
            }
        } else if ($transactionStatus == 'settlement') {
            $order->payment_status = 'paid';
            $order->status         = 'awaiting_shipment';
        } else if ($transactionStatus == 'pending') {
            $order->payment_status = 'unpaid';
            $order->status         = 'pending';
        } else if ($transactionStatus == 'deny') {
            $order->payment_status = 'failed';
            $order->status         = 'cancelled';
        } else if (in_array($transactionStatus, ['expire', 'cancel'])) {
            $order->payment_status = 'expired';
            $order->status         = 'cancelled';
        }

        $order->save();

        return response()->json(['message' => 'OK']);
    }
}
