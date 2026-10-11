<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Services\Shipping\BiteshipClient;

class ShipmentController extends Controller
{
    public function show($id, BiteshipClient $biteship)
    {
        $shipment = Shipment::with('order')->findOrFail($id);

        // Prioritas pakai tracking_id
        if ($shipment->courier_tracking_code) {
            $tracking = $biteship->getTrackingById($shipment->courier_tracking_code);
        } elseif ($shipment->waybill_id && $shipment->courier_company) {
            $tracking = $biteship->getTrackingPublic($shipment->waybill_id, $shipment->courier_company);
        } else {
            $tracking = null;
        }

        // kalau sukses, bisa update status terbaru ke DB
        if ($tracking && ($tracking['success'] ?? false)) {
            $shipment->status     = $tracking['status'] ?? $shipment->status;
            $shipment->raw_status = $tracking['status'] ?? $shipment->raw_status;
            $shipment->raw_payload = $tracking; // atau simpan di kolom lain
            $shipment->save();
        }

        return view('shipments.show', [
            'shipment' => $shipment,
            'tracking' => $tracking,
        ]);
    }
}
