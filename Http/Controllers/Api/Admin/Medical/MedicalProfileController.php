<?php

namespace App\Http\Controllers\Api\Admin\Medical;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\MedicalProfileService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MedicalProfileController extends Controller
{
    protected $medicalProfileService;

    public function __construct()
    {
        $this->medicalProfileService = new MedicalProfileService();
    }

    /**
     * @api {get} /api/admin/medical-profile Получение и поиск мед. профилей
     * @apiVersion 0.1.0
     * @apiName SearchMedProfile
     * @apiGroup AdminMedicalProfile
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {integer} [disease_id] Идентификатор заболевания
     * @apiParam {json}  [sorting] Массив сортировки {"name_ru": "asc", "diseases_count": "desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 3,
        "total": 16,
        "items": [
            {
                "id": 5,
                "name_ru": "Болезни нервной системы",
                "name_en": "Diseases of the nervous system"
                "active": true,
                "diseases_count": "465"
            },
            {
                "id": 6,
                "name_ru": "Болезни эндокринной системы и обмена веществ",
                "name_en": "Diseases of the endocrine system and metabolism"
                "active": true,
                "diseases_count": "465"
            },
            {
                "id": 7,
                "name_ru": "Болезни органов зрения",
                "name_en": "Diseases of the organs of vision"
                "active": true,
                "diseases_count": "465"
            }
        ]
    }
     *
     * @param Request|null $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function index(?Request $request)
    {
        $valid = Validator($request->all(),[
            'disease_id' => 'integer|nullable',
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
        $filterParams = $request->only('disease_id');
        $sorting = json_decode($request->get('sorting'), true) ?? null;

        return $this->medicalProfileService->search($page, $rowsPerPage, $searchKey, $filterParams, $sorting, null);
    }

    /**
     * @api {post} /api/admin/medical-profile Сохранение нового мед. профиля
     * @apiVersion 0.1.0
     * @apiName AddMedProfile
     * @apiGroup AdminMedicalProfile
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {String}  name_ru Название
     * @apiParam {String}  name_en Название
     * @apiParam {string}  [description_ru] описание на русском
     * @apiParam {string}  [description_en] описание на англ.
     * @apiParam {boolean}  [active] Активность
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "name_ru": "nameRu",
        "name_en": "nameEn",
        "description_ru": "descriptionRu",
        "description_en": "descriptionEn",
        "active": false,
        "updated_at": "2019-01-11 15:20:17",
        "created_at": "2019-01-11 15:20:17",
        "id": 23
    }
     *
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function createProfile(Request $request)
    {
        $valid = Validator($request->all(), [
            'name_ru' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'active' => 'boolean',
        ]);

        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        $data = $request->only('name_ru', 'name_en', 'description_ru', 'description_en', 'active');
        $profile = $this->medicalProfileService->createProfile($data);

        return response()->json($profile, 200);
    }

    /**
     * @api {put} /api/admin/medical-profile/{profileId} Редактирование мед. профиля
     * @apiVersion 0.1.0
     * @apiName EditMedProfile
     * @apiGroup AdminMedicalProfile
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {String}  [name_ru] Название
     * @apiParam {String}  [name_en] Название
     * @apiParam {string}  [description_ru] описание на русском
     * @apiParam {string}  [description_en] описание на англ.
     * @apiParam {boolean}  [active] Активность
     * @apiParam {array}  [diseases] Массив идентификаторов заболеваний [23,323,43,54]
     * @apiParam {array} [tags_ru] Тэги для поиска RU
     * @apiParam {array} [tags_en] Тэги для поиска EN
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "medicalProfile": [
            [
             "Мед. профиль обновлен"
            ]
        ]
    }
     * @param Request $request
     * @param int $profileId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function editProfile(Request $request, int $profileId)
    {
        $valid = Validator($request->all(), [
            'name_ru' => 'string|max:255',
            'name_en' => 'string|max:255',
            'active' => 'boolean',
            'diseases' => [ new IsArray ],
            'tags_ru' => [ new IsArray ],
            'tags_en' => [ new IsArray ],
        ]);

        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        $data = $request->only('name_ru', 'name_en', 'description_ru', 'description_en', 'active',
            'diseases', 'tags_ru', 'tags_en');

        $profile = $this->medicalProfileService->editProfile($data, $profileId);

        if (!is_null($profile)) {
            return response()->json(['medicalProfile' => [
                ['Мед. профиль обновлен'],
            ]], 200);

        } else {
            return response()->json(['medicalProfile' => [
                ['Мед. профиль не найден']
            ]], 404);
        }
    }

    /**
     * @api {get} /api/admin/medical-profile/{profileId} Получение мед. профиля
     * @apiVersion 0.1.0
     * @apiName GetMedProfile
     * @apiGroup AdminMedicalProfile
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 15,
        "name_ru": "Болезни опорно-двигательного аппарата",
        "name_en": "Diseases of the musculoskeletal system",
        "description_ru": "<p>Опорно-двигательный аппарат человека несет на себе важнейшие функции &ndash; придание телу формы и опоры, защита внутренних органов, возможность передвижения и принятия различных поз. Он состоит из скелета и мышечного корсета, представляя собой закономерную совокупность костей, соединенных суставами и сухожилиями и покрытых разными группами мышц.</p>\\r\\n<p>Заболеваниями опорно-двигательного аппарата называют потерю или ограничение тех или иных функций. Они условно делятся на болезни позвоночного столба и болезни суставов. Также существует деление заболеваний опорно-двигательного аппарата по принципу их возникновения &ndash; первичные и вторичные заболевания.</p>\\r\\n<p>К первой группе заболеваний опорно-двигательного аппарата относятся нарушения являющиеся самостоятельными. Вторичными заболеваниями принято называть нарушения в строении опорно-двигательного аппарата, возникшие в результате развития сопутствующих заболеваний.</p>",
        "description_en": "",
        "created_at": "2017-01-05 19:54:39",
        "updated_at": "2019-03-14 13:15:58",
        "active": true,
        "alias": "bolezni-oporno-dvigatelnogo-apparata-15",
        "therapies": [
            {
                "id": 239,
                "alias": "elektroforez-lekarstvennykh-sredstv-pri-boleznyakh-pecheni-i-zhelchevyvodyashchikh-putey-239",
                "name_ru": "Электрофорез лекарственных средств при болезнях печени и желчевыводящих путей",
                "name_en": ""
            }
        ],
        "seo": {
            "medical_profile_id": 15,
            "for": "medical-profile-page",
            "h1_ru": null,
            "h1_en": null,
            "title_ru": null,
            "title_en": null,
            "url": "bolezni-oporno-dvigatelnogo-apparata-15",
            "meta_description_ru": null,
            "meta_description_en": null,
            "meta_keywords_ru": null,
            "meta_keywords_en": null
        },
        "diseases": [
            {
                "id": 2,
                "parent": 1,
                "name_ru": "КИШЕЧНЫЕ ИНФЕКЦИИ",
                "name_en": "Intestinal infectious diseases"
            },
            {
                "id": 3,
                "parent": 2,
                "name_ru": "Холера",
                "name_en": "Cholera"
            },
            {
                "id": 4,
                "parent": 3,
                "name_ru": "Холера, вызванная холерным вибрионом 01, биовар cholerae",
                "name_en": "Cholera due to Vibrio cholerae 01, biovar cholerae"
            }
        ],
        "images": [],
        "tags_ru": [],
        "tags_en": []
    }
     *
     * @param int $profileId
     * @return mixed
     * @throws ApiProblemException
     */
    public function getProfile(int $profileId)
    {
        return $this->medicalProfileService->getProfile($profileId);
    }

