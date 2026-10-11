<?php
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\PostalCode;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function provinces()
    {
        return Province::orderBy('name')->get(['id','name']);
    }

    public function cities(Request $request)
    {
        $request->validate(['province_id' => 'required|integer']);
        return City::where('province_id', $request->province_id)
            ->orderBy('name')
            ->get(['id','name','type']);
    }

    public function districts(Request $request)
    {
        $request->validate(['city_id' => 'required|integer']);
        return District::where('city_id', $request->city_id)
            ->orderBy('name')
            ->get(['id','name']);
    }

    public function postalCodes(Request $request)
    {
        $request->validate(['district_id' => 'required|integer']);
        return PostalCode::where('district_id', $request->district_id)
            ->orderBy('postal_code')
            ->get(['id','village','postal_code','latitude','longitude']);
    }
}
