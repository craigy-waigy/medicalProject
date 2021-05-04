<?php

namespace App\Http\Controllers;

use App\Models\OldRegion;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        $searchKey = $request->get('search');
        if (!is_null($searchKey)){
            $searchKey = mb_strtolower($searchKey);
            $regions = OldRegion::whereRaw("lower(title) LIKE '%{$searchKey}%'")->get();
        } else {
            $regions = OldRegion::all();
        }

        return view('regions')->with([
            'regions' => $regions,
        ]);
    }

    public function getRegion(int $regionId)
    {
        $region = OldRegion::find($regionId);

        return view('region')->with([
            'region' => $region,
        ]);
    }

}
