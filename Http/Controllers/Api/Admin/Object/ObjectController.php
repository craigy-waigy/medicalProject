<?php

namespace App\Http\Controllers\Api\Admin\Object;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\ObjectService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Token;

class ObjectController extends Controller
{
    protected $objectService;

    public function __construct()
    {
        $this->objectService = new ObjectService();
    }

    /**
     * Display a listing of the resource.
     *
     * @api {get} /api/admin/object Получение и поиск объектов
     * @apiVersion 0.1.0
     * @apiName SearchObject
     * @apiGroup AdminObject
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
        "total": 5,
        "items": [
            {
                "id": 44,
                "country_id": 18,
                "region_id": 4388,
                "city_id": 118006,
                "title_ru": "Санаторий Сосновый бор",
                "alias": "sosnoviy-bor",
                "is_visibly": true,
                "created_at": null,
                "modified_at": null,
                "showcase_rooms_count": "13",
                "expensive": false,
                "heating_rating": "0",
                "full_rating": "0",
                "viewing_count": 3,
                "has_default_diseases_only": true,
                "country": {
                    "id": 18,
                    "name_ru": "Ливан"
                },
                "region": {
                    "id": 4388,
                    "name_ru": "Ujar Rayon"
                },
                "city": {
                    "id": 118006,
                    "name_ru": "Сан-Хосе"
                }
            }
        ]
    }
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $sorting = json_decode($request->get('sorting'), true) ?? null;
        $searchKey = $request->get('searchKey') ?? null;

        $objects = $this->objectService->getAll($page, $rowsPerPage, $sorting, $searchKey);

        return response()->json($objects, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @api {post} /api/admin/object Сохранение нового объекта
     * @apiVersion 0.1.0
     * @apiName CreateObject
     * @apiGroup AdminObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {String}  title_ru Название объекта
     * @apiParam {String}  [title_en] Название объекта
     * @apiParam {boolean} [is_visibly] видимость на страницах
     * @apiParam {integer} [country_id] ID страны
     * @apiParam {integer} [region_id] ID региона
     * @apiParam {integer} [city_id] ID города
     * @apiParam {string}  [lat] широта
     * @apiParam {string}  [lon] долгота
     * @apiParam {string}  [zip] почтовый индекс
     * @apiParam {string}  [address] адрес
     * @apiParam {string}  [description_ru] описание на русском
     * @apiParam {string}  [description_en] описание на англ.
     * @apiParam {string}  [documents_ru] документы на русском.
     * @apiParam {string}  [documents_en] описание на англ.
     * @apiParam {string}  [visa_information_ru] описание визы на русс.
     * @apiParam {string}  [visa_information_en] описание визы на анг.
     * @apiParam {string}  [contraindications_ru] противопоказания на русс.
     * @apiParam {string}  [contraindications_en] противопоказания на анг.
     * @apiParam {string}  [street_view_link] ссылка на вид с улицы
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
        HTTP/1.1 200 OK
    {
        "id": 2,
        "user_id": null,
        "title_ru": "название объекта",
        "title_en": "test",
        "is_visibly": 1,
        "country_id": 1,
        "region_id": 1,
        "city_id": 1,
        "lat": "42.34543",
        "lon": "43.434535",
        "address": "адрес объекта",
        "zip": "213432",
        "description_ru": "описание объекта",
        "description_en": null,
        "documents_ru": "документы для объекта",
        "documents_en": null,
        "visa_information_ru": "информация по визе объекта",
        "visa_information_en": null,
        "contraindications_ru": "противопоказания на объекта",
        "contraindications_en": null,
        "showcase_rooms_count": "2",
        "created_at": "2018-11-16 11:48:58",
        "modified_at": "2018-11-16 11:48:58",
        "is_deleted": 0,
        "deleted_at": null,
        "street_view_link": "ссылка на объект с улицы",
        "images": [
            {
                "id": 21,
                "object_id": 2,
                "moderationStatus": 1,
                "image": "2_NTutm8Avjn.JPG",
                "sorting_rule": null,
                "description": "qwerty",
                "is_main": 0,
                "created_at": "2018-11-19 10:13:35",
                "updated_at": "2018-11-19 11:19:06",
                "image_link": "/backend/object_images/2_NTutm8Avjn.JPG"
            }

        ],
        "country": {
            "id": 1,
            "name_ru": "Россия",
            "name_en": "Russia",
            "created_at": null,
            "updated_at": null
        },
        "region": {
            "id": 1,
            "name_ru": "Краснодарский край",
            "name_en": "Krasnodar region",
            "country_id": 1,
            "created_at": null,
            "updated_at": null
        },
        "city": {
            "id": 1,
            "name_ru": "Сочи",
            "name_en": "Sochi",
            "region_id": 1,
            "country_id": 1,
            "created_at": null,
            "updated_at": null
        }
        "award_icons": [
            {
                "id": 1,
                "title_ru": "NmaeRu",
                "title_en": "NmaeEn",
                "description_ru": null,
                "description_en": null,
                "image": null,
                "created_at": "2019-01-23 10:59:39",
                "updated_at": "2019-01-23 10:59:39",
                "active": false
            }
        ],
        "therapies": [],
        "medical_profiles": []
    }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function store(Request $request)
    {
        $object = $request->only('user_id', 'title_ru', 'title_en', 'is_visibly', 'country_id', 'region_id',
            'city_id', 'lat', 'lon', 'address', 'zip', 'description_ru', 'description_en', 'documents_ru', 'documents_en',
            'visa_information_ru', 'visa_information_en', 'contraindications_ru', 'contraindications_en', 'capabilities',
            'street_view_link'
        );

        $valid = Validator($object, [
            'country_id' => 'integer|nullable',
            'region_id' => 'integer|nullable',
            'city_id' => 'integer|nullable',
            'is_visibly' => 'boolean',
            'title_ru' => 'required|max:255',
            'title_en' => 'max:255',
            'lat' => 'max:255',
            'lon' => 'max:255',
            'zip' => 'max:10|nullable',
            'user_id' => 'integer|nullable',
            'street_view_link' => 'max:255|nullable',
        ]);

        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        $object = $this->objectService->createObject($object);

        return response()->json($object, 201);
    }

    /**
     * Display the specified resource.
     *
     * @api {get} /api/admin/object/{objectId} получение объекта
     * @apiVersion 0.1.0
     * @apiName GetObject
     * @apiGroup AdminObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
        HTTP/1.1 200 OK

    {
        "id": 2,
        "user_id": null,
        "title_ru": "название объекта",
        "title_en": "test",
        "is_visibly": 1,
        "country_id": 1,
        "region_id": 1,
        "city_id": 1,
        "lat": "42.34543",
        "lon": "43.434535",
        "address": "адрес объекта",
        "zip": "213432",
        "description_ru": "описание объекта",
        "description_en": null,
        "documents_ru": "документы для объекта",
        "documents_en": null,
        "visa_information_ru": "информация по визе объекта",
        "visa_information_en": null,
        "contraindications_ru": "противопоказания на объекта",
        "contraindications_en": null,
        "showcase_rooms_count": "2",
        "created_at": "2018-11-16 11:48:58",
        "modified_at": "2018-11-16 11:48:58",
        "is_deleted": 0,
        "deleted_at": null,
        "images": [
            {
            "id": 21,
            "object_id": 2,
            "moderationStatus": 1,
            "image": "2_NTutm8Avjn.JPG",
            "sorting_rule": null,
            "description": "qwerty",
            "is_main": 0,
            "created_at": "2018-11-19 10:13:35",
            "updated_at": "2018-11-19 11:19:06",
            "image_link": "/backend/object_images/2_NTutm8Avjn.JPG"
            }

        ],
        "country": {
            "id": 1,
            "name_ru": "Россия",
            "name_en": "Russia",
            "created_at": null,
            "updated_at": null
        },
        "region": {
            "id": 1,
            "name_ru": "Краснодарский край",
            "name_en": "Krasnodar region",
            "country_id": 1,
            "created_at": null,
            "updated_at": null
        },
        "city": {
            "id": 1,
            "name_ru": "Сочи",
            "name_en": "Sochi",
            "region_id": 1,
            "country_id": 1,
            "created_at": null,
            "updated_at": null
        }
        "award_icons": [
            {
                "id": 1,
                "title_ru": "NmaeRu",
                "title_en": "NmaeEn",
                "description_ru": null,
                "description_en": null,
                "image": null,
                "created_at": "2019-01-23 10:59:39",
                "updated_at": "2019-01-23 10:59:39",
                "active": false
            }
         ],
        "therapies": [
            {
                "id": 78,
                "name_ru": "Миоэлектростимуляция",
                "name_en": ""
            },
            {
                "id": 176,
                "name_ru": "Прием (осмотр, консультация) врача-кардиолога",
                "name_en": "Reception (examination, consultation) Cardiologist"
            }
        ],
        "medical_profiles": [
            {
                "id": 10,
                "name_ru": "Болезни желудочно-кишечного тракта",
                "name_en": "Diseases of the gastrointestinal tract",
                "has_default_diseases_only": false,
                "diseases_count": 3,
                "not_default_diseases_count": 3
            },
            {
                "id": 5,
                "name_ru": "Болезни нервной системы",
                "name_en": "Diseases of the nervous system",
                "has_default_diseases_only": true,
                "diseases_count": 1,
                "not_default_diseases_count": 0
            }
        ]
    }

     *
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function show($id)
    {
        return $this->objectService->getObject($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @api {put} /api/admin/object/{objectId} Редактирование объекта
     * @apiVersion 0.1.0
     * @apiName UpdateObject
     * @apiGroup AdminObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {String}  [title_ru] Название объекта
     * @apiParam {String}  [title_en] Название объекта
     * @apiParam {boolean} [is_visibly] видимость на страницах
     * @apiParam {integer} [country_id] ID страны
     * @apiParam {integer} [region_id] ID региона
     * @apiParam {integer} [city_id] ID города
     * @apiParam {string}  [lat] широта
     * @apiParam {string}  [lon] долгота
     * @apiParam {string}  [zip] почтовый индекс
     * @apiParam {string}  [address] адрес
     * @apiParam {string}  [description_ru] описание на русском
     * @apiParam {string}  [description_en] описание на англ.
     * @apiParam {string}  [documents_ru] документы на русском.
     * @apiParam {string}  [documents_en] описание на англ.
     * @apiParam {string}  [visa_information_ru] описание визы на русс.
     * @apiParam {string}  [visa_information_en] описание визы на анг.
     * @apiParam {string}  [contraindications_ru] противопоказания на русс.
     * @apiParam {string}  [contraindications_en] противопоказания на анг.
     * @apiParam {string}  [payment_description_ru] условия оплаты на рус.
     * @apiParam {string}  [payment_description_en] условия оплаты на анг.
     * @apiParam {array}   [services] массив ID услуг [2, 4, 56, 76, 5]
     * @apiParam {array}   [award_icons] массив ID наград [2, 4, 56, 76, 5]
     * @apiParam {array}   [medical_profiles] массив ID мед. профилей  [1, 3, 5, 7]
     * @apiParam {array}   [therapies] массив ID методов лечений  [1, 3, 5, 7]
     * @apiParam {array}   [moods] массив ID mood тегов  [1, 2]
     * @apiParam {integer} [stars]   целочисленное количество звезд
     * @apiParam {date} [year_of_found]    Дата основания
     * @apiParam {date} [year_of_reconstruction]    Дата последней реконструкции
     * @apiParam {string} [bankomats]    Банкоматы
     * @apiParam {string} [discount_cards]    Наличае дисконтных карт / описание
     * @apiParam {string} [partnership_programs]    В каких партнерских программах учавствует
     * @apiParam {string} [early_check_in]    Условия раннего заезда
     * @apiParam {string} [late_check_out]    Условия позднего выезда
     * @apiParam {string} [other_rules]    Дополнительные условия
     * @apiParam {json} [commission]    Дополнительная комиссия
     * @apiParam {string} [card_types]    Какие типы карт принимаются
     * @apiParam {string} [taxes]    Какие налоги включены в стоимость
     * @apiParam {json} [other_taxes]    Дополнительные налоги. {"existence": false, "description": ""}
     * @apiParam {string} [service_contacts]    Контакты основных служб
     * @apiParam {string} [route_description]    Как добраться от ближайшего аэропорта/вокзала
     * @apiParam {json} [contacts]    Контакты. {"fax": "номер факса", "email": "example@main.com", "telephone": "номер телофона", "messengers": {"skype": "", "telegram": "", "whatsapp": ""}}
     * @apiParam {boolean} [expensive] Не дорого
     * @apiParam {float} [heating_rating] Рейтинг лечения
     * @apiParam {float} [full_rating] Полный рейтинг
     * @apiParam {array} [tags_ru] Тэги для поиска RU
     * @apiParam {array} [tags_en] Тэги для поиска EN
     *
     *
     *
     * @apiParam {string} [head_doctor_name]    Главный врач (ФИО)
     * @apiParam {string} [head_doctor_regalia]    Регалии и звания врача
     * @apiParam {json} [climatic_factors]    Климатические факторы ["приморский", "горный", "предгорный", "лесостепной зоны"]
     * @apiParam {json} [water]    Вода {"water_type": [], "drinking_water_type": [], "chemical_composition": ""}
     * @apiParam {json} [pump_room]    Бювет {"type": "", "location": "", "existence": false}
     * @apiParam {json} [healing_mud]    Лечебная грязь ["торфяная", "сульфидная иловая", "сапропелевая"]
     * @apiParam {string} [other_health_information]    Другие природные факторы
     * @apiParam {bool} [voucher_without_accommodation]    Возможно ли приобретение курсовок? (курса лечене без проживания)
     * @apiParam {integer} [personal_count]    Общая численность персонала
     * @apiParam {json} [certified_personal]    Сертифицированные специалисты. ["Аллерголог-иммунолог", "Гинеколог", "Врач восстановительной медицины"]
     * @apiParam {string} [medical_equipment]    Медицинское оборудование в лечебном корпусе
     * @apiParam {string} [unique_therapy]    Уникальные методики лечения
     * @apiParam {string} [paid_therapy]    Платные мед. Услуги не входящие в один пакет/путевку
     * @apiParam {string} [paid_spa]    Платные СПА и велнесс-услуги, не входящие в один пакет/путевку
     * @apiParam {bool} [exist_reanimation]    Наличае реанимации
     * @apiParam {json} [operation_in_object]    Операции в объекте. {"existence": false, "description": ""}
     * @apiParam {json} [drinking_water_plumbing]    Питьевая вода в водопроводе. {"existence": false, "description": "Если не пригодна, описание как использовать воду из под крана"}
     *
     *
     *
     * @apiParam {bool} [working_by_season]    Функционирование объекта, сезонно или круглый год
     * @apiParam {json} [season_period]    период сезона, массив с месяцами работы ["January", "February"]
     * @apiParam {json} [months_peak]    Месяцы набольшей загрузки ["January", February"]
     * @apiParam {json} [months_lows]    Месяцы наименьшей загрузки ["January", February"]
     * @apiParam {json} [effective_months]    Эффективные месяцы лечения  ["January", February"]
     * @apiParam {json} [contingent]    Контингент. ["Взрослые", "Дети", "Дети без родителей", "Родители с детьми"]
     * @apiParam {json} [territory]    Территория. {"area": "", "corps": {"floors": "", "number": "", "passages": "", "specific": ""}, "relief": ["Ровный", "Холмистый", "Смешанный"], "description": "", "infrastructures": "", "steps_by_territory": ""}
     * @apiParam {string} [elevators]    Наличае лифтов, и их описание
     * @apiParam {bool} [has_electro_cars]    Наличае электрокаров
     * @apiParam {json} [reservoir]    Водоемы {"name": "", "type": "", "beach": {"type": "", "existence": false, "availability": ""}, "transfer": false, "existence": false, "remoteness": "", "description": ""}
     * @apiParam {json} [pools]    Бассейны. {"type": [], "water": {"temperature": "", "cleaning_period": ""}, "existence": false, "descriptions": "", "additional_equipment": ""}
     * @apiParam {bool} [lifesavers]    Наличие спасателей
     * @apiParam {bool} [training_apparatuses]    Тренажоры
     * @apiParam {json} [for_disabilities]    Инфраструктура для инвалидов ["Например лифты", "бассейны", "Что-то еще"]
     * @apiParam {bool} [changing_rooms]    Наличае раздевалок
     * @apiParam {bool} [instructors]    Наличае инструкторов
     * @apiParam {json} [parking]    Парковка. {"cost": "", "existence": false}
     * @apiParam {json} [markets]    Магазины. {"existence": false, "working_time": ""}
     * @apiParam {json} [pharmacies]    Аптеки. {"existence": false, "working_time": ""}
     *
     *
     *
     * @apiParam {integer} [number_of_place]    Количество мест
     * @apiParam {integer} [number_of_rooms]    Количество номеров
     * @apiParam {json} [for_disabilities]    Наличае номеров для инвалидою. {"existence": false, "number": 0}
     * @apiParam {string} [room_description]    Категории номеров и их пложадь
     * @apiParam {string} [room_equipment]    Оснащение номера. ["ТВ", "Радио", "Халат", "Кресло"]
     * @apiParam {string} [lighting]    Освещение номера
     * @apiParam {string} [sleeping_place_description]    Оборудование спальных мест
     * @apiParam {string} [flooring]    Покрытие на полу и в номерах
     * @apiParam {string} [restroom_equipment]    Оснащение санузла
     * @apiParam {json} [restroom_square]    Площадь санузла. {"to": "", "from": ""}
     * @apiParam {string} [toilet_paper_type]    Тип туалетной бумаги
     * @apiParam {json} [welcome_kit]    Наличае наборов гостепреимства. {"rules": {"existence": false, "description": ""}, "existence": false, "description": ""}
     *
     *
     *
     * @apiParam {string} [chief-cooker_name]    Шеф повар (ФИО)
     * @apiParam {string} [kitchen_type]    Тип кухни
     * @apiParam {string} [food_point_description]    Количество ивиды точек питания
     * @apiParam {json} [mini_bar]    Состав мини бара. ["Коньяк", "Водка", "Мартини", "Вино"]
     *
     *
     * @apiParam {boolean} [on_main_page] Показывать на главной странице
     * @apiParam {boolean} [in_action] Акция активна
     *
     * @apiParam {string} [reviewpro_code] Код ревью про
     * @apiParam {string}  [street_view_link] ссылка на вид с улицы
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "user_id": null,
        "title_ru": "название объекта",
        "title_en": "test",
        "is_visibly": 1,
        "country_id": 1,
        "region_id": 1,
        "city_id": 1,
        "lat": "42.34543",
        "lon": "43.434535",
        "address": "адрес объекта",
        "zip": "213432",
        "description_ru": "описание объекта",
        "description_en": null,
        "documents_ru": "документы для объекта",
        "documents_en": null,
        "visa_information_ru": "информация по визе объекта",
        "visa_information_en": null,
        "contraindications_ru": "противопоказания на объекта",
        "contraindications_en": null,
        "showcase_rooms_count": "2",
        "showcase_rooms_count": "2",
        "created_at": "2018-11-16 11:48:58",
        "modified_at": "2018-11-16 11:48:58",
        "is_deleted": 0,
        "deleted_at": null,
        "tags_ru": [],
        "tags_en": [],
        "street_view_link": "ссылка на объект с улицы",
        "has_default_diseases_only": true,
        "images": [
            {
                "id": 21,
                "object_id": 2,
                "moderationStatus": 1,
                "image": "2_NTutm8Avjn.JPG",
                "sorting_rule": null,
                "description": "qwerty",
                "is_main": 0,
                "created_at": "2018-11-19 10:13:35",
                "updated_at": "2018-11-19 11:19:06",
                "image_link": "/backend/object_images/2_NTutm8Avjn.JPG"
            }

        ],
        "country": {
            "id": 1,
            "name_ru": "Россия",
            "name_en": "Russia",
            "created_at": null,
            "updated_at": null
        },
        "region": {
            "id": 1,
            "name_ru": "Краснодарский край",
            "name_en": "Krasnodar region",
            "country_id": 1,
            "created_at": null,
            "updated_at": null
        },
        "city": {
            "id": 1,
            "name_ru": "Сочи",
            "name_en": "Sochi",
            "region_id": 1,
            "country_id": 1,
            "created_at": null,
            "updated_at": null
        }
        "award_icons": [
            {
                "id": 1,
                "title_ru": "NmaeRu",
                "title_en": "NmaeEn",
                "description_ru": null,
                "description_en": null,
                "image": null,
                "created_at": "2019-01-23 10:59:39",
                "updated_at": "2019-01-23 10:59:39",
                "active": false
            }
        ],
        "therapies": [
            {
                "id": 78,
                "name_ru": "Миоэлектростимуляция",
                "name_en": ""
            },
            {
                "id": 176,
                "name_ru": "Прием (осмотр, консультация) врача-кардиолога",
                "name_en": "Reception (examination, consultation) Cardiologist"
            }
        ],
       "moods": [
            {
                "id": 1,
                "name_ru": "Ок",
                "name_en": "ok",
                "alias": "ok",
                "image": "/storage/moods/DoX12ULbleuF8xR3X9OEDytstWwk4BzKUoNKHUwN.jpeg",
                "crop_image": "/storage/moods_crop/DoX12ULbleuF8xR3X9OEDytstWwk4BzKUoNKHUwN.jpeg"
            },
            {
                "id": 2,
                "name_ru": "Мать и дитя",
                "name_en": "mother and child",
                "alias": "mother-and-child",
                "image": "/storage/moods/jY27yLMSdCDpXhBQ6wbSm1yI4KOJSX5oxQ5EZgCD.jpeg",
                "crop_image": "/storage/moods_crop/jY27yLMSdCDpXhBQ6wbSm1yI4KOJSX5oxQ5EZgCD.jpeg"
            }
        ],
        "medical_profiles": [
            {
                "id": 13,
                "name_ru": "Урология и Гинекология",
                "name_en": "Urology and Gynecology",
                "has_default_diseases_only": false,
                "diseases_count": 2,
                "not_default_diseases_count": 2
            },
            {
                "id": 15,
                "name_ru": "Болезни опорно-двигательного аппарата",
                "name_en": "Diseases of the musculoskeletal system",
                "has_default_diseases_only": false,
                "diseases_count": 3,
                "not_default_diseases_count": 3
            },
        ]
    }
     *
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function update(Request $request, $id)
    {
        $valid = Validator($request->all(), [
            'country_id' => 'integer|nullable',
            'region_id' => 'integer|nullable',
            'city_id' => 'integer|nullable',
            'is_visibly' => 'boolean',
            'working_by_season' => 'boolean',
            'title_ru' => 'max:255|nullable',
            'title_en' => 'max:255|nullable',
            'lat' => 'numeric|nullable',
            'lon' => 'numeric|nullable',
            'head_doctor_name' => 'max:255|nullable',
            'chief-cooker_name' => 'max:255|nullable',
            'toilet_paper_type' => 'max:255|nullable',
            'zip' => 'max:10|nullable',
            'user_id' => 'integer|nullable',
            'number_of_place' => 'integer|nullable',
            'number_of_rooms' => 'integer|nullable',
            'stars' => 'integer|nullable',
            'personal_count' => 'integer|nullable',
            'on_main_page' => 'boolean',
            'in_action' => 'boolean',
            'expensive' => 'boolean',
            'heating_rating' => 'numeric',
            'full_rating' => 'numeric',
            'reviewpro_code' => 'string|max:255|nullable',
            'award_icons' => [ new IsArray ],
            'tags_ru' => [ new IsArray ],
            'tags_en' => [ new IsArray ],
            'street_view_link' => 'max:255|nullable',
        ]);

        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        $object = $request->only(
            'user_id',
            'title_ru',
            'title_en',
            'is_visibly',
            'country_id',
            'region_id',
            'city_id',
            'lat',
            'lon',
            'address',
            'zip',
            'description_ru',
            'description_en',
            'documents_ru',
            'documents_en',
            'visa_information_ru',
            'visa_information_en',
            'contraindications_ru',
            'contraindications_en',
            'capabilities',
            'services',
            'medical_profiles',
            'therapies',
            'moods',
            'stars',
            'payment_description_ru',
            'payment_description_en',
            'year_of_found',
            'year_of_reconstruction',
            'bankomats',
            'discount_cards',
            'partnership_programs',
            'early_check_in',
            'late_check_out',
            'other_rules',
            'commission',
            'card_types',
            'taxes',
            'other_taxes',
            'service_contacts',
            'route_description',
            'contacts',
            'on_main_page',
            'in_action',
            'award_icons',
            'expensive',
            'heating_rating',
            'full_rating',
            'reviewpro_code',
            'tags_ru',
            'tags_en',
            'street_view_link'
        );

        if (count($object) > 0) $this->objectService->updateObject($object, $id);

        $medicalInformation = $request->only(
            'head_doctor_name',
            'head_doctor_regalia',
            'climatic_factors',
            'water',
            'pump_room',
            'healing_mud',
            'other_health_information',
            'voucher_without_accommodation',
            'personal_count',
            'certified_personal',
            'medical_equipment',
            'unique_therapy',
            'paid_therapy',
            'paid_spa',
            'exist_reanimation',
            'operation_in_object',
            'drinking_water_plumbing'
        );
        if (count($medicalInformation) > 0) $this->objectService->updateMedicalInformation($id, $medicalInformation);

        $infrastructure = $request->only(
            'working_by_season',
            'season_period',
            'months_peak',
            'months_lows',
            'effective_months',
            'contingent',
            'territory',
            'elevators',
            'reservoir',
            'pools',
            'lifesavers',
            'training_apparatuses',
            'changing_rooms',
            'instructors',
            'parking',
            'markets',
            'pharmacies'
        );
        if (count($infrastructure) > 0) $this->objectService->updateInfrastructure($id, $infrastructure);

        $rooms = $request->only(
            'number_of_place',
            'number_of_rooms',
            'room_equipment',
            'lighting',
            'sleeping_place_description',
            'flooring',
            'restroom_equipment',
            'restroom_square',
            'toilet_paper_type',
            'welcome_kit',
            'for_disabilities'
        );
        if (count($rooms) > 0) $this->objectService->updateRoom($id, $rooms);

        $sportFood = $request->only(
            'chief-cooker_name',
            'kitchen_type',
            'food_point_description',
            'mini_bar'
        );

        if (count($sportFood) > 0) $this->objectService->updateFoodSport($id, $sportFood);

        return $this->objectService->getObject($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @api {delete} /api/admin/object/{objectId} Удаление объекта
     * @apiVersion 0.1.0
     * @apiName DeleteObject
     * @apiGroup AdminObject
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "object": [
            "Объект успешно удален"
        ]
    }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->objectService->softDelete($id);

        return response()->json(['object' => [
            'Объект успешно удален'
        ] ], 200);
    }

    /**
     * @api {get} /api/admin/object/worksheet/{objectId} Получение данных для анкеты
     * @apiVersion 0.1.0
     * @apiName GetWorksheet
     * @apiGroup AdminObject
     *
     * @apiParam {string} [type] Тип файла (doc - Microsoft Word ) <b>По умочанию</b>
     * @apiParam {string} [type.] Тип файла (rtf - RTF - формат )
     * @apiParam {string} [type..] Тип файла (pdf - PDF - формат )
     * @apiParam {string} [type...] Тип файла (html - HTML - формат )
     * @apiParam {boolean} [download] Флаг скачивания (по умолчанию true)
     * @apiParam {string} token токен доступа
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
     * HTTP/1.1 200 OK
    FILE CONtENT

     *
     * @param Request $request
     * @param int $objectId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws ApiProblemException
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    public function getWorksheet( Request $request, int $objectId)
    {
        $valid = Validator($request->all(), [
            'token' => 'required|string'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $token = $request->get('token');
        $tokenId = (new \Lcobucci\JWT\Parser())->parse($token)->getHeader('jti');
        $now = (new \DateTime('now'))->format('Y-m-d h:i:s');
        $token = Token::where('id', $tokenId)->where('revoked', false)->where('expires_at', '>', $now)->first();
        if (is_null($token))
            throw new ApiProblemException('Доступ запрещен', 403);
        $user = $token->user;

        //TODO: Нужна проверка, админ или контент менеджер

        $type = $request->get('type') ?? 'doc';
        $download = $request->get('download') ?? true;
        $worksheet = $this->objectService->getWorksheet($objectId, (string)$type, (bool)$download);

        return $worksheet;
    }
}
