<?php

namespace App\Http\Controllers\Api\Account\SanatoriumDoctor;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\SanatoriumDoctorService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SanatoriumDoctorController extends Controller
{
    /**
     * @var SanatoriumDoctorService
     */
    protected $sanatoriumDoctorService;

    /**
     * SanatoriumDoctorController constructor.
     * @param SanatoriumDoctorService $sanatoriumDoctorService
     */
    public function __construct(SanatoriumDoctorService $sanatoriumDoctorService)
    {
        $this->sanatoriumDoctorService = $sanatoriumDoctorService;
    }


    /**
     * @api {put} /api/account/sanatorium-doctor Редактирование данных врача
     * @apiVersion 0.1.0
     * @apiName EditDoctor
     * @apiGroup AccountSanatoriumDoctor
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiParam {array} [specializations_ru] Специализация на русском (["терапевт", "окулист", "парацитамол"])
     * @apiParam {array} [specializations_en] Специализация на английском (["терапевт", "окулист", "парацитамол"])
     * @apiParam {array} [languages] Языки на котором говорит врач (["ru","en","kz","az","hz"])
     * @apiParam {array} [online] Отправляется TRUE если врач онлайн, FALSE если вышел. (Если флаг был установлен в TRUE более 5 мин. назад, то автоматически меняется на FALSE системой)
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "id": 45,
        "email": "email-test@mail.com",
        "name": "testustaton",
        "father_name": "father_name",
        "last_name": "last_name",
        "fullname": "last_name testustaton father_name",
        "avatar": null,
        "sanatorium_doctor": {
            "id": 1,
            "user_id": 45,
            "object_id": 33,
            "specializations_ru": [
                "terapevt",
                "okulist"
            ],
            "specializations_en": [
                "terapevt",
                "okulist",
                "herolog"
            ],
            "languages": [
                "ru",
                "en"
            ],
            "online": false,
            "deleted_at": null,
            "created_at": "2019-08-15 05:22:49",
            "updated_at": "2019-08-15 05:41:13"
        }
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function editDoctor(Request $request)
    {
        $valid = Validator($request->all(), [

            'specializations_ru' => [ new IsArray ],
            'specializations_en' => [ new IsArray ],
            'languages' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $sanatoriumDoctor = Auth::user()->sanatoriumDoctor;
        if (is_null($sanatoriumDoctor))
            throw new ApiProblemException('У пользователя нет данных доктора санатория ', 422);

        $data = $request->only(

            'specializations_ru',
            'specializations_en',
            'languages',
            'online'
        );
        $sanatoriumDoctor = $this->sanatoriumDoctorService->editDoctor($sanatoriumDoctor->user_id, $sanatoriumDoctor->object_id, $data);

        return response()->json($sanatoriumDoctor, 200);
    }


    /**
     * @api {get} /api/account/sanatorium-doctor Получение данных врача
     * @apiVersion 0.1.0
     * @apiName GetDoctor
     * @apiGroup AccountSanatoriumDoctor
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "id": 45,
        "email": "email-test@mail.com",
        "name": "testustaton",
        "father_name": "father_name",
        "last_name": "last_name",
        "fullname": "last_name testustaton father_name",
        "avatar": null,
        "sanatorium_doctor": {
            "id": 1,
            "user_id": 45,
            "object_id": 33,
            "specializations_ru": [
                "terapevt",
                "okulist"
            ],
            "specializations_en": [
                "terapevt",
                "okulist",
                "herolog"
            ],
            "languages": [
                "ru",
                "en"
            ],
            "online": false,
            "deleted_at": null,
            "created_at": "2019-08-15 05:22:49",
            "updated_at": "2019-08-15 05:41:13"
        },
        "object": {
            "id": 33,
            "title": "Санаторий Буран",
            "country_id": 88,
            "region_id": 6977,
            "city_id": 7,
            "country": {
                "id": 88,
                "name": "Российская Федерация"
            },
            "region": {
                "id": 6977,
                "name": "Алтайский Край"
            },
            "city": {
                "id": 7,
                "name": "Барнаул"
            }
        }
    }
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function getDoctor()
    {
        $sanatoriumDoctor = Auth::user()->sanatoriumDoctor;
        if (is_null($sanatoriumDoctor))
            throw new ApiProblemException('У пользователя нет данных доктора санатория ', 422);

        $sanatoriumDoctor = $this->sanatoriumDoctorService->getDoctor($sanatoriumDoctor->user_id, $sanatoriumDoctor->object_id);

        return response()->json($sanatoriumDoctor, 200);
    }
}
