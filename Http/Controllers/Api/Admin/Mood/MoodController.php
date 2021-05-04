<?php

namespace App\Http\Controllers\Api\Admin\Mood;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\MoodService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MoodController extends Controller
{
    /**
     * @var MoodService
     */
    protected $moodService;

    /**
     * MoodController constructor.
     */
    public function __construct()
    {
        $this->moodService = new MoodService();
    }

    /**
     * @api {get} /api/admin/mood Получение и поиск mood-тегов
     * @apiVersion 0.1.0
     * @apiName SearchMood
     * @apiGroup AdminMood
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {string} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Сортировка {"id": "desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 2,
        "total": 9,
        "items": [
            {
                "id": 15,
                "name_ru": "Наименование mood-тега 1",
                "name_en": "mood tag name 1",
                "alias": "alias1",
                "image": "/storage/moods/Ynfs0iCCXWVbj2FpqrTmFTeXswjwvyQeyFQmi3U7.jpeg",
                "crop_image": "/storage/moods_crop/Ynfs0iCCXWVbj2FpqrTmFTeXswjwvyQeyFQmi3U7.jpeg",
                "created_at": "2020-04-30 10:18:44",
                "updated_at": "2020-04-30 10:18:44"
            },
            {
                "id": 14,
                "name_ru": "Наименование mood-тега 1",
                "name_en": "mood tag name 1",
                "alias": "alias1",
                "image": "/storage/moods/vhAugkBBcd20sweFubdIgaEYjvrqq7DX2CaapYPs.jpeg",
                "crop_image": "/storage/moods_crop/vhAugkBBcd20sweFubdIgaEYjvrqq7DX2CaapYPs.jpeg",
                "created_at": "2020-04-30 10:18:12",
                "updated_at": "2020-04-30 10:18:12"
            }
        ]
    }
     *
     * @param Request|null $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function search(?Request $request)
    {
        $valid = Validator($request->only('page', 'rowsPerPage', 'searchKey'),[
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 50;
        $searchKey = $request->get('searchKey') ?? null;
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($request->get('sorting'), true) ?? null;

        return $this->moodService->search($page, $rowsPerPage, $searchKey, $sorting);
    }

    /**
     * @api {post} /api/admin/mood Создание нового mood-тега
     * @apiVersion 0.1.0
     * @apiName CreateMood
     * @apiGroup AdminMood
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string}  name_ru наименование mood-тега на русском
     * @apiParam {string}  [name_en] наименование mood-тега на английском
     * @apiParam {string}  alias алиас mood-тега
     * @apiParam {file}  [image] файл изображения
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK

    {
        "image": "/storage/moods/wRyID3rub8ldAC7f8SmNGSm60kywqSlhfQhRCQHX.jpeg",
        "crop_image": "/storage/moods_crop/wRyID3rub8ldAC7f8SmNGSm60kywqSlhfQhRCQHX.jpeg",
        "name_ru": "Наименование mood-тега",
        "name_en": "mood tag name",
        "alias": "alias1",
        "updated_at": "2020-04-30 11:16:41",
        "created_at": "2020-04-30 11:16:41",
        "id": 16
    }

     *
     * @param Request $request
     * @return \App\Models\Offer|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws
     */
    public function create(Request $request)
    {
        $valid = Validator($request->all(),[
            'image' => 'file|image|max:5128|nullable',
            'name_ru' => 'required|string|max:255',
            'name_en' => 'string|max:255',
            'alias' => 'required|string|max:255',
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        return $this->moodService->create($request);
    }

    /**
     * @api {get} /api/admin/mood/{moodId} Получение mood-тега
     * @apiVersion 0.1.0
     * @apiName GetMood
     * @apiGroup AdminMood
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 16,
        "name_ru": "Наименование mood-тега",
        "name_en": "mood tag name",
        "alias": "alias1",
        "image": "/storage/moods/wRyID3rub8ldAC7f8SmNGSm60kywqSlhfQhRCQHX.jpeg",
        "crop_image": "/storage/moods_crop/wRyID3rub8ldAC7f8SmNGSm60kywqSlhfQhRCQHX.jpeg",
        "created_at": "2020-04-30 11:16:41",
        "updated_at": "2020-04-30 11:16:41"
    }
     * @param int $moodId
     * @return mixed
     * @throws ApiProblemException
     */
    public function get(int $moodId)
    {
        $mood = $this->moodService->get($moodId);

        return response()->json($mood, 200);
    }

    /**
     * @api {delete} /api/admin/mood/{moodId} Удаление mood-тега
     * @apiVersion 0.1.0
     * @apiName DeleteMood
     * @apiGroup AdminMood
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "mood": [
            "Mood-тег удален"
        ]
    }
     *
     * @param int $moodId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException

     */
    public function delete(int $moodId)
    {
        $deleted = $this->moodService->delete($moodId);

        if ($deleted) {
            return response()->json(['mood' => [
                'Mood-тег удален'
            ]], 200);

        } else {
            return response()->json(['mood' => [
                'Mood-тег не удален'
            ]], 404);
        }
    }

    /**
     * @api {post} /api/admin/mood/{moodId} Редактирование mood-тега
     * @apiVersion 0.1.0
     * @apiName EditMood
     * @apiGroup AdminMood
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string}  [name_ru] наименование mood-тега на русском
     * @apiParam {string}  [name_en] наименование mood-тега на английском
     * @apiParam {string}  [alias] алиас mood-тега
     * @apiParam {file}  [image] файл изображения.
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 15,
        "name_ru": "Наименование mood-тега 2",
        "name_en": "mood tag name 2",
        "alias": "aliasNew",
        "image": "/storage/moods/6F2zllQ80ME8qSB98Yeb7aXnMbjtOduTC7EhG8pG.jpeg",
        "crop_image": "/storage/moods_crop/6F2zllQ80ME8qSB98Yeb7aXnMbjtOduTC7EhG8pG.jpeg",
        "created_at": "2020-04-30 10:18:44",
        "updated_at": "2020-04-30 11:19:33"
    }
     *
     * @param Request $request
     * @param int $offerId
     * @return bool|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function update(Request $request, int $moodId)
    {
        $valid = Validator($request->all(),[
            'image' => 'file|image|max:5128',
            'name_ru' => 'string|max:255',
            'name_en' => 'string|max:255',
            'alias' => 'string|max:255',
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $mood = $this->moodService->update($request, $moodId);

        return response()->json($mood, 200);
    }


    /**
     * @api {delete} /api/admin/mood/{moodId}/image Удаление изображения у mood-тега
     * @apiVersion 0.1.0
     * @apiName DeleteImageMood
     * @apiGroup AdminMood
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "mood": [
            "Изображение удалено"
        ]
    }
     *
     * @param int $moodId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function deleteImage(int $moodId)
    {
        $deleted = $this->moodService->deleteImage($moodId);
        if ($deleted){

            return response()->json(['mood' =>[
                'Изображение удалено'
            ]], 200);
        } else {

            return response()->json(['mood' =>[
                'Изображение не найдено'
            ]], 404);
        }
    }
}
