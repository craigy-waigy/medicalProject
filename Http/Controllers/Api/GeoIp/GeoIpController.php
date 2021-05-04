<?php

namespace App\Http\Controllers\Api\GeoIp;

use App\Exceptions\UnsupportLocaleException;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Eseath\SxGeo\SxGeo;

class GeoIpController extends Controller
{
    /**
     * @api {get} /api/{locale}/geoip Получение информации о местоположении (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetGeoIP
     * @apiGroup GeoIP
     *
     *
     * @apiParam {string} [ip] IP адрес. Если не передан, адрес берется из запроса.
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "lat": 37.33939,
        "lon": -121.89496,
        "city": {
            "id": 118006,
            "region_id": 6812,
            "country_id": 190,
            "name": "Сан-Хосе"
        },
        "region": {
            "id": 6812,
            "country_id": 190,
            "name": "Калифорния"
        },
        "country": {
            "id": 190,
            "name": "США",
            "alias": "ssha-190",
            "country_code": "US",
            "telephone_code": "1",
            "locale": "en",
            "seo": {
                "id": 35673,
                "country_id": 190,
                "title": "США",
                "order": 107
            }
        }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\
     * @throws UnsupportLocaleException
     */
    public function getCityFull(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'ip' => 'ip|nullable'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $ip = $request->get('ip');
        if (is_null($ip)) $ip = $request->ip();

        $lat = null;
        $lon = null;
        $country = null;
        $region = null;
        $city = null;

        $sxGeo = new SxGeo(database_path('GeoIP.dat'));
        $data = $sxGeo->getCityFull($ip);
        if ($data){
            $lat = $data['city']['lat'];
            $lon = $data['city']['lon'];

            if ($data['city']['id'] === 0){
                if ($data['country']['iso'] != '' || $data['country']['iso'] != null || !empty($data['country']['iso'])){
                    $country = Country::where('country_code', $data['country']['iso']);
                    switch ($locale){
                        case 'ru' :
                            $country =
                                $country->select(['id', 'name_ru as name', 'alias', 'country_code', 'telephone_code', 'locale'])
                                ->with('seo:id,country_id,title_ru as title,order')
                                ->first();
                            break;
                        case 'en' :
                            $country =
                                $country->select(['id', 'name_en as name', 'alias', 'country_code', 'telephone_code', 'locale'])
                                ->with('seo:id,country_id,title_en as title,order')
                                ->first();
                            break;

                        default : throw new UnsupportLocaleException();
                    }
                }
            } elseif ($data['city']['id'] != 0){
                $city = City::where('geonameid', $data['city']['id']);
                switch ($locale){
                    case 'ru' :
                        $city = $city->select(['id', 'region_id', 'country_id', 'name_ru as name'])->first();
                        break;
                    case 'en' :
                        $city = $city->select(['id', 'region_id', 'country_id', 'name_en as name'])->first();
                        break;
                    default : throw new UnsupportLocaleException();
                }
                if (!is_null($city)){
                    $region = Region::where('id', $city->region_id);
                    switch ($locale){
                        case 'ru' :
                            $region = $region->select(['id', 'country_id', 'name_ru as name'])->first();
                            break;
                        case 'en' :
                            $region = $region->select(['id', 'country_id', 'name_en as name'])->first();
                            break;
                        default : throw new UnsupportLocaleException();
                    }

                    $country = Country::where('id', $city->country_id);
                    switch ($locale){
                        case 'ru' :
                            $country =
                                $country->select(['id', 'name_ru as name', 'alias', 'country_code', 'telephone_code', 'locale'])
                                ->with('seo:id,country_id,title_ru as title,order')
                                ->first();
                            break;
                        case 'en' :
                            $country =
                                $country->select(['id', 'name_en as name', 'alias', 'country_code', 'telephone_code', 'locale'])
                                ->with('seo:id,country_id,title_en as title,order')
                                ->first();
                            break;
                        default : throw new UnsupportLocaleException();
                    }
                }
            }
        }
        $response = ['lat' => $lat, 'lon' => $lon, 'city' => $city, 'region' => $region, 'country' => $country];

        return response()->json((object)$response, 200);
    }
}
