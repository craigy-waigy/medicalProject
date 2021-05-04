<?php

namespace App\Http\Controllers\Api\Account\Object;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\MedicalProfileService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MedicalProfileSettings extends Controller
{
    /**
     * @var MedicalProfileService
     */
    protected $medicalProfileService;

    /**
     * MedicalProfileSettings constructor.
     */
    public function __construct()
    {
        $this->medicalProfileService = new MedicalProfileService();
    }

    /**
     * @api {get} /api/account/object/settings/medical-profile/{medicalProfileId} Список заболеваний мед.профиля
     * @apiVersion 0.1.0
     * @apiName GetMedicalProfileDisease
     * @apiGroup AccountObjectSettings
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 3565,
            "parent": 3564,
            "name_ru": "Болезни наружного уха",
            "name_en": "Diseases of external ear",
            "has_children": true,
            "excluded": false,
            "is_default": false
        },
        {
            "id": 3566,
            "parent": 3565,
            "name_ru": "Наружный отит",
            "name_en": "Otitis externa",
            "has_children": true,
            "excluded": false,
            "is_default": true
        },
    ]
     *
     * @param int $medicalProfileId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function getDiseases(int $medicalProfileId)
    {
        if (is_null(Auth::user()->object))
            throw new ApiProblemException('У пользователя нет объекта', 404);

        $objectId = Auth::user()->object->id;

        $diseases = $this->medicalProfileService->getDiseases($medicalProfileId, $objectId);

        return response()->json($diseases, 200);
    }

    /**
     * @api {post} /api/account/object/settings/medical-profile Исключение заболеваний из профиля лечения
     * @apiVersion 0.1.0
     * @apiName ChangeMedicalProfileDisease
     * @apiGroup AccountObjectSettings
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} medical_profile_id ID мед. профиля
     * @apiParam {array} diseases Массив ID заболеваний которые необходимо исключить из мед профиля [23,43,656]
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Настройки мед. профиля сохранены"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function offDiseases(Request $request)
    {
        $valid = Validator($request->all(), [
           'medical_profile_id' => 'required|integer',
           'diseases' => [ 'present', new IsArray ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        if (is_null(Auth::user()->object))
            throw new ApiProblemException('У пользователя нет объекта', 404);

        $objectId = Auth::user()->object->id;
        $medicalProfileId = $request->get('medical_profile_id');
        $diseases = $request->get('diseases');
        if (!is_array($diseases)) $diseases = json_decode($diseases, true);

        $this->medicalProfileService->offDiseases($objectId, $medicalProfileId, $diseases);

        $this->medicalProfileService->checkMedicalProfileHasDefaultDiseasesOnly($medicalProfileId, $objectId);
        $this->medicalProfileService->checkObjectHasDefaultDiseasesOnly($objectId);

        return response()->json(['message' => 'Настройки мед. профиля сохранены'], 200);
    }

    /**
     * @api {get} /api/{locale}/account/object/medical-profile Получение списка мед. профилей (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetMedicalProfiles
     * @apiGroup AccountObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
        {
            "page": 1,
            "rowsPerPage": 10,
            "total": 14,
            "items": [
                {
                    "id": 5,
                    "name": "Болезни нервной системы",
                    "alias": "bolezni-nervnoy-sistemy",
                    "diseases_count": 100
                }
            ]
        }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function getMedicalProfiles(Request $request, string $locale)
    {
        $valid = Validator($request->all(),[
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
        ]);
        if ($valid->fails()) return response( $valid->errors(), 400);

        if (is_null(Auth::user()->object))
            throw new ApiProblemException('У пользователя нет объекта', 404);
        $objectId = Auth::user()->object->id;

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;
        $sorting = json_decode($request->get('sorting'), true) ?? null;

        $medicalProfiles = $this->medicalProfileService
            ->getObjectMedicalProfiles($page, $rowsPerPage, $searchKey, null, $sorting, $locale, $objectId);

        return response()->json($medicalProfiles, 200);
    }

    /**
     * @api {post} /api/account/object/settings/reset-medical-profile Сбросить медпрофиль к дефолтным заболеваниям
     * @apiVersion 0.1.0
     * @apiName ResetMedicalProfileDiseasesToDefault
     * @apiGroup AccountObjectSettings
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} object_id ID объекта(санатория)
     * @apiParam {integer} medical_profile_id ID медпрофиля
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
    "message": "Медпрофиль сброшен к дефолтным заболеваниям"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetMedicalProfileToDefaultDiseases(Request $request)
    {
        $valid = Validator($request->all(), [
            'object_id' => 'required|integer|exists:objects,id',
            'medical_profile_id' => 'required|integer|exists:medical_profiles,id',
        ], [
            'exists' => 'id объекта некорректный'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $objectId = $request->get('object_id');
        $medicalProfileId = $request->get('medical_profile_id');

        $this->medicalProfileService->resetMedicalProfileToDefaultDiseases($objectId, $medicalProfileId);

        return response()->json(['message' => 'Медпрофиль сброшен к дефолтным заболеваниям'], 200);
    }

}