    /**
     * @api {delete} /api/admin/medical-profile/{profileId} Удаление мед. профиля
     * @apiVersion 0.1.0
     * @apiName DeleteMedProfile
     * @apiGroup AdminMedicalProfile
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "medical_profile": [
            "Профиль удален"
        ]
    }
     *
     * @param int $profileId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteProfile(int $profileId)
    {
        $deleted = $this->medicalProfileService->deleteProfile($profileId);
        if ($deleted){

            return response()->json(['medical_profile' =>[
                'Профиль удален'
            ]], 200);
        } else {
            return response()->json(['medical_profile' =>[
                'Профиль не найден'
            ]], 404);
        }
    }

    /**
     * @api {post} /api/admin/medical-profile/{profileId}/image Добавление изображения
     * @apiVersion 0.1.0
     * @apiName AddImageMedProfile
     * @apiGroup AdminMedicalProfile
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {file}  image Файл изображения
     * @apiParam {string}  [description] Название изображения
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "medical_profile_id": 5,
        "image": "/storage/medical_profile/nFFTMz33mQ1tzJQlTwpiHfMYmm83U1XGPIXxvAo9.jpeg",
        "description": null,
        "id": 10
    }
     *
     * @param Request $request
     * @param int $profileId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function addProfileImage(Request $request, int $profileId)
    {
        $valid = Validator($request->all( ),[
            'image' => 'file|image|max:5128',
        ]);
        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        $image = $this->medicalProfileService->addProfileImage($request, $profileId);
        return response()->json($image, 200);
    }

    /**
     * @api {get} /api/admin/medical-profile/{profileId}/images Получение изображений мед. профиля
     * @apiVersion 0.1.0
     * @apiName GetImagesMedProfile
     * @apiGroup AdminMedicalProfile
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     [
        {
            "id": 12,
            "medical_profile_id": 5,
            "description": null,
            "image": "/storage/medical_profile/nM1Szrg6OojAzJLSnPt1XVNr4MyDgJUYtgZznHnt.jpeg"
        },
        {
            "id": 11,
            "medical_profile_id": 5,
            "description": null,
            "image": "/storage/medical_profile/7JuqIKEq6xNOZDsL9AN0jwFA7HcRBYWPhpSV8fT5.jpeg"
        },
        {
            "id": 10,
            "medical_profile_id": 5,
            "description": null,
            "image": "/storage/medical_profile/nFFTMz33mQ1tzJQlTwpiHfMYmm83U1XGPIXxvAo9.jpeg"
        }
    ]

     *
     * @param int $profileId
     * @return mixed
     */
    public function getProfileImages(int $profileId)
    {
        return $this->medicalProfileService->getProfileImages($profileId);
    }

    /**
     * @api {delete} /api/admin/medical-profile/{profileId}/image/{imageId} Удаление изображения мед. профиля
     * @apiVersion 0.1.0
     * @apiName DeleteImageMedProfile
     * @apiGroup AdminMedicalProfile
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "medical_profile": [
            "Изображение удалено"
        ]
    }
     *
     * @param int $profileId
     * @param int $imageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteProfileImage(int $profileId, int $imageId)
    {
        $deleted = $this->medicalProfileService->deleteProfileImage($profileId, $imageId);

        if ($deleted){

            return response()->json(['medical_profile' =>[
                'Изображение удалено'
            ]], 200);
        } else {
            return response()->json(['medical_profile' =>[
                'Изображение не найдено'
            ]], 404);
        }
    }
}
