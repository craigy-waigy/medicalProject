<?php

namespace App\Http\Controllers\Api\Admin\Object;

use App\Models\ObjectAward;
use App\Services\AwardService;
use App\Services\ObjectAwardService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AwardController extends Controller
{
    /**
     * @var AwardService
     */
    protected $awardService;

    /**
     * @var ObjectAwardService
     */
    protected $objectAwardService;

    /**
     * AwardController constructor.
     */
    public function __construct()
    {
        $this->awardService = new AwardService();
        $this->objectAwardService = new ObjectAwardService();
    }

    /**
     * @api {get} /api/admin/award-object Поиск наград
     * @apiVersion 0.1.0
     * @apiName SearchAward
     * @apiGroup AdminAwardObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     *
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 3,
        "total": 6,
        "items": [
            {
                "id": 1,
                "title_ru": "NmaeRu",
                "title_en": "NmaeEn",
                "description_ru": null,
                "description_en": null,
                "image": null,
                "active": false,
                "created_at": "2019-01-23 10:59:39",
                "updated_at": "2019-01-23 10:59:39"
            },
            {
                "id": 3,
                "title_ru": "TitleRurr",
                "title_en": "TitleEnrr",
                "description_ru": "DescriptionRuerter",
                "description_en": "DescriptionEnert",
                "image": "/storage/award_icons/UuRP6TXK030powUKPD0Rx2fWMdAHtGHBPzgmDgeU.jpeg",
                "active": false,
                "created_at": "2019-01-23 11:09:23",
                "updated_at": "2019-01-23 11:09:23"
            },
            {
                "id": 4,
                "title_ru": "TitleRurr",
                "title_en": "TitleEnrr",
                "description_ru": "DescriptionRuerter",
                "description_en": "DescriptionEnert",
                "image": null,
                "active": false,
                "created_at": "2019-01-23 11:21:18",
                "updated_at": "2019-01-23 11:21:18"
            }
        ]
    }
     *
     * @param Request $request
     * @return array
     */
    public function searchAward(Request $request)
    {
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;

        return $this->awardService->searchAward($page, $rowsPerPage, $searchKey);
    }

    /**
     * @api {post} /api/admin/award-object Добавление награды
     * @apiVersion 0.1.0
     * @apiName AddAward
     * @apiGroup AdminAwardObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} title_ru Название на русск.
     * @apiParam {string} title_en Название на анг.
     * @apiParam {string} [description_ru] Описание на русс.
     * @apiParam {string} [description_en] Описание на анг.
     * @apiParam {file} [image] Иконка награды
     * @apiParam {boolean} [active] активность
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 201 OK
    {
        "title_ru": "TitleRurr",
        "title_en": "TitleEnrr",
        "description_ru": "DescriptionRuerter",
        "description_en": "DescriptionEnert",
        "active": false,
        "updated_at": "2019-01-23 11:21:26",
        "created_at": "2019-01-23 11:21:26",
        "id": 7
    }
     *
     * @param Request $request
     * @return \App\Models\AwardIcon|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function addAward(Request $request)
    {
        $valid = Validator($request->all(),[
            'title_ru' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'image' => 'file|image|max:5128|nullable',
            'active' => 'boolean',
        ]);

        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        return $this->awardService->addAward($request);
    }

    /**
     * @api {post} /api/admin/award-object/{awardId} Редактирование награды
     * @apiVersion 0.1.0
     * @apiName EditAward
     * @apiGroup AdminAwardObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} [title_ru] Название на русск.
     * @apiParam {string} [title_en] Название на анг.
     * @apiParam {string} [description_ru] Описание на русс.
     * @apiParam {string} [description_en] Описание на анг.
     * @apiParam {file} [image] Иконка награды
     * @apiParam {boolean} [active] активность
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "services": [
            "Обновлено"
        ]
    }
     *
     * @param Request $request
     * @param int $awardId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function editAward(Request $request, int $awardId)
    {
        $valid = Validator($request->all(),[
            'title_ru' => 'string|max:255',
            'title_en' => 'string|max:255',
            'image' => 'file|image|max:5128|nullable',
            'active' => 'boolean'
        ]);

        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        $updated = $this->awardService->editAward($request, $awardId);
        if ($updated){

            return response()->json(['services' =>
                ['Обновлено']
            ], 200);
        } else {

            return response()->json(['services' =>
                ['Не найдено']
            ], 404);
        }

    }

    /**
     * @api {get} /api/admin/award-object Получение награды
     * @apiVersion 0.1.0
     * @apiName GetAward
     * @apiGroup AdminAwardObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 5,
        "title_ru": "TitleRurr",
        "title_en": "TitleEnrr",
        "description_ru": "DescriptionRuerter",
        "description_en": "DescriptionEnert",
        "image": null,
        "active": false,
        "created_at": "2019-01-23 11:21:21",
        "updated_at": "2019-01-23 11:21:21"
    }
     *
     * @param int $awardId
     * @return mixed
     */
    public function getAward(int $awardId)
    {
        return $this->awardService->getAward($awardId);
    }

    /**
     * @api {delete} /api/admin/award-object Удаление награды
     * @apiVersion 0.1.0
     * @apiName DeleteAward
     * @apiGroup AdminAwardObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "award": [
            "Награда отозвана"
        ]
    }
     *
     * @param int $awardId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAward(int $awardId)
    {
        $deleted = $this->awardService->deleteAward($awardId);
        if ($deleted){

            return response()->json(['services' =>
                ['Удалено']
            ], 200);
        } else {

            return response()->json(['services' =>
                ['Не найдено']
            ], 404);
        }
    }



    /**
     * @api {patch} /api/admin/award-object/object/{objectId}/award/{awardId} Присвоение награды объекту
     * @apiVersion 0.1.0
     * @apiName SetAward
     * @apiGroup AdminAwardObject
     *
     * @apiParam [object_self_award_id] Идентификатор награды загруженной объектом из ЛК (После сохранения автоматически скрывается из листинга)
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "award": [
            "Награда присуждена"
        ]
    }
     *
     * @param int $objectId
     * @param int $awardId
     * @return \Illuminate\Http\JsonResponse
     */
    public function setAward(Request $request, int $objectId, int $awardId)
    {
        $valid = Validator($request->all(), [
            'object_self_award_id' => 'integer|nullable'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $objectSelfAwardId = $request->get('object_self_award_id');
        if (!is_null($objectSelfAwardId)){
            ObjectAward::where('id', $objectSelfAwardId)->update(['is_new', false]);
        }

        $response = $this->awardService->setAward($objectId, $awardId);

        return response()->json($response['message'], $response['status']);
    }

    /**
     * @api {delete} /api/admin/award-object/object/{objectId}/award/{awardId} Отзыв награды объекта
     * @apiVersion 0.1.0
     * @apiName RevokeAward
     * @apiGroup AdminAwardObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "award": [
            "Награда отозвана"
        ]
    }
     *
     * @param int $objectId
     * @param int $awardId
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeAward(Request $request, int $objectId, int $awardId)
    {
        $valid = Validator($request->all(), [
            'object_self_award_id' => 'integer|nullable'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $objectSelfAwardId = $request->get('object_self_award_id');
        if (!is_null($objectSelfAwardId)){
            ObjectAward::where('id', $objectSelfAwardId)->update([ 'is_new', true ]);
        }

        $response = $this->awardService->revokeAward($objectId, $awardId);

        return response()->json($response['message'], $response['status']);
    }

    /**
     *
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNewObjectAwards(Request $request)
    {
        $valid = Validator($request->all(), [
           'page' => 'integer|nullable',
           'rowsPerPage' => 'integer|nullable',
           'searchKey' => 'nullable|nullable',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;

        $objects = $this->objectAwardService->getNewObjectAwards($page, $rowsPerPage, $searchKey);

        return response()->json($objects, 200);
    }

    /**
     * @api {get} /api/admin/award-object-self/ Получение списка наград объекта
     * @apiVersion 0.1.0
     * @apiName GetListSelfAward
     * @apiGroup AdminAwardObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 1,
        "items": [
            {
                "id": 1,
                "object_id": 33,
                "image": "/storage/awards/dlkBOIPdjArHPvTwpWnhY26YEbOX6WsNizeRAzF9.jpeg",
                "description": "tratatushki-tratata",
                "created_at": "2019-05-24 06:33:38",
                "updated_at": "2019-06-27 08:26:04",
                "is_new": true,
                "object": {
                    "id": 33,
                    "title_ru": "Санаторий Буран"
                }
            }
        ]
    }
     *
     * @param int $objectId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNewObjectAward(int $objectId )
    {
        $awards = $this->objectAwardService->get($objectId);

        return response()->json($awards, 200);
    }

    /**
     * @api {get} /api/admin/award-object-self/{object_self_award_id} Получение награды объекта
     * @apiVersion 0.1.0
     * @apiName GetSelfAward
     * @apiGroup AdminAwardObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 1,
        "object_id": 33,
        "image": "/storage/awards/dlkBOIPdjArHPvTwpWnhY26YEbOX6WsNizeRAzF9.jpeg",
        "description": "tratatushki-tratata",
        "created_at": "2019-05-24 06:33:38",
        "updated_at": "2019-06-27 08:26:04",
        "is_new": true,
        "object": {
            "id": 33,
            "title_ru": "Санаторий Буран"
        }
    }
     *
     * @param int $objectAwardId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getObjectSelfAward(int $objectAwardId)
    {
        $award = $this->objectAwardService->getObjectAward($objectAwardId);

        return response()->json($award, 200);
    }

    /**
     * @api {delete} /api/admin/award-object-self/object/{objectId}/award/{object_self_award_id} удалить награду из списка
     * @apiVersion 0.1.0
     * @apiName RemoveFromListAward
     * @apiGroup AdminAwardObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "award": [
            "Награда объекта скрыта"
        ]
    }
     *
     * @param int $objectId
     * @param int $objectAwardId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function removeNewObjectAward(int $objectId, int $objectAwardId)
    {
        $data['is_new'] = false;
        $this->objectAwardService->update($data, $objectAwardId, $objectId);

        return response()->json(['awards' => [
            'Награда объекта скрыта'
        ]], 200);
    }
}
