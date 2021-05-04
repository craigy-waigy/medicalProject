<?php

namespace App\Http\Controllers\Api\Admin\Medical;

use App\Exceptions\ApiProblemException;
use App\Exceptions\UnsupportLocaleException;
use App\Rules\IsArray;
use App\Services\DiseaseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DiseaseController extends Controller
{
    protected $diseaseService;

    public function __construct()
    {
        $this->diseaseService = new DiseaseService();
    }

    /**
     * @api {get} /api/admin/disease Получение и поиск заболеваний
     * @apiVersion 0.1.0
     * @apiName SearchDisease
     * @apiGroup AdminDisease
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {integer}  [parent] Родитель
     * @apiParam {integer}  [profile_id] Идентификатор мед. профиля
     * @apiParam {integer}  [therapy_id] Идентификатор мет. лечения лечения
     * @apiParam {json}  [sorting] Массив сортировки {"therapies_count": "asc", "name_ru": "desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 3,
        "total": 11176,
        "items": [
            {
                "id": 12,
                "parent": 7,
                "name_ru": "Паратиф неуточненный",
                "active": false,
                "has_children": false,
                "open_new_page": false,
                "temporarily_disabled": false,
                "medical_profiles_count": "0"
            },
            {
                "id": 13,
                "parent": 2,
                "name_ru": "Другие сальмонеллезные инфекции",
                "active": true,
                "has_children": true,
                "open_new_page": false,
                "temporarily_disabled": false,
                "medical_profiles_count": "1"
            },
            {
                "id": 14,
                "parent": 13,
                "name_ru": "Сальмонеллезный энтерит",
                "active": true,
                "has_children": false,
                "open_new_page": false,
                "temporarily_disabled": false,
                "medical_profiles_count": "1"
            }
        ]
    }
     *
     * @param Request|null $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws UnsupportLocaleException
     */
    public function index(?Request $request)
    {
        $valid = Validator($request->all(),[
            'profile_id' => 'integer|nullable',
            'therapy_id' => 'integer|nullable',
            'parent' => 'integer|nullable',
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
        $sorting = json_decode($request->get('sorting'), true) ?? null;

        $profileId = $request->get('profile_id') ?? null;
        $therapyId = $request->get('therapy_id') ?? null;
        $parent = $request->get('parent') ?? null;

        return $this->diseaseService->search($page, $rowsPerPage, $searchKey, $profileId, $therapyId, $parent, null, $sorting);
    }

    /**
     * @api {post} /api/admin/disease Сохранение нового заболевания
     * @apiVersion 0.1.0
     * @apiName AddDisease
     * @apiGroup AdminDisease
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {String}  name_ru Название
     * @apiParam {String}  name_en Название
     * @apiParam {string}  [description_ru] описание на русском
     * @apiParam {string}  [description_en] описание на англ.
     * @apiParam {string}  [source_ru] источник на англ.
     * @apiParam {string}  [source_en] источник на англ.
     * @apiParam {integer}  [parent] Родитель
     * @apiParam {boolean}  [open_new_page] тип открытие окна описания
     * @apiParam {boolean}  [temporarily_disabled] временно заблокировано
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "name_ru": "nameRu",
        "name_en": "NameEn",
        "desc_ru": "qweqw",
        "desc_en": "qweqwe",
        "source_ru": "qweqweqwe",
        "source_en": "qweqweqwe",
        "active": false,
        "open_new_page": true,
        "temporarily_disabled": true
    }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function addDisease(Request $request)
    {
        $valid = Validator($request->all(), [
            'name_ru' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'parent' => 'integer|nullable',
            'open_new_page' => 'boolean',
            'temporarily_disabled' => 'boolean',
        ]);

        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        $data = $request->only('name_ru', 'name_en', 'desc_ru', 'desc_en', 'source_ru', 'source_en', 'parent', 'open_new_page', 'temporarily_disabled');
        $disease = $this->diseaseService->addDisease($data);

        return response()->json($disease, 200);
    }

    /**
     * @api {put} /api/admin/disease/{diseaseId} Редактирование заболевания
     * @apiVersion 0.1.0
     * @apiName EditDisease
     * @apiGroup AdminDisease
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {String}  [name_ru] Название
     * @apiParam {String}  [name_en] Название
     * @apiParam {string}  [description_ru] описание на русском
     * @apiParam {string}  [description_en] описание на англ.
     * @apiParam {string}  [source_ru] источник на англ.
     * @apiParam {string}  [source_en] источник на англ.
     * @apiParam {integer}  [parent] Родитель
     * @apiParam {array} [tags_ru] Тэги для поиска RU
     * @apiParam {array} [tags_en] Тэги для поиска EN
     * @apiParam {array} [therapies_ids] Методы лечения
     * @apiParam {boolean}  [open_new_page] тип открытие окна описания, true по умолчанию
     * @apiParam {boolean}  [temporarily_disabled] временно заблокировано
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "disease": [
            [
                "Заболевание обновлено"
            ]
        ]
    }
     *
     * @param Request $request
     * @param int $diseaseId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function editDisease(Request $request, int $diseaseId)
    {
        $valid = Validator($request->all(), [
            'name_ru' => 'string|max:255',
            'name_en' => 'string|max:255',
            'parent' => 'integer|nullable',
            'active' => 'boolean|nullable',
            'tags_ru' => [ new IsArray ],
            'tags_en' => [ new IsArray ],
            'therapies_ids' => [ new IsArray ],
            'open_new_page' => 'boolean',
            'temporarily_disabled' => 'boolean',
        ]);

        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        $therapiesIds = $request->get('therapies_ids');

        $data = $request->only('name_ru', 'name_en', 'desc_ru', 'desc_en', 'source_ru', 'source_en',
            'parent', 'active', 'tags_ru', 'tags_en', 'open_new_page', 'temporarily_disabled');
        $disease = $this->diseaseService->editDisease($data, $diseaseId, $therapiesIds);

        if (!is_null($disease)) {
            return response()->json(['disease' => [
                ['Заболевание обновлено'],
            ]], 200);

        } else {
            return response()->json(['disease' => [
                ['Заболевание не найдено']
            ]], 404);
        }
    }

    /**
     * @api {get} /api/admin/disease/{diseaseId} Получение заболевания
     * @apiVersion 0.1.0
     * @apiName GetDisease
     * @apiGroup AdminDisease
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2642,
        "parent": 2638,
        "name_ru": "Соматоформная дисфункция вегетативной нервной системы",
        "name_en": "Somatoform autonomic dysfunction",
        "desc_ru": "Соматоформная дисфункция вегетативной нервной системы. Жалобы предъявляются больным таким образом, будто они обусловлены физическим расстройством той системы или органа, которые в основном или полностью находятся под влиянием вегетативной нервной системы, то есть сердечно-сосудистой, желудочно-кишечной или дыхательной системы. (Сюда частично относится и мочеполовая система). Наиболее частые и яркие примеры относятся к сердечно-сосудистой системе (\\\"невроз сердца\\\"), дыхательной системе (психогенная одышка и икота) и желудочно-кишечной системе (\\\"невроз желудка\\\" и \\\"нервный понос\\\"). Симптомы обычно бывают двух типов, ни один из которых не указывает на физическое расстройство затрагиваемого органа или системы. Первый тип симптомов, на котором во многом основывается диагностика, характеризуется жалобами, отражающими объективные признаки вегетативного возбуждения, такие как сердцебиение, потение, покраснение и тремор. Второй тип характеризуется более идиосинкратическими, субъективными и неспецифическими симптомами, такими как ощущения мимолетных болей, жжения, тяжести, напряжения, ощущения раздувания или растяжения.",
        "desc_en": "",
        "source_ru": "",
        "source_en": "",
        "active": true,
        "has_children": true,
        "seo": null,
        "tags_ru": [],
        "tags_en": [],
        "open_new_page": false,
        "temporarily_disabled": false,
        "medical_profiles": [
            {
                "id": 5,
                "name_ru": "nameRu",
                "name_en": "nameEn",
                "active": true,

            }
        ],
        "therapies": [
            {
                "id": 2,
                "name_ru": "Визуальный осмотр общетерапевтический",
                "name_en": "",

            },
            {
                "id": 1,
                "name_ru": "Сбор анамнеза и жалоб общетерапевтический",
                "name_en": "",

            }
        ]
    }
     *
     * @param int $diseaseId
     * @return mixed
     * @throws ApiProblemException
     */
    public function getDisease(int $diseaseId)
    {
        return $this->diseaseService->getDisease($diseaseId);
    }

    /**
     * @api {delete} /api/admin/disease/{diseaseId} Удаление заболевания
     * @apiVersion 0.1.0
     * @apiName DeleteDisease
     * @apiGroup AdminDisease
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "disease": [
            "Заболевание удалено"
        ]
    }
     *
     * @param int $diseaseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDisease(int $diseaseId)
    {
        $deleted = $this->diseaseService->deleteDisease($diseaseId);

        if ($deleted){

            return response()->json(['disease' =>[
                'Заболевание удалено'
            ]], 200);
        } else {
            return response()->json(['disease' =>[
                'Заболевание не найдено'
            ]], 404);
        }
    }

    /**
     * @api {patch} /api/admin/disease/{diseaseId}/medical-profile/{medical_profile_id} Установлени связи заболевание - мед. профиль
     * @apiVersion 0.1.0
     * @apiName AddRelationProfile-Disease
     * @apiGroup AdminDisease
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "disease": [
            "Связь успешно установлена"
        ]
    }
     *
     * @param int $diseaseId
     * @param int $medicalProfileId
     * @return \Illuminate\Http\JsonResponse
     */
    public function addDiseaseMedicalProfile(int $diseaseId, int $medicalProfileId)
    {
        $response = $this->diseaseService->addDiseaseMedicalProfile($diseaseId, $medicalProfileId);

        return response()->json(['disease' =>[
            $response['message']
        ]], $response['status']);
    }

    /**
     * @api {delete} /api/admin/disease/{diseaseId}/medical-profile/{medical_profile_id} Разрыв связи заболевание - мед. профиль
     * @apiVersion 0.1.0
     * @apiName DeleteRelationProfile-Disease
     * @apiGroup AdminDisease
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "disease": [
            "Связь успешно разорвана"
        ]
    }
     *
     * @param int $diseaseId
     * @param int $medicalProfileId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDiseaseMedicalProfile(int $diseaseId, int $medicalProfileId)
    {
        $response = $this->diseaseService->deleteDiseaseMedicalProfile($diseaseId, $medicalProfileId);

        return response()->json(['disease' =>[
            $response['message']
        ]], $response['status']);
    }

    /**
     * @api {patch} /api/admin/disease/{diseaseId}/therapy/{therapyId} Установлени связи заболевание - мет. лечения
     * @apiVersion 0.1.0
     * @apiName AddRelationTherapy-Disease
     * @apiGroup AdminDisease
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "disease": [
            "Связь успешно установлена"
        ]
    }
     *
     * @param int $diseaseId
     * @param int $therapyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function addDiseaseTherapy(int $diseaseId, int $therapyId)
    {
        $response = $this->diseaseService->addDiseaseTherapy($diseaseId, $therapyId);

        return response()->json(['disease' =>[
            $response['message']
        ]], $response['status']);
    }

    /**
     * @api {delete} /api/admin/disease/{diseaseId}therapy/{therapyId} Разрыв связи заболевание - мет. лечения
     * @apiVersion 0.1.0
     * @apiName DeleteRelationTherapy-Disease
     * @apiGroup AdminDisease
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "disease": [
            "Связь успешно разорвана"
        ]
    }
     *
     * @param int $diseaseId
     * @param int $therapyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDiseaseTherapy(int $diseaseId, int $therapyId)
    {
        $response = $this->diseaseService->deleteDiseaseTherapy($diseaseId, $therapyId);

        return response()->json(['disease' =>[
            $response['message']
        ]], $response['status']);
    }
}
