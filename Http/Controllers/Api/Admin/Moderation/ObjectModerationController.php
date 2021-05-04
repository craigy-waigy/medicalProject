<?php

namespace App\Http\Controllers\Api\Admin\Moderation;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\ObjectModerationService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ObjectModerationController extends Controller
{
    protected $objectModerationService;

    public function __construct()
    {
        $this->objectModerationService = new ObjectModerationService();
    }

    /**
     * @api {put} /api/admin/moderation/object/{objectId} модерирование объекта
     * @apiVersion 0.1.0
     * @apiName ModerateObject
     * @apiGroup ModerationObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiDescription В примере показаны все json-объекты, но так-же на модерацию можно отправлять по одному json-объекту.
     * которые обрабатывает апи.
     *
     * @apiParamExample {json} Request-Example:
        {
            "description":
                { "approve": true, "message": null},
     *
            "stars":
                { "approve": true, "message": null},
     *
            "payment_description":
                { "approve": false, "message": "moderator message"},
     *
            "documents":
                { "approve": false, "message": "moderator message"},
     *
            "contraindications":
                { "approve": true, "message": null},
     *
            "services":
                { "approve": true, "message": null},
     *
            "medical_profiles":
                { "approve": false, "message": "moderator message"},
     *
            "therapy":
                { "approve": false, "message": "moderator message"},
        }
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "object": [
            "Модерация выполнена"
        ]
    }
     *
     * @param Request $request
     * @param int $objectId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function moderate(Request $request, int $objectId)
    {
        $valid = Validator($request->all(), [
            'description' => [ new IsArray ],
            'stars' => [ new IsArray ],
            'payment_description' => [ new IsArray ],
            'documents' => [ new IsArray ],
            'contraindications' => [ new IsArray ],
            'services' => [ new IsArray ],
            'medical_profiles' => [ new IsArray ],
            'therapies' => [ new IsArray ],
            'contacts' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $rejectEmptyMessage = 'Необходимо предоставить сообщение о причине отклонении';

        $data = $request->only(
            'description',
            'stars',
            'payment_description',
            'documents',
            'contraindications',
            'services',
            'medical_profiles',
            'therapies',
            'contacts'
        );

        foreach ($data as $key=>$value){

            if (    !in_array('approve', array_keys($value)) )

                throw new ApiProblemException('Не поддерживаемый формат данных для модерации', 422);


                if ($key == 'description'){

                    if ( (bool)$value['approve'] ){
                        $this->objectModerationService->approveDescription($objectId);
                    } else {
                        if (empty($value['message'])) throw new ApiProblemException($rejectEmptyMessage, 400);
                        $this->objectModerationService->rejectDescription($objectId, $value['message']);
                    }
                } elseif ($key == 'stars'){

                    if ( (bool)$value['approve'] ){
                        $this->objectModerationService->approveStars($objectId);
                    } else {
                        if (empty($value['message'])) throw new ApiProblemException($rejectEmptyMessage, 400);
                        $this->objectModerationService->rejectStars($objectId, $value['message']);
                    }
                } elseif ($key == 'payment_description'){

                    if ( (bool)$value['approve'] ){
                        $this->objectModerationService->approvePaymentDescription($objectId);
                    } else {
                        if (empty($value['message'])) throw new ApiProblemException($rejectEmptyMessage, 400);
                        $this->objectModerationService->rejectPaymentDescription($objectId, $value['message']);
                    }
                } elseif ($key == 'documents'){

                    if ( (bool)$value['approve'] ){
                        $this->objectModerationService->approveDocuments($objectId);
                    } else {
                        if (empty($value['message'])) throw new ApiProblemException($rejectEmptyMessage, 400);
                        $this->objectModerationService->rejectDocuments($objectId, $value['message']);
                    }
                } elseif ($key == 'contraindications'){

                    if ( (bool)$value['approve'] ){
                        $this->objectModerationService->approveContraindications($objectId);
                    } else {
                        if (empty($value['message'])) throw new ApiProblemException($rejectEmptyMessage, 400);
                        $this->objectModerationService->rejectContraindications($objectId, $value['message']);
                    }
                } elseif ($key == 'services'){

                    if ( (bool)$value['approve'] ){
                        $this->objectModerationService->approveServices($objectId);
                    } else {
                        if (empty($value['message'])) throw new ApiProblemException($rejectEmptyMessage, 400);
                        $this->objectModerationService->rejectServices($objectId, $value['message']);
                    }
                } elseif ($key == 'medical_profiles'){

                    if ( (bool)$value['approve'] ){
                        $this->objectModerationService->approveMedicalProfile($objectId);
                    } else {
                        if (empty($value['message'])) throw new ApiProblemException($rejectEmptyMessage, 400);
                        $this->objectModerationService->rejectMedicalProfile($objectId, $value['message']);
                    }
                } elseif ($key == 'therapies'){

                    if ( (bool)$value['approve'] ){
                        $this->objectModerationService->approveTherapy($objectId);
                    } else {
                        if (empty($value['message'])) throw new ApiProblemException($rejectEmptyMessage, 400);
                        $this->objectModerationService->rejectTherapy($objectId, $value['message']);
                    }
                } elseif ($key == 'contacts'){

                    if ( (bool)$value['approve'] ){
                        $this->objectModerationService->approveContacts($objectId);
                    } else {
                        if (empty($value['message'])) throw new ApiProblemException($rejectEmptyMessage, 400);
                        $this->objectModerationService->rejectContacts($objectId, $value['message']);
                    }
                } else {

                    throw new ApiProblemException("Модерация для {$key} не определена на сервере", 510);
                }
        }

        return response()->json(['moderationObject' =>
            ['Модерация выполнена']
        ], 200);
    }

    /**
     * @api {get} /api/admin/moderation/object Получение и поиск объектов для модерации
     * @apiVersion 0.1.0
     * @apiName SearchObject
     * @apiGroup ModerationObject
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
        "total": 1,
        "items": [
            {
                "id": 33,
                "country_id": 88,
                "region_id": 6977,
                "city_id": 7,
                "title_ru": "Санаторий Буран",
                "alias": "buran",
                "is_visibly": true,
                "created_at": null,
                "modified_at": "2019-05-21 04:37:17",
                "showcase_rooms_count": "10",
                "country": {
                    "id": 88,
                    "name_ru": "Российская Федерация"
                },
                "region": {
                    "id": 6977,
                    "name_ru": "Алтайский Край"
                },
                "city": {
                    "id": 7,
                    "name_ru": "Барнаул"
                }
            }
        ]
    }
     *
     * @param Request|null $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getForApproval(?Request $request)
    {
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $sorting = json_decode($request->get('sorting'), true) ?? null;
        $searchKey = $request->get('searchKey') ?? null;

        $objects = $this->objectModerationService->getForApproval($page, $rowsPerPage, $sorting, $searchKey);
        return response()->json($objects, 200);
    }

    /**
     *
     * @api {get} /api/admin/moderation/object/{objectId} получение объекта для модерации
     * @apiVersion 0.1.0
     * @apiName GetObject
     * @apiGroup ModerationObject
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
        HTTP/1.1 200 OK
    {
        "id": 33,
        "user_id": 5,
        "title_ru": "Санаторий Буран",
        "title_en": "Sanatorium \"Buran\"",
        "is_visibly": true,
        "country_id": 88,
        "region_id": 6977,
        "city_id": 7,
        "address": "Московская обл., Сергиево-Посадский р-н, дер. Трехселище, ул. Григорово 10-14, инд. 141330",
        "zip": "141330",
        "description_ru": "<p><strong>Санаторий \"Буран\"</strong> находится в 120 км. от Москвы по Угличскому шоссе, в 33 км. от г. Сергиев-Посад в живописном уголке Подмосковья.</p>\r\n<p>Красота мест и яркие страницы истории Древней Руси, ансамбль архитектурно-исторических памятников и музеев привлекают множество туристов как из России, так и из-за рубежа.</p>\r\n<p>Отдых в<strong> санатории \"Буран\"</strong> отвечает всем требованиям европейского уровня комфорта.</p>\r\n<p>На охраняемой лесопарковой территории расположились четыре кирпичных 4-этажных корпуса и медицинский корпус. Рядом расположен горнолыжный склон, в 5 км - лесное озеро.</p>\r\n<p><strong>Санаторий \"Буран\"</strong> - часть системы здравниц \"РЖД-Здоровье\", гарантирующей высокое качество лечения и отличный сервис.</p>\r\n<p><br data-cke-eol=\"1\" /><br /></p>\r\n<p>&nbsp;</p>",
        "description_en": "<p>Sanatorium \"Buran\" - part of the system of health resorts \"RZD-Zdorovie\", which guarantees high quality of treatment and excellent service.<br />Four four-storey brick buildings and a medical building are located in the protected forest-park territory. Nearby there is a ski slope, 5 km - a forest lake.<br /><br />Rest in the sanatorium \"Buran\" meets all the requirements of the European level of comfort.<br /><br />Sanatorium \"Buran\" is located in a mixed forest, next to the ski slope.<br /><br />The beauty of places and bright pages of the history of Ancient Rus, the ensemble of architectural and historical monuments and museums attract many tourists both from Russia and from abroad.<br /><br />Sanatorium \"Buran\" is located in 120 km. From Moscow along the Uglich highway, 33 km. From the city of Sergiev-Posad in a picturesque corner of the Moscow region.</p>",
        "documents_ru": "<ul>\r\n<li>взрослым: удостоверяющий личность (паспорт); санаторно-курортную карту поформе 072/у-04, выданной не ранее чем за 2 месяца до даты начала лечения; полис обязательного медицинского страхования; для лиц, направленных на восстановительное лечение после пребывания в стационаре - выписку из истории болезни;<br /><br /></li>\r\n<li>детям: свидетельство о рождении либо паспорт для детей старше 14 лет; санаторно-курортную карту по форме № 076/у-04; справку о прививках; справку о санитарно-эпидемиологическом окружении; справку от педиатра об отсутствии противопоказаний.</li>\r\n</ul>",
        "documents_en": "<ul>\r\n<li><span lang=\"en\">adults: identity card (passport); a sanatorium-resort card 072 / y-04, issued not earlier than 2 months before the start of treatment; the policy of compulsory medical insurance; for individuals, intervention for restorative treatment after hospital stay - an extract from the medical history; </span></li>\r\n<li><span lang=\"en\">children: birth certificate or passport for children over 14; sanatorium-resort card on the form № 076 / у-04; certificate of vaccination; a certificate of the sanitary-epidemiological environment; certificate from a pediatrician about the absence of contraindications.</span></li>\r\n</ul>",
        "visa_information_ru": "<p>Пожалуйста, при планировании поездки учитывайте, что гражданам других государств для въезда на территорию России может потребоваться виза.</p>\r\n<p><strong>Безвизовый въезд предусмотрен только для резидентов:</strong></p>\r\n<p>Аргентины, Армении, Азербайджана, Белоруссии, Боливии, Боснии и Герцеговины, Бразилии, Вануату, Венесуэлы, Чили, Колумбии, Кубы, Эквадора, Сальвадора, Фиджи, Гренады, Гватемалы, Гайаны, Гондураса, Гонконга, Израиля, Казахстана, Киргизии, Лаоса, Maкao, Маврикии, Молдовы, Монголии, Черногории, Науру, Никарагуа, Панамы, Парагвая, Перу, Сент-Китса и Невиса, Самоа, Сербии, Сейшельских о-в, Таджикистана, Таиланда, Украины, Уругвая, Узбекистана, Южной Африки, Южной Кореи.</p>",
        "visa_information_en": null,
        "contraindications_ru": "<ul>\r\n<li>Все заболевания в острой стадии, хронические заболевания в стадии обострения и осложненные острогнойными процессами.</li>\r\n<li>Острые инфекционные заболевания до окончания срока изоляции.</li>\r\n<li>Все венерические заболевания в острой и заразной форме.</li>\r\n<li>Все болезни крови в острой стадии и стадии обострения.</li>\r\n<li>Психические заболевания; все формы наркоманий и хронический алкоголизм; эпилепсия.</li>\r\n<li>Кахексия любого происхождения.</li>\r\n<li>Злокачественные новообразования (после радикального лечения при общем удовлетворительном состоянии, отсутствии метастазирования, нормальных показателях периферической крови могут направляться в местные санатории для общеукрепляющего лечения).</li>\r\n<li>Все заболевания и состояния, требующие стационарного лечения, в том числе и хирургического вмешательства, все заболевания, при которых больные не способны к самостоятельному передвижению и самообслуживанию, нуждаются в постоянном специальном уходе (кроме лиц, подлежащих лечению в специализированных санаториях для спинальных больных).</li>\r\n<li>Эхинококкоз любой локализации.</li>\r\n</ul>",
        "contraindications_en": "<p>All diseases in the acute stage, chronic diseases in the acute stage and complicated by acute processes.</p>",
        "capabilities": null,
        "created_at": null,
        "modified_at": "2019-05-21 04:37:17",
        "is_deleted": false,
        "deleted_at": null,
        "stars": 3,
        "payment_description_ru": "<ul>\r\n<li>Предоплата 30%</li>\r\n<li>принимаем оплату наличными и банковские карты: VISA, MasterCard, Maestro, American Express, JCB, DCI</li>\r\n</ul>",
        "payment_description_en": "<ul>\r\n<li>Prepayment 30%</li>\r\n<li>We accept payment in cash and bank cards: VISA, MasterCard, Maestro, American Express, JCB, DCI</li>\r\n</ul>",
        "other_rules": null,
        "commission": {
            "existence": false,
            "description": ""
        },
        "card_types": null,
        "service_contacts": null,
        "route_description": null,
        "contacts": {
            "skype": null,
            "telegram": null,
            "whatsapp": null
        },
        "year_of_found": null,
        "year_of_reconstruction": null,
        "lat": "56.5590333",
        "lon": "38.2154778",
        "on_main_page": false,
        "in_action": false,
        "viewing_count": 1,
        "priority_of_showing": 0,
        "alias": "buran",
            "bankomats": {
            "existence": true,
            "description": "la-la-la"
        },
        "discount_cards": {
            "existence": true,
            "description": "la-la-la"
        },
        "partnership_programs": {
            "existence": true,
            "description": "la-la-la"
        },
        "early_check_in": {
            "existence": true,
            "description": "la-la-la"
        },
        "late_check_out": {
            "existence": true,
            "description": "la-la-la"
        },
        "other_taxes": {
            "existence": false,
            "description": ""
        },
        "taxes": null,
        "moderation_object": {
            "description_ru": {
                "status_id": 2,
                "value": null,
                "message": null,
                "time": "2019-05-20 07:24:55"
            },
            "description_en": {
                "status_id": 2,
                "value": "description_en",
                "message": null,
                "time": "2019-05-20 07:24:55"
            },
            "stars": {
                "status_id": 1,
                "value": null,
                "message": null,
                "time": null
            },
            "payment_description_ru": {
                "status_id": 1,
                "value": null,
                "message": null,
                "time": null
            },
            "payment_description_en": {
                "status_id": 1,
                "value": null,
                "message": null,
                "time": null
            },
            "documents_ru": {
                "status_id": 1,
                "value": null,
                "message": null,
                "time": null
            },
            "documents_en": {
                "status_id": 1,
                "value": null,
                "message": null,
                "time": null
            },
            "contraindications_ru": {
                "status_id": 1,
                "value": null,
                "message": null,
                "time": null
            },
            "contraindications_en": {
                "status_id": 1,
                "value": null,
                "message": null,
                "time": null
            },
            "services": {
                "status_id": 2,
                "value": [
                    {
                        "id": 10,
                        "name_ru": "Анимация",
                        "name_en": null
                    },
                    {
                        "id": 8,
                        "name_ru": "Лобби-бар",
                        "name_en": null
                    }
                ],
                "message": null,
                "time": "2019-05-20 07:25:35"
            },
            "medical_profiles": {
                "status_id": 1,
                "value": [
                    {
                        "id": 10,
                        "name_ru": "Болезни желудочно-кишечного тракта",
                        "name_en": "Diseases of the gastrointestinal tract"
                    }
                ],
                "message": null,
                "time": null
            },
            "therapies": {
                "status_id": 1,
                "value": [
                    {
                        "id": 78,
                        "name_ru": "Миоэлектростимуляция",
                        "name_en": ""
                    }
                ],
                "message": null,
                "time": null
            },
            "contacts": {
                "status_id": 2,
                "value": {
                    "skype": null,
                    "telegram": null,
                    "whatsapp": null
                },
                "message": null,
                "time": "2019-05-21 04:37:17"
            }
        },
        "services": [
            {
                "id": 1,
                "name_ru": "Детская комната",
                "name_en": null
            },
            {
                "id": 3,
                "name_ru": "Тренажерный зал",
                "name_en": null
            }
        ],
        "medical_profiles": [
            {
                "id": 6,
                "name_ru": "Болезни эндокринной системы и обмена веществ",
                "name_en": "Diseases of the endocrine system and metabolism"
            },
            {
                "id": 8,
                "name_ru": "Болезни системы кровообращения",
                "name_en": "Diseases of the circulatory system"
            }
        ],
        "therapies": [
            {
                "id": 24,
                "name_ru": "Ванны контрастные",
                "name_en": "Contrast bath therapy"
            },
            {
                "id": 102,
                "name_ru": "Прием (осмотр, консультация) врача – невролога первичный",
                "name_en": "Reception (examination, consultation) doctor - neurologist primary"
            },
            {
                "id": 307,
                "name_ru": "Прием (осмотр, консультация) врача-гинеколога первичный",
                "name_en": "Reception (examination, consultation) gynecologist primary"
            }
        ]
    }
     * @param int $objectId
     * @return mixed
     * @throws ApiProblemException
     */
    public function getObjectForApproval(int $objectId)
    {
        return $this->objectModerationService->getObjectForApproval($objectId);
    }
}
