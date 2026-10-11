<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;

class UserAddressController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'label'                   => ['nullable', 'string', 'max:255'],
            'receiver_name'           => ['required', 'string', 'max:255'],
            'receiver_phone'          => ['required', 'string', 'max:32'],
            'receiver_email'          => ['nullable', 'email'],
            'receiver_address_line1'  => ['required', 'string', 'max:255'],
            'receiver_address_line2'  => ['nullable', 'string', 'max:255'],
            'receiver_postal_code'    => ['required', 'string', 'max:16'],
            'receiver_province'       => ['required', 'string', 'max:255'],
            'receiver_city'           => ['required', 'string', 'max:255'],
            'receiver_city_id'        => ['nullable', 'string', 'max:255'],
            'receiver_district'       => ['required', 'string', 'max:255'],
            'receiver_district_id'    => ['nullable', 'string', 'max:255'],
            'receiver_area_id'        => ['nullable', 'string', 'max:255'],
        ]);

        $data['user_id'] = $user->id;
        $data['receiver_country_code'] = 'ID';
        $data['receiver_email'] = $data['receiver_email'] ?? $user->email;

        $address = UserAddress::create($data);

        return response()->json([
            'success' => true,
            'address' => $address,
        ]);
    }
}
