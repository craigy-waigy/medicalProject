<?php

namespace App\Http\Controllers\Api\Filter;

use App\Models\NewsScope;
use App\Models\PageScope;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ScopeController extends Controller
{
    /**
     * @api {get} /api/filter/scope получение списка видимости
     * @apiVersion 0.1.0
     * @apiName GetScope
     * @apiGroup Filter
     *
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 3,
            "name": "для главврачей",
            "slug": "for_hed_doctors",
            "role_id": null
        },
        {
            "id": 4,
            "name": "для врачей",
            "slug": "for_doctors",
            "role_id": null
        },
        {
            "id": 5,
            "name": "для партнеров",
            "slug": "for_partners",
            "role_id": null
        },
        {
            "id": 1,
            "name": "для пациентов",
            "slug": "for_patients",
            "role_id": 2
        },
        {
            "id": 2,
            "name": "для объектов",
            "slug": "for_objects",
            "role_id": 3
        }
    ]
     *
     * @return mixed
     */
    public function getScope()
    {
        return NewsScope::select(['id', 'name', 'slug', 'role_id'])->get();
    }

    /**
     * @api {get} /api/filter/page-scope получение списка видимости для страниц
     * @apiVersion 0.1.0
     * @apiName GetPageScope
     * @apiGroup Filter
     *
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 1,
            "name": "Главная",
            "slug": "main",
            "description": "Показывать на главной странице"
        },
        {
            "id": 2,
            "name": "Заболевания",
            "slug": "disease",
            "description": "Показывать на странице заболеваний"
        },
        {
            "id": 3,
            "name": "Методы лечения",
            "slug": "therapy",
            "description": "Показывать на странице методы лечения"
        },
        {
            "id": 4,
            "name": "Профили лечения",
            "slug": "medical_profile",
            "description": "Показывать на странице профили лечения"
        },
        {
            "id": 5,
            "name": "География",
            "slug": "geography",
            "description": "Показывать на страницах географии (города, региоа, страны)"
        },
        {
            "id": 6,
            "name": "Новости",
            "slug": "news",
            "description": "Показывать на странице новости"
        },
        {
            "id": 7,
            "name": "О проекте",
            "slug": "about",
            "description": "Показывать на странице \"О проекте\""
        },
        {
            "id": 8,
            "name": "Личный кабинет",
            "slug": "account",
            "description": "Показывать в личном кабинете пользователя"
        },
        {
            "id": 9,
            "name": "Публикации",
            "slug": "publication",
            "description": "Показывать на странице публикации"
        },
        {
            "id": 10,
            "name": "Статьи",
            "slug": "article",
            "description": "Показывать на странице статьи"
        },
        {
            "id": 11,
            "name": "Спецпредложения",
            "slug": "offer",
            "description": "Показывать на странице спецпредложения"
        }
    ]
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPageScope()
    {
        $pageScopes = PageScope::all();

        return response()->json($pageScopes, 200);
    }
}
