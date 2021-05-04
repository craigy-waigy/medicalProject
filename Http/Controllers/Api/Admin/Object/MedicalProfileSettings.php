<?php

namespace App\Http\Controllers\Api\Admin\Object;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\MedicalProfileService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
     * @api {get} /api/admin/object/settings/medical-profile/{medicalProfileId}/{objectId} Список заболеваний мед.профиля
     * @apiVersion 0.1.0
     * @apiName GetMedicalProfileDisease
     * @apiGroup AdminMedicalProfileSettings
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "defaultDiseasesCount": 23,
        "notDefaultDiseasesCount": 594,
        "items": [
            {
                "id": 1,
                "parent": 0,
                "name_ru": "Некоторые инфекционные и паразитарные болезни",
                "name_en": "qw",
                "has_children": true,
                "excluded": true,
                "is_default": false
            },
            {
                "id": 71,
                "parent": 1,
                "name_ru": "Туберкулез",
                "name_en": "Tuberculosis",
                "has_children": true,
                "excluded": true,
                "is_default": false
            }
        ],
    }
     *
     * @param int $medicalProfileId
     * @param int $objectId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function getDiseases(int $medicalProfileId, int $objectId)
    {
        $diseases = $this->medicalProfileService->getDiseases($medicalProfileId, $objectId);

        return response()->json($diseases, 200);
    }

    /**
     * @api {post} /api/admin/object/settings/medical-profile Исключение заболеваний из профиля лечения
     * @apiVersion 0.1.0
     * @apiName ExcludeMedicalProfileDiseases
     * @apiGroup AdminMedicalProfileSettings
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
     */
    public function offDiseases(Request $request)
    {
        $valid = Validator($request->all(), [
            'medical_profile_id' => 'required|integer',
            'object_id' => 'required|integer',
            'diseases' => [ 'present', new IsArray ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $objectId = $request->get('object_id');
        $medicalProfileId = $request->get('medical_profile_id');
        $diseases = $request->get('diseases');
        if (!is_array($diseases)) $diseases = json_decode($diseases, true);

        $this->medicalProfileService->offDiseases($objectId, $medicalProfileId, $diseases);

        $this->medicalProfileService->checkMedicalProfileHasDefaultDiseasesOnly($medicalProfileId, $objectId);
        $this->medicalProfileService->checkObjectHasDefaultDiseasesOnly($objectId);

        return response()->json(['message' => 'Настройки мед. профиля сохранены'], 200);
    }

    /**
     * @api {post} /api/admin/object/settings/reset-medical-profiles Сбросить все медпрофили к дефолтным заболеваниям
     * @apiVersion 0.1.0
     * @apiName ChangeAllMedicalProfileDiseases
     * @apiGroup AdminMedicalProfileSettings
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} object_id ID объекта(санатория)
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Медпрофили сброшены к дефолтным заболеваниям"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetAllMedicalProfilesToDefaultDiseases(Request $request)
    {
        $valid = Validator($request->all(), [
            'object_id' => 'required|integer|exists:objects,id',
        ], [
            'exists' => 'id объекта некорректный'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $objectId = $request->get('object_id');

        $this->medicalProfileService->resetAllMedicalProfilesToDefaultDiseases($objectId);

        return response()->json(['message' => 'Медпрофили сброшены к дефолтным заболеваниям'], 200);
    }

    /**
     * @api {post} /api/admin/object/settings/reset-medical-profile Сбросить медпрофиль к дефолтным заболеваниям
     * @apiVersion 0.1.0
     * @apiName ChangeMedicalProfileDiseases
     * @apiGroup AdminMedicalProfileSettings
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
