<?php

namespace App\Http\Controllers\Api\Filter;

use App\Models\Capabilities;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CapabilitiesController extends Controller
{
    /**
     *
     * @api {get} /api/filter/capabilities получение списка удобств
     * @apiVersion 0.1.0
     * @apiName GetCapabilities
     * @apiGroup Filter
     *
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
        HTTP/1.1 200 OK
        [
            {
                "id": 1,
                "name_ru": "Пляж",
                "name_en": "Beach",
                "slug": "beach"
            },
            {
                "id": 2,
                "name_ru": "SPA",
                "name_en": "SPA",
                "slug": "spa"
            },
            {
                "id": 3,
                "name_ru": "Бассейн",
                "name_en": "Poll",
                "slug": "pool"
            },
            {
                "id": 4,
                "name_ru": "Инфраструктура для людей с ограниченными возможностями",
                "name_en": "Infrastructure for people with disabilities",
                "slug": "for_disabilities"
            },
            {
                "id": 5,
                "name_ru": "Услуги для детей",
                "name_en": "Services for children",
                "slug": "for_children"
            }
        ]
     *
     *
     * @return Capabilities[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return Capabilities::all();
    }
}
