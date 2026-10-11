<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'cart_id',
        'code',
        'total_items',
        'subtotal_amount',
        'shipping_cost',
        'grand_total',
        'currency',
        'status',
        'payment_status',
        'payment_provider',
        'payment_transaction_id',
        'payment_payload',
        'shipping_provider',
        'shipping_courier_code',
        'shipping_courier_name',
        'shipping_etd',
        'receiver_name',
        'receiver_phone',
        'receiver_email',
        'receiver_address_line1',
        'receiver_address_line2',
        'receiver_postal_code',
        'receiver_country_code',
        'receiver_province',
        'receiver_city',
        'receiver_city_id',
        'receiver_district',
        'receiver_district_id',
        'receiver_latitude',
        'receiver_longitude',
        'customer_notes',
        'internal_notes',
        'metadata',
    ];

    protected $casts = [
        'payment_payload' => 'array',
        'metadata'        => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }
}
