<?php

namespace App\Http\Controllers\Api\Admin\Role;

use App\Rules\IsArray;
use App\Services\RoleService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    /**
     * @var RoleService
     */
    protected $roleService;

    /**
     * RoleController constructor.
     */
    public function __construct()
    {
        $this->roleService = new RoleService();
    }

    /**
     * @api {get} /api/admin/role Получение списка ролей
     * @apiVersion 0.1.0
     * @apiName ListRole
     * @apiGroup AdminRoles
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Массив сортировки {"title_ru": "asc", "modified_at": "desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 4,
        "items": [
            {
                "id": 2,
                "name": "Пациент",
                "description": "Простой пользователь",
                "slug": "patient"
            },
            {
                "id": 3,
                "name": "Объект",
                "description": "Владелец объекта",
                "slug": "object"
            },
            {
                "id": 4,
                "name": "Партнер",
                "description": "Портнер проекта",
                "slug": "partner"
            },
            {
                "id": 1,
                "name": "Администратор",
                "description": "Администратор с высокими привилегиями",
                "slug": "admin"
            }
        ]
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $serachKey = $request->get('searchKey');
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);

        $roles = $this->roleService->search($page, $rowsPerPage, $sorting, $serachKey);

        return response()->json($roles, 200);
    }

    /**
     * @api {get} /api/admin/role/{roleId} Получение роли
     * @apiVersion 0.1.0
     * @apiName GetRole
     * @apiGroup AdminRoles
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 1,
        "name": "Администратор",
        "description": "Администратор с высокими привилегиями",
        "slug": "admin",
        "permissions": {
            "admin_panel": true,
            "object_read": true,
            "object_write": true,
            "room_read": true,
            "room_write": true,
            "user_read": true,
            "user_write": true,
            "news_read": true,
            "news_write": true,
            "moderation_object": true,
            "moderation_partner": true,
            "medicine_read": true,
            "medicine_write": true,
            "geography_read": true,
            "geography_write": true,
            "main_page_read": true,
            "main_page_write": true,
            "service_read": true,
            "service_write": true,
            "award_read": true,
            "award_write": true,
            "seo_read": true,
            "seo_write": true,
            "about_read": true,
            "about_write": true,
            "faq_read": true,
            "faq_write": true,
            "banner_read": true,
            "banner_write": true,
            "partner_read": true,
            "partner_write": true,
            "publication_read": true,
            "publication_write": true
        },
        "created_at": null,
        "updated_at": null
    }
     *
     * @param int $roleId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function get(int $roleId)
    {
        $role = $this->roleService->get($roleId);

        return response()->json($role, 200);
    }

    /**
     * @api {post} /api/admin/role Создание роли
     * @apiVersion 0.1.0
     * @apiName AddRole
     * @apiGroup AdminRoles
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} name Название роли
     * @apiParam {string} description Краткое описание
     * @apiParam {json} permissions объект с разрешениями
     * ({
            "admin_panel": false,
            "object_read": false
    })
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 8,
        "name": "Новая роль",
        "description": "Роль эта новая",
        "slug": "novaya-rol",
        "permissions": {
            "admin_panel": false,
            "object_read": false,
            "object_write": false,
            "room_read": false,
            "room_write": false,
            "user_read": false,
            "user_write": false,
            "news_read": false,
            "news_write": false,
            "moderation_object": false,
            "moderation_partner": false,
            "medicine_read": false,
            "medicine_write": false,
            "geography_read": false,
            "geography_write": false,
            "main_page_read": false,
            "main_page_write": false,
            "service_read": false,
            "service_write": false,
            "award_read": true,
            "award_write": false,
            "seo_read": false,
            "seo_write": false,
            "about_read": false,
            "about_write": true,
            "faq_read": false,
            "faq_write": false,
            "banner_read": false,
            "banner_write": false,
            "partner_read": false,
            "partner_write": false,
            "publication_read": false,
            "publication_write": false
        },
        "created_at": "2019-07-09 12:24:58",
        "updated_at": "2019-07-09 12:24:58"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function add(Request $request)
    {
        $valid = Validator($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255|nullable',
            'permissions' => [ 'present', new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $data = $request->only('name', 'description', 'permissions');
        $role = $this->roleService->add($data);

        return response()->json($role, 201);
    }

    /**
     * @api {put} /api/admin/role/{roleId} Редактирование роли
     * @apiVersion 0.1.0
     * @apiName EditRole
     * @apiGroup AdminRoles
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} name Название роли
     * @apiParam {string} description Краткое описание
     * @apiParam {json} permissions объект с разрешениями
     * ({
        "admin_panel": false,
        "object_read": false
    })
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 8,
        "name": "Новая роль",
        "description": "Роль эта новая",
        "slug": "novaya-rol",
        "permissions": {
            "admin_panel": false,
            "object_read": false,
            "object_write": false,
            "room_read": false,
            "room_write": false,
            "user_read": false,
            "user_write": false,
            "news_read": false,
            "news_write": false,
            "moderation_object": false,
            "moderation_partner": false,
            "medicine_read": false,
            "medicine_write": false,
            "geography_read": false,
            "geography_write": false,
            "main_page_read": false,
            "main_page_write": false,
            "service_read": false,
            "service_write": false,
            "award_read": true,
            "award_write": false,
            "seo_read": false,
            "seo_write": false,
            "about_read": false,
            "about_write": true,
            "faq_read": false,
            "faq_write": false,
            "banner_read": false,
            "banner_write": false,
            "partner_read": false,
            "partner_write": false,
            "publication_read": false,
            "publication_write": false
        },
        "created_at": "2019-07-09 12:24:58",
        "updated_at": "2019-07-09 12:24:58"
    }
     *
     * @param Request $request
     * @param int $roleId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function edit(Request $request, int $roleId)
    {
        $valid = Validator($request->all(), [
            'name' => 'string|max:255',
            'description' => 'string|max:255|nullable',
            'permissions' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $data = $request->only('name', 'description', 'permissions');
        $role = $this->roleService->edit($data, $roleId);

        return response()->json($role, 200);
    }

    /**
     * @api {delete} /api/admin/role/{roleId} Удаление роли
     * @apiVersion 0.1.0
     * @apiName deleteRole
     * @apiGroup AdminRoles
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Роль удалена"
    }
     *
     * @param int $roleId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function delete(int $roleId)
    {
        $this->roleService->delete($roleId);

        return response()->json(['message' => 'Роль удалена'], 200);
    }

    /**
     * @api {get} /api/admin/role/permissions Получение разрешений
     * @apiVersion 0.1.0
     * @apiName ListPermissions
     * @apiGroup AdminRoles
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "name": "Вход в админку",
            "description": "",
            "slug": "admin_panel"
        },
        {
            "name": "Просмотр объектов",
            "description": "",
            "slug": "object_read"
        },
        {
            "name": "Редактирование объектов",
            "description": "",
            "slug": "object_write"
        },
        {
            "name": "Просмотр номеров",
            "description": "",
            "slug": "room_read"
        },
        {
            "name": "Редактирование номеров",
            "description": "",
            "slug": "room_write"
        },
        {
            "name": "Просмотр пользователей",
            "description": "",
            "slug": "user_read"
        },
        {
            "name": "Редактирование пользователей",
            "description": "",
            "slug": "user_write"
        },
        {
            "name": "Просмотр новостей",
            "description": "",
            "slug": "news_read"
        },
        {
            "name": "Редактирование новостей",
            "description": "",
            "slug": "news_write"
        },
        {
            "name": "Модерация объектов",
            "description": "",
            "slug": "moderation_object"
        },
        {
            "name": "Модерация партнеров",
            "description": "",
            "slug": "moderation_partner"
        },
        {
            "name": "Просмотр медицины",
            "description": "",
            "slug": "medicine_read"
        },
        {
            "name": "Редактирование медицины",
            "description": "",
            "slug": "medicine_write"
        },
        {
            "name": "Просмотр географии",
            "description": "",
            "slug": "geography_read"
        },
        {
            "name": "Редактирование географии",
            "description": "",
            "slug": "geography_write"
        },
        {
            "name": "Просмотр главной страницы",
            "description": "",
            "slug": "main_page_read"
        },
        {
            "name": "Редактирование главной страницы",
            "description": "",
            "slug": "main_page_write"
        },
        {
            "name": "Просмотр услуг",
            "description": "",
            "slug": "service_read"
        },
        {
            "name": "Редактирование услуг",
            "description": "",
            "slug": "service_write"
        },
        {
            "name": "Просмотр наград",
            "description": "",
            "slug": "award_read"
        },
        {
            "name": "Редактирование наград",
            "description": "",
            "slug": "award_write"
        },
        {
            "name": "Просмотр SEO",
            "description": "",
            "slug": "seo_read"
        },
        {
            "name": "Редактирование SEO",
            "description": "",
            "slug": "seo_write"
        },
        {
            "name": "Просмотр О проекте",
            "description": "",
            "slug": "about_read"
        },
        {
            "name": "Редактирование О проекте",
            "description": "",
            "slug": "about_write"
        },
        {
            "name": "Просмотр FAQ",
            "description": "",
            "slug": "faq_read"
        },
        {
            "name": "Редактироване FAQ",
            "description": "",
            "slug": "faq_write"
        },
        {
            "name": "Просмотр баннеров",
            "description": "",
            "slug": "banner_read"
        },
        {
            "name": "Редактирование баннеров",
            "description": "",
            "slug": "banner_write"
        },
        {
            "name": "Просмотр партнеров",
            "description": "",
            "slug": "partner_read"
        },
        {
            "name": "Редактирование партнеров",
            "description": "",
            "slug": "partner_write"
        },
        {
            "name": "Просмотр публикаций партнеров",
            "description": "",
            "slug": "publication_read"
        },
        {
            "name": "Редактирование публикаций партнеров",
            "description": "",
            "slug": "publication_write"
        },
        {
            "name": "Просмотр ролей",
            "description": "",
            "slug": "role_read"
        },
        {
            "name": "Редактирование ролей",
            "description": "",
            "slug": "role_write"
        },
        {
            "name": "Просмотр спецпредложений",
            "description": "",
            "slug": "offer_read"
        },
        {
            "name": "Редактирование спецпредложений",
            "description": "",
            "slug": "offer_write"
        },
        {
            "name": "Просмотр активности на проекте",
            "description": "",
            "slug": "activity_read"
        },
        {
            "name": "Просмотр справочников",
            "description": "",
            "slug": "some_directory_read"
        },
        {
            "name": "Редактирование справочников",
            "description": "",
            "slug": "some_directory_write"
        },
        {
            "name": "Просмотр брони",
            "description": "",
            "slug": "reservation_read"
        },
        {
            "name": "Редактирование брони",
            "description": "",
            "slug": "reservation_write"
        },
        {
            "name": "Просмотр отзывов",
            "description": "",
            "slug": "feedback_read"
        },
        {
            "name": "Редактирование отзывов",
            "description": "",
            "slug": "feedback_write"
        },
        {
            "name": "Просмотр mood-тегов",
            "description": "",
            "slug": "mood_read"
        },
        {
            "name": "Редактирование mood-тегов",
            "description": "",
            "slug": "mood_write"
        }
    ]
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPermissions()
    {
        $permissions = $this->roleService->getPermissions();

        return response()->json($permissions, 200);
    }
}
