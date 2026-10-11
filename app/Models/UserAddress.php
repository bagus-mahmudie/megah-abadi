<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'label',
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
        'is_default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
