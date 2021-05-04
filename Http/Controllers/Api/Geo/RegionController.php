<?php

namespace App\Http\Controllers\Api\Geo;

use App\Services\GeoService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RegionController extends Controller
{
    protected $geoService;

    public function __construct()
    {
        $this->geoService = new GeoService();
    }

    /**
     * Display a listing of the resource.
     *
     * api {get} /api/geo/region поиск - получение регионов
     * apiVersion 0.1.0
     * apiName GetRegion
     * apiGroup Geo
     *
     * apiParam {integer} [page] номер страницы
     * apiParam {integer} [rowsPerPage] количество результатов на страницу
     * apiParam {String} [searchKey] Ключевое слово для поиска
     *
     * apiParam {integer} country_id ID страны
     *
     *
     * apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 1,
            "name_ru": "Краснодарский край",
            "name_en": "Krasnodar region",
            "country_id": 1,
            "created_at": null,
            "updated_at": null,
            "country": {
                "id": 1,
                "name_ru": "Россия",
                "name_en": "Russia",
                "created_at": null,
                "updated_at": null
            }
        }
    ]
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $valid = validator($request->only( 'country_id' ), [
            'country_id' => 'required|integer',
        ], [
            'country_id.required' =>  'ID страны не отправлен',
            'country_id.integer' =>  'ID страны должен быть целочисленным',
        ]);

        if ($valid->fails()){
            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];
            return response($response, $response['status']);
        }

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;
        $countryId = $request->get('country_id');

        return $this->geoService->getRegions($countryId, $page, $rowsPerPage, $searchKey);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
