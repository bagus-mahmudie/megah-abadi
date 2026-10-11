<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'biteship_order_id',
        'courier_tracking_code',
        'waybill_id',
        'status',
        'raw_status',
        'courier_company',
        'courier_service',
        'picked_up_at',
        'delivered_at',
        'cancelled_at',
        'label_url',
        'raw_payload',
    ];

    protected $casts = [
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'raw_payload'  => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
