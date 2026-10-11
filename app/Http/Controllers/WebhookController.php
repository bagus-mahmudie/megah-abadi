<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipment;
use App\Services\Shipping\BiteshipClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class WebhookController extends Controller
{
    /**
     * Webhook dari Midtrans
     * Set payment_status & trigger create shipment ke Biteship.
     */
    public function midtrans(Request $request, BiteshipClient $biteship)
    {
        $payload = $request->all();
        \Log::info('MIDTRANS WEBHOOK HIT', $payload);

        $orderIdMidtrans   = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus       = $payload['fraud_status'] ?? null;

        if (!$orderIdMidtrans) {
            return response()->json(['message' => 'Missing order_id'], 400);
        }

        $order = Order::where('code', $orderIdMidtrans)->first();

        if (! $order) {
            \Log::warning('Order not found for Midtrans webhook', [
                'order_id' => $orderIdMidtrans,
            ]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        // simpan payload mentah untuk debug
        $order->payment_payload = $payload;

        switch ($transactionStatus) {
            case 'capture':
                // biasanya credit card
                if ($fraudStatus === 'challenge') {
                    $order->payment_status = 'pending';
                    $order->status         = 'pending';
                } else {
                    // accept
                    $order->payment_status = 'paid';
                    $order->status         = 'awaiting_shipment';
                    $order->save();

                    $this->createBiteshipShipmentForOrder($order, $biteship);
                    return response()->json(['message' => 'OK']);
                }
                break;

            case 'settlement':
                // VA / e-wallet dsb
                $order->payment_status = 'paid';
                $order->status         = 'awaiting_shipment';

                $order->save();
                $this->createBiteshipShipmentForOrder($order, $biteship);
                return response()->json(['message' => 'OK']);

            case 'pending':
                $order->payment_status = 'unpaid';
                $order->status         = 'pending';
                break;

            case 'cancel':
            case 'expire':
            case 'deny':
                $order->payment_status = 'failed';
                $order->status         = 'cancelled';
                break;
        }

        $order->save();

        return response()->json(['message' => 'OK']);
    }

    public function createBiteshipShipmentForOrder(Order $order, BiteshipClient $biteship)
    {
        try {
            $shipmentData = $biteship->createShipmentFromOrder($order);

            if (!$shipmentData || !isset($shipmentData['id'])) {
                \Log::error("Failed create shipment", ['order_id' => $order->id, 'result' => $shipmentData]);
                return false;
            }

            Shipment::create([
                'order_id'              => $order->id,
                'biteship_order_id'     => $shipmentData['id'],

                'courier_tracking_code' => Arr::get($shipmentData, 'courier.tracking_id'),
                'waybill_id'            => Arr::get($shipmentData, 'courier.waybill_id'),

                'status'                => $shipmentData['status'] ?? 'confirmed',
                'raw_status'            => $shipmentData['status'] ?? null,

                'courier_company'       => Arr::get($shipmentData, 'courier.company'),
                'courier_service'       => Arr::get($shipmentData, 'courier.type'),
                'label_url'             => Arr::get($shipmentData, 'courier.link'),

                'raw_payload'           => $shipmentData, // biar langsung cast ke json di model
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error("Create shipment error", [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Webhook dari Biteship untuk update tracking.
     */
    public function biteship(Request $request)
    {
        $payload = $request->all();
        Log::info('Biteship webhook', $payload);

        $biteshipOrderId = $payload['order_id'] ?? $payload['id'] ?? null;
        $status = $payload['status'] ?? null;

        if (!$biteshipOrderId) {
            return response()->json(['message' => 'Missing order_id'], 400);
        }

        $shipment = Shipment::where('biteship_order_id', $biteshipOrderId)->first();

        if (!$shipment) {
            return response()->json(['message' => 'Shipment not found'], 404);
        }

        $shipment->status = $status ?? $shipment->status;
        $shipment->raw_status = $status;
        $shipment->raw_payload = $payload;

        if ($status === 'delivered') {
            $shipment->delivered_at = now();
            $shipment->order->status = 'completed';
            $shipment->order->save();
        }

        $shipment->save();

        return response()->json(['message' => 'OK']);
    }
}
