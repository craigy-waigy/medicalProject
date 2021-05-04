<?php

namespace App\Http\Controllers\Api\Account\Object;

use App\Exceptions\ApiProblemException;
use App\Models\MedicalProfile;
use App\Models\ModerationObjectMedicalProfile;
use App\Models\ModerationObjectsService;
use App\Models\ModerationObject;
use App\Models\ModerationObjectTherapy;
use App\Models\ModerationStatus;
use App\Models\Service;
use App\Models\Therapy;
use App\Services\ObjectService;
use Dotenv\Validator;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ObjectPlace;
use Laravel\Passport\Client;
use Laravel\Passport\Token;
use phpseclib\Crypt\Hash;

class ObjectController extends Controller
{
    /**
     * @var ObjectImageService
     */
    protected $objectService;

    /**
     * ObjectController constructor.
     */
    public function __construct()
    {
        $this->objectService = new ObjectService();
    }

    /**
     * Update the specified resource in storage.
     *
     * @api {post} /api/account/object Редактирование объекта
     * @apiVersion 0.1.0
     * @apiName UpdateObject
     * @apiGroup AccountObject
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
     * @apiParam {array}   [capabilities] удобства ["spa", "pool", "beach", "for_disabilities", "for_children"]
     * @apiParam {array}   [services] массив ID услуг [2, 4, 56, 76, 5]
     * @apiParam {array}   [medical_profiles] массив ID мед. профилей  [1, 3, 5, 7]
     * @apiParam {array}   [therapies] массив ID методов лечений  [1, 3, 5, 7]
     * @apiParam {integer} [stars]   целочисленное количество звезд
     * @apiParam {date} [year_of_found]    Дата основания
     * @apiParam {date} [year_of_reconstruction]    Дата последней реконструкции
     * @apiParam {json} [bankomats]    Банкоматы {"existence": false, "description": ""}
     * @apiParam {json} [discount_cards]    Наличае дисконтных карт / описание {"existence": false, "description": ""}
     * @apiParam {json} [partnership_programs]    В каких партнерских программах учавствует {"existence": false, "description": ""}
     * @apiParam {json} [early_check_in]    Условия раннего заезда {"existence": false, "description": ""}
     * @apiParam {json} [late_check_out]    Условия позднего выезда {"existence": false, "description": ""}
     * @apiParam {string} [other_rules]    Дополнительные условия
     * @apiParam {json} [commission]    Дополнительная комиссия {"existence": false, "description": ""}
     * @apiParam {string} [card_types]    Какие типы карт принимаются
     * @apiParam {string} [taxes]    Какие налоги включены в стоимость
     * @apiParam {json} [other_taxes]    Дополнительные налоги. {"existence": false, "description": ""}
     * @apiParam {string} [service_contacts]    Контакты основных служб
     * @apiParam {string} [route_description]    Как добраться от ближайшего аэропорта/вокзала
     * @apiParam {json} [contacts]    Контакты. {"fax": "номер факса", "email": "example@main.com", "telephone": "номер телофона", "messengers": {"skype": "", "telegram": "", "whatsapp": ""}}
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
     * @apiParam {json} [reservoir]    Водоемы {"name": "", "type": [], "beach": {"type": [], "existence": false, "availability": ""}, "transfer": {"existence": false, "description": ""}, "existence": false, "remoteness": "", "description": ""}
     * @apiParam {json} [pools]    Бассейны. {"type": [], "water": {"temperature": "", "cleaning_period": ""}, "existence": false, "descriptions": "", "additional_equipment": ""}
     * @apiParam {bool} [lifesavers]    Наличие спасателей
     * @apiParam {bool} [training_apparatuses]    Тренажоры
     * @apiParam {json} [infrastructure_for_disabilities]    Инфраструктура для инвалидов ["Например лифты", "бассейны", "Что-то еще"]
     * @apiParam {bool} [changing_rooms]    Наличае раздевалок
     * @apiParam {bool} [instructors]    Наличае инструкторов
     * @apiParam {json} [parking]    Парковка. {"cost": "", "existence": false}
     * @apiParam {json} [markets]    Магазины. {"existence": false, "working_time": ""}
     * @apiParam {json} [pharmacies]    Аптеки. {"existence": false, "working_time": "", address: ""}
     * @apiParam {boolean} [has_security]    Наличае охраны
     * @apiParam {boolean} [has_entertainments]    Наличае развлечений
     * @apiParam {boolean} [has_restaurants_bars]    Наличае ресторанов/баров
     *
     *
     *
     * @apiParam {integer} [number_of_place]    Количество мест
     * @apiParam {integer} [number_of_rooms]    Количество номеров
     * @apiParam {json} [rooms_for_disabilities]    Наличае номеров для инвалидою. {"existence": false, "number": 0}
     * @apiParam {string} [room_description]    Категории номеров и их пложадь
     * @apiParam {string} [room_equipment]    Оснащение номера. ["ТВ", "Радио", "Халат", "Кресло"]
     * @apiParam {string} [lighting]    Освещение номера
     * @apiParam {json} [sleeping_place_equipment]    Оборудование спальных мест ["меню подушек", "лечебние подушки"]
     * @apiParam {string} [flooring]    Покрытие на полу и в номерах
     * @apiParam {json} [restroom_equipment]    Оснащение санузла ["ванна", "душевая кабинка"]
     * @apiParam {json} [restroom_square]    Площадь санузла. {"to": "", "from": ""}
     * @apiParam {string} [toilet_paper_type]    Тип туалетной бумаги
     * @apiParam {json} [welcome_kit]    Наличае наборов гостепреимства. {"rules": {"existence": false, "description": ""}, "existence": false, "description": ""}
     *
     *
     *
     * @apiParam {string} [chief-cooker_name]    Шеф повар (ФИО)
     * @apiParam {string} [kitchen_type]    Тип кухни
     * @apiParam {string} [food_point_description]    Количество ивиды точек питания
     * @apiParam {json} [mini_bar]    Минибар {"existence": false, "composition": ["Коньяк", "Водка", "Мартини", "Вино"]}
     * @apiParam {json} [foods]    Питание []
     * @apiParam {boolean} [has_calorie_dishes] указана каллорийность блюд
     * @apiParam {boolean} [has_early_breakfast] есть ранний завтрак
     * @apiParam {boolean} [has_dry_ration] есть сухой паек
     * @apiParam {json} [room_service] room-сервис {"existence": false, "description": ""}
     * @apiParam {json} [sport_services] Спортивно - оздоровительные услуги []
     * @apiParam {string} [heating_program] оздоровительные программы
     * @apiParam {json} [ethernet_availability] доступность проводного интернета { "existence": false, "description": "" }
     * @apiParam {json} [wifi_places] доступность беспроводного интернета { "existence": false, "description": "" }
     * @apiParam {string} [other_it_service] Перечень инных IT услуг
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    *
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
        "medical_information": {
        "id": 113,
            "object_id": 33,
            "head_doctor_name": null,
            "head_doctor_regalia": null,
            "climatic_factors": [],
            "water": {
            "water_type": [],
                "drinking_water_type": [],
                "chemical_composition": ""
            },
            "pump_room": {
            "type": "",
                "location": "",
                "existence": false
            },
            "healing_mud": [],
            "other_health_information": null,
            "voucher_without_accommodation": null,
            "personal_count": null,
            "certified_personal": [],
            "medical_equipment": null,
            "unique_therapy": null,
            "paid_therapy": null,
            "paid_spa": null,
            "exist_reanimation": false,
            "operation_in_object": {
            "existence": false,
                "description": ""
            },
            "drinking_water_plumbing": {
            "existence": false,
                "description": ""
            },
            "created_at": null,
            "updated_at": null,
            "effective_months": [],
            "contraindications_ru": "<ul>\r\n<li>Все заболевания в острой стадии, хронические заболевания в стадии обострения и осложненные острогнойными процессами.</li>\r\n<li>Острые инфекционные заболевания до окончания срока изоляции.</li>\r\n<li>Все венерические заболевания в острой и заразной форме.</li>\r\n<li>Все болезни крови в острой стадии и стадии обострения.</li>\r\n<li>Психические заболевания; все формы наркоманий и хронический алкоголизм; эпилепсия.</li>\r\n<li>Кахексия любого происхождения.</li>\r\n<li>Злокачественные новообразования (после радикального лечения при общем удовлетворительном состоянии, отсутствии метастазирования, нормальных показателях периферической крови могут направляться в местные санатории для общеукрепляющего лечения).</li>\r\n<li>Все заболевания и состояния, требующие стационарного лечения, в том числе и хирургического вмешательства, все заболевания, при которых больные не способны к самостоятельному передвижению и самообслуживанию, нуждаются в постоянном специальном уходе (кроме лиц, подлежащих лечению в специализированных санаториях для спинальных больных).</li>\r\n<li>Эхинококкоз любой локализации.</li>\r\n</ul>",
            "contraindications_en": "<p>All diseases in the acute stage, chronic diseases in the acute stage and complicated by acute processes.</p>"
        },
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
        "images": [
            {
                "id": 2295,
                "object_id": 33,
                "moderation_status": 1,
                "image": "/storage/object_gallery/qeoLAqb8rwIhsgQY.jpeg",
                "sorting_rule": 0,
                "description": null,
                "is_main": false,
                "created_at": null,
                "updated_at": null,
                "moderator_message": null
            },
            {
                "id": 2297,
                "object_id": 33,
                "moderation_status": 1,
                "image": "/storage/object_gallery/1XAGNYKsb0GlVsh7.jpeg",
                "sorting_rule": 1,
                "description": null,
                "is_main": false,
                "created_at": null,
                "updated_at": null,
                "moderator_message": null
            },
            {
                "id": 2419,
                "object_id": 33,
                "moderation_status": 1,
                "image": "/storage/object_gallery/nSLocyEG2qG1pHfy.jpeg",
                "sorting_rule": 2,
                "description": null,
                "is_main": false,
                "created_at": null,
                "updated_at": null,
                "moderator_message": null
            },
            {
                "id": 2417,
                "object_id": 33,
                "moderation_status": 1,
                "image": "/storage/object_gallery/VnnswQ02sshLYvDv.jpeg",
                "sorting_rule": 3,
                "description": null,
                "is_main": false,
                "created_at": null,
                "updated_at": null,
                "moderator_message": null
            },
            {
                "id": 2415,
                "object_id": 33,
                "moderation_status": 1,
                "image": "/storage/object_gallery/oEiknzX9RM1gjKoY.jpeg",
                "sorting_rule": 4,
                "description": null,
                "is_main": false,
                "created_at": null,
                "updated_at": null,
                "moderator_message": null
            },
            {
                "id": 2414,
                "object_id": 33,
                "moderation_status": 1,
                "image": "/storage/object_gallery/fOcpwmqpmwGjgqXI.jpeg",
                "sorting_rule": 5,
                "description": null,
                "is_main": false,
                "created_at": null,
                "updated_at": null,
                "moderator_message": null
            },
            {
                "id": 2416,
                "object_id": 33,
                "moderation_status": 1,
                "image": "/storage/object_gallery/JkWI4iepH85LY8cu.jpeg",
                "sorting_rule": 6,
                "description": null,
                "is_main": false,
                "created_at": null,
                "updated_at": null,
                "moderator_message": null
            },
            {
                "id": 2418,
                "object_id": 33,
                "moderation_status": 1,
                "image": "/storage/object_gallery/MksghtyZ0UBMq3ym.jpeg",
                "sorting_rule": 7,
                "description": null,
                "is_main": false,
                "created_at": null,
                "updated_at": null,
                "moderator_message": null
            },
            {
                "id": 2420,
                "object_id": 33,
                "moderation_status": 1,
                "image": "/storage/object_gallery/hf05yq8dC7qr5sM8.jpeg",
                "sorting_rule": 8,
                "description": null,
                "is_main": false,
                "created_at": null,
                "updated_at": null,
                "moderator_message": null
            },
            {
                "id": 2421,
                "object_id": 33,
                "moderation_status": 1,
                "image": "/storage/object_gallery/8Dq2c2RfdSjODm52.jpeg",
                "sorting_rule": 9,
                "description": null,
                "is_main": false,
                "created_at": null,
                "updated_at": null,
                "moderator_message": null
            },
            {
                "id": 2296,
                "object_id": 33,
                "moderation_status": 1,
                "image": "/storage/object_gallery/8brf2C3ATveiFZqC.jpeg",
                "sorting_rule": 10,
                "description": null,
                "is_main": false,
                "created_at": null,
                "updated_at": null,
                "moderator_message": null
            },
            {
                "id": 2926,
                "object_id": 33,
                "moderation_status": 2,
                "image": "/storage/object_gallery/njAX3lx8jFDh3aJHTMJHwynw195Y0HObct7YEPHu.jpeg",
                "sorting_rule": null,
                "description": "werqwerqwer",
                "is_main": false,
                "created_at": "2019-05-27 05:34:49",
                "updated_at": "2019-05-27 05:35:17",
                "moderator_message": null
            },
            {
                "id": 2924,
                "object_id": 33,
                "moderation_status": 2,
                "image": "/storage/object_gallery/6AHajYVzueATXPherEtWTHuzwdLotxXVeRlCF6Ty.jpeg",
                "sorting_rule": null,
                "description": null,
                "is_main": false,
                "created_at": "2019-05-24 14:51:28",
                "updated_at": "2019-05-24 14:51:28",
                "moderator_message": null
            },
            {
                "id": 2925,
                "object_id": 33,
                "moderation_status": 2,
                "image": "/storage/object_gallery/FwLKBoZTo2fRmG7vQPithGIBsXZM4mz51RzmIPez.jpeg",
                "sorting_rule": null,
                "description": "werqwerqwer",
                "is_main": false,
                "created_at": "2019-05-24 14:53:35",
                "updated_at": "2019-05-24 15:05:50",
                "moderator_message": null
            }
        ],
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
            },
            {
                "id": 6,
                "name_ru": "Парковка",
                "name_en": null
            },
            {
                "id": 13,
                "name_ru": "Теннисный корт",
                "name_en": null
            },
            {
                "id": 14,
                "name_ru": "Сауна",
                "name_en": null
            },
            {
                "id": 17,
                "name_ru": "Бар",
                "name_en": null
            },
            {
                "id": 21,
                "name_ru": "3-разовое",
                "name_en": null
            },
            {
                "id": 22,
                "name_ru": "Крытый бассейн",
                "name_en": null
            },
            {
                "id": 24,
                "name_ru": "Массаж",
                "name_en": null
            },
            {
                "id": 25,
                "name_ru": "Бильярд",
                "name_en": null
            },
            {
                "id": 26,
                "name_ru": "Библиотека",
                "name_en": null
            },
            {
                "id": 27,
                "name_ru": "Детская площадка",
                "name_en": null
            },
            {
                "id": 29,
                "name_ru": "Спортивная площадка",
                "name_en": null
            }
        ],
        "country": {
        "id": 88,
            "name_ru": "Российская Федерация",
            "name_en": "Russian Federation"
        },
        "region": {
        "id": 6977,
            "name_ru": "Алтайский Край",
            "name_en": "Altayskiy Kray"
        },
        "city": {
        "id": 7,
            "name_ru": "Барнаул",
            "name_en": "Barnaul"
        },
        "infrastructure": {
        "id": 111,
            "object_id": 33,
            "working_by_season": null,
            "season_period": [],
            "months_peak": [],
            "months_lows": [],
            "contingent": [],
            "territory": {
            "area": "",
                "corps": {
                "floors": "",
                    "number": "",
                    "passages": "",
                    "specific": ""
                },
                "relief": [],
                "description": "",
                "infrastructures": "",
                "steps_by_territory": ""
            },
            "elevators": null,
            "pools": {
            "type": [],
                "water": {
                "temperature": "",
                    "cleaning_period": ""
                },
                "existence": false,
                "descriptions": "",
                "additional_equipment": ""
            },
            "lifesavers": false,
            "training_apparatuses": false,
            "changing_rooms": false,
            "instructors": false,
            "parking": {
            "cost": "",
                "existence": false
            },
            "markets": {
            "existence": false,
                "working_time": ""
            },
            "created_at": null,
            "updated_at": null,
            "has_electro_cars": false,
            "infrastructure_for_disabilities": null,
            "reservoir": {
            "name": "",
                "type": [],
                "beach": {
                "type": [],
                    "existence": false,
                    "availability": ""
                },
                "transfer": {
                "existence": false,
                    "description": ""
                },
                "existence": false,
                "remoteness": "",
                "description": ""
            },
            "pharmacies": {
            "address": "",
                "existence": false,
                "working_time": ""
            },
            "has_security": null,
            "has_entertainments": null,
            "has_restaurants_bars": null
        },
        "food_and_sport": {
        "id": 110,
            "object_id": 33,
            "chief-cooker_name": null,
            "kitchen_type": null,
            "food_point_description": null,
            "created_at": null,
            "updated_at": "2019-05-23 14:34:37",
            "mini_bar": {
            "existence": false,
                "composition": []
            },
            "foods": [],
            "has_calorie_dishes": false,
            "has_early_breakfast": false,
            "has_dry_ration": false,
            "room_service": {
            "existence": false,
                "description": ""
            },
            "sport_services": [],
            "heating_program": "",
            "other_services": [],
            "other_it_service": "",
            "ethernet_availability": {
            "existence": false,
                "description": ""
            },
            "wifi_places": {
            "existence": false,
                "description": ""
            }
           "general_services": []
        },
        "seo": {
        "object_id": 33,
            "for": "object-page",
            "h1_ru": null,
            "h1_en": null,
            "title_ru": null,
            "title_en": null,
            "url": "buran",
            "meta_description_ru": null,
            "meta_description_en": null,
            "meta_keywords_ru": null,
            "meta_keywords_en": null
        },
        "award_icons": [],
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
        "rooms": {
        "id": 111,
            "object_id": 33,
            "number_of_place": null,
            "number_of_rooms": null,
            "lighting": null,
            "flooring": null,
            "restroom_square": {
            "to": "",
                "from": ""
            },
            "toilet_paper_type": null,
            "welcome_kit": {
            "rules": {
                "existence": false,
                    "description": ""
                },
                "existence": false,
                "description": ""
            },
            "created_at": null,
            "updated_at": "2019-05-25 05:41:24",
            "room_description": null,
            "rooms_for_disabilities": {
            "number": 0,
                "existence": false
            },
            "sleeping_place_equipment": [],
            "restroom_equipment": [
            1,
            3,
            4,
            2
        ],
            "room_equipment": [
            1,
            3,
            4,
            2
        ]
        }
    }

     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function update(Request $request)
    {
        $valid = Validator($request->all(), [
            'country_id' => 'integer|nullable',
            'region_id' => 'integer|nullable',
            'city_id' => 'integer|nullable',
            'is_visibly' => 'boolean',
            'title_ru' => 'string|max:255',
            'title_en' => 'string|max:255',
            'lat' => 'numeric|nullable',
            'lon' => 'numeric|nullable',
            'zip' => 'string|max:10',
            'head_doctor_name' => 'max:255|nullable',
            'chief-cooker_name' => 'max:255|nullable',
            'toilet_paper_type' => 'max:255|nullable',
            'number_of_place' => 'integer|nullable',
            'number_of_rooms' => 'integer|nullable',
            'stars' => 'integer|nullable',
            'personal_count' => 'integer|nullable',
            'has_security' => 'boolean|nullable',
            'has_entertainments' => 'boolean|nullable',
            'has_restaurants_bars' => 'boolean|nullable',
            'has_calorie_dishes' => 'boolean|nullable',
            'has_early_breakfast' => 'boolean|nullable',
            'has_dry_ration' => 'boolean|nullable',
            'year_of_found' => 'digits:4|integer|min:1900|max:'.(date('Y')) . '|nullable',
            'year_of_reconstruction' => 'digits:4|integer|min:1900|max:'.(date('Y')) . '|nullable',
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $userId = Auth::user()->id;
        $object = ObjectPlace::where('user_id', $userId)->first();

        if (is_null($object))
            throw new ApiProblemException('У пользователя нет прикрепленного объекта', 422);

        $noModerateData = $request->only(
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
            'contacts'
        );
        if (count($noModerateData) > 0) $this->objectService->updateObject($noModerateData, $object->id);

        $toModerateData = $request->only(
            'description_ru',
            'description_en',
            'documents_ru',
            'documents_en',
            'visa_information_ru',
            'visa_information_en',
            'contraindications_ru',
            'contraindications_en',
            'stars',
            'services',
            'payment_description_ru',
            'payment_description_en',
            'medical_profiles',
            'therapies',
            'contacts'
        );
        if (count($toModerateData) > 0) $this->objectService->setToModerateData($toModerateData, $object->id);

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
            'effective_months',
            'other_for_disabilities',
            'drinking_water_plumbing'
        );
        if (count($medicalInformation) > 0) $this->objectService->updateMedicalInformation($object->id, $medicalInformation);

        $infrastructure = $request->only(
            'working_by_season',
            'season_period',
            'months_peak',
            'months_lows',
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
            'pharmacies',
            'has_security',
            'has_entertainments',
            'has_restaurants_bars',
            'other_for_disabilities',
            'infrastructure_for_disabilities'
        );
        if (count($infrastructure) > 0) $this->objectService->updateInfrastructure($object->id, $infrastructure);

        $rooms = $request->only(
            'number_of_place',
            'number_of_rooms',
            'room_equipment',
            'lighting',
            'sleeping_place_equipment',
            'flooring',
            'restroom_equipment',
            'restroom_square',
            'toilet_paper_type',
            'welcome_kit',
            'other_room_equipment',
            'rooms_for_disabilities'
        );
        if (count($rooms) > 0) $this->objectService->updateRoom($object->id, $rooms);

        $sportFood = $request->only(
            'chief-cooker_name',
            'kitchen_type',
            'food_point_description',
            'mini_bar',
            'foods',
            'has_calorie_dishes',
            'has_early_breakfast',
            'has_dry_ration',
            'room_service',
            'sport_services',
            'heating_program',
            'other_services',
            'ethernet_availability',
            'wifi_places',
            'other_it_service',
            'general_services'
        );
        if (count($sportFood) > 0) $this->objectService->updateFoodSport($object->id, $sportFood);

        /** Если что-то сохраняется, значит есть данные для анкеты */
        $object->has_worksheet = true;
        $object->save();


        return $this->objectService->getObject($object->id, true);
    }

    /**
     * @api {get} /api/account/object/common Получение общей информации
     * @apiVersion 0.1.0
     * @apiName GetCommonObject
     * @apiGroup AccountObject
     *
     * @apiHeader {string} Authorization access-token
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
        "modified_at": "2019-05-20 05:47:15",
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
            "fax": "",
            "email": "",
            "telephone": "",
            "messengers": {
                "skype": "",
                "telegram": "",
                "whatsapp": ""
            }
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
        "taxes": null,
        "other_taxes": {
            "existence": false,
            "description": ""
        },
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
                        "name_en": null,
                        "created_at": null,
                        "updated_at": "2019-05-16 11:41:30",
                        "service_category_id": 5,
                        "filter_name_ru": "Анимация",
                        "filter_name_en": null,
                        "is_filter": false,
                        "active": false,
                        "alias": "animatsiya-10"
                    },
                    {
                        "id": 8,
                        "name_ru": "Лобби-бар",
                        "name_en": null,
                        "created_at": null,
                        "updated_at": "2019-05-16 11:34:09",
                        "service_category_id": 4,
                        "filter_name_ru": "Лобби-бар",
                        "filter_name_en": null,
                        "is_filter": false,
                        "active": false,
                        "alias": "lobbi-bar"
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
                "status_id": 1,
                "value": null,
                "message": null,
                "time": null
            }
        }
    }
     *
     * @return array|\Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function common()
    {
        if (is_null(Auth::user()->object))
            throw new ApiProblemException('У пользователя нет прикрепленного объекта', 422 );

        return $this->objectService->common(Auth::user()->object->id);
    }

    /**
     * @api {get} /api/account/object/medical Получение информации по медицине
     * @apiVersion 0.1.0
     * @apiName GetMedicalObject
     * @apiGroup AccountObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 3,
        "object_id": 33,
        "head_doctor_name": null,
        "head_doctor_regalia": null,
        "climatic_factors": [],
        "water": {
            "water_type": [],
            "drinking_water_type": [],
            "chemical_composition": ""
        },
        "pump_room": {
            "type": "",
            "location": "",
            "existence": false
        },
        "healing_mud": [],
        "other_health_information": null,
        "voucher_without_accommodation": null,
        "personal_count": null,
        "certified_personal": [],
        "medical_equipment": null,
        "unique_therapy": null,
        "paid_therapy": null,
        "paid_spa": null,
        "exist_reanimation": false,
        "operation_in_object": {
            "description": "la-la-la",
            "other_taxes": true
        },
        "drinking_water_plumbing": {
            "existence": false,
            "description": ""
        },
        "contraindications_ru": "<ul>\r\n<li>Все заболевания в острой стадии, хронические заболевания в стадии обострения и осложненные острогнойными процессами.</li>\r\n<li>Острые инфекционные заболевания до окончания срока изоляции.</li>\r\n<li>Все венерические заболевания в острой и заразной форме.</li>\r\n<li>Все болезни крови в острой стадии и стадии обострения.</li>\r\n<li>Психические заболевания; все формы наркоманий и хронический алкоголизм; эпилепсия.</li>\r\n<li>Кахексия любого происхождения.</li>\r\n<li>Злокачественные новообразования (после радикального лечения при общем удовлетворительном состоянии, отсутствии метастазирования, нормальных показателях периферической крови могут направляться в местные санатории для общеукрепляющего лечения).</li>\r\n<li>Все заболевания и состояния, требующие стационарного лечения, в том числе и хирургического вмешательства, все заболевания, при которых больные не способны к самостоятельному передвижению и самообслуживанию, нуждаются в постоянном специальном уходе (кроме лиц, подлежащих лечению в специализированных санаториях для спинальных больных).</li>\r\n<li>Эхинококкоз любой локализации.</li>\r\n</ul>",
        "contraindications_en": "<p>All diseases in the acute stage, chronic diseases in the acute stage and complicated by acute processes.</p>",
        "created_at": "2019-05-20 05:49:13",
        "updated_at": "2019-05-20 05:49:13",
        "effective_months": [],
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
        }
    }
     *
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function medical()
    {
        if (is_null(Auth::user()->object))
            throw new ApiProblemException('У пользователя нет прикрепленного объекта', 422);

        $objectId = Auth::user()->object->id;
        $medicals = $this->objectService->medical($objectId);

        return response()->json($medicals, 200);
    }

    /**
     * @api {get} /api/account/object/infrastructure Получение информации по ифраструктуре
     * @apiVersion 0.1.0
     * @apiName GetInfrastructureObject
     * @apiGroup AccountObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 3,
        "object_id": 33,
        "working_by_season": true,
        "season_period": [],
        "months_peak": [],
        "months_lows": [],
        "contingent": [],
        "territory": {
            "area": "",
            "corps": {
                "floors": "",
                "number": "",
                "passages": "",
                "specific": ""
            },
            "relief": [],
            "description": "",
            "infrastructures": "",
            "steps_by_territory": ""
        },
        "elevators": null,
        "reservoir": {
            "name": "",
            "type": "",
            "beach": {
                "type": "",
                "existence": false,
                "availability": ""
            },
            "transfer": false,
            "existence": false,
            "remoteness": "",
            "description": ""
        },
        "pools": {
            "type": [],
            "water": {
                "temperature": "",
                "cleaning_period": ""
            },
            "existence": false,
            "descriptions": "",
            "additional_equipment": ""
        },
        "lifesavers": false,
        "training_apparatuses": false,
        "changing_rooms": false,
        "instructors": false,
        "parking": {
            "cost": "",
            "existence": false
        },
        "markets": {
            "existence": false,
            "working_time": ""
        },
        "pharmacies": {
            "existence": false,
            "working_time": ""
        },
        "created_at": "2019-05-21 05:04:58",
        "updated_at": "2019-05-21 05:04:58",
        "has_electro_cars": false,
        "infrastructure_for_disabilities": null,
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
        }
    }
     *
     * @return array|\Illuminate\Http\JsonResponse|null
     * @throws ApiProblemException
     */
    public function infrastructure()
    {
        if (is_null(Auth::user()->object))
            throw new ApiProblemException('У пользователя нет прикрепленного объекта', 422);

        $objectId = Auth::user()->object->id;
        $infrastructure = $this->objectService->infrastructure($objectId);

        return response()->json($infrastructure, 200);
    }

    /**
     * @api {get} /api/account/object/service Получение информации по услугам и сервисам
     * @apiVersion 0.1.0
     * @apiName GetServicesObject
     * @apiGroup AccountObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "object_id": 33,
        "chief-cooker_name": null,
        "kitchen_type": null,
        "food_point_description": null,
        "created_at": null,
        "updated_at": null,
        "mini_bar": {
            "existence": false,
            "composition": []
        },
        "foods": [],
        "has_calorie_dishes": false,
        "has_early_breakfast": false,
        "has_dry_ration": false,
        "room_service": {
            "existence": false,
            "description": ""
        },
        "sport_services": [],
        "heating_program": null,
        "other_services": [],
        "ethernet_availability": [],
        "wifi_places": [],
        "general_services": [],
        "other_it_service": "",
        "created_at": "2019-05-21 05:14:02",
        "updated_at": "2019-05-21 05:14:02",
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
        }
    }
     *
     * @return array|\Illuminate\Http\JsonResponse|null
     * @throws ApiProblemException
     */
    public function sportFood()
    {
        if (is_null(Auth::user()->object))
            throw new ApiProblemException('У пользователя нет прикрепленного объекта', 422);

        $objectId = Auth::user()->object->id;
        $sportFood = $this->objectService->sportFood($objectId);

        return response()->json($sportFood, 200);
    }

    /**
     * @api {get} /api/account/object/room Получение информации по номерам
     * @apiVersion 0.1.0
     * @apiName GetRoomsObject
     * @apiGroup AccountObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "object_id": 33,
        "number_of_place": null,
        "number_of_rooms": null,
        "room_equipment": null,
        "lighting": null,
        "flooring": null,
        "restroom_square": {
            "to": null,
            "from": null
        },
        "toilet_paper_type": null,
        "welcome_kit": {
            "rules": {
                "existence": false,
                "description": ""
            },
            "existence": false,
            "description": ""
        },
        "rooms_for_disabilities": {
            "number": 0,
            "existence": false
        },
        "sleeping_place_equipment": [],
        "restroom_equipment": [],
        "created_at": "2019-05-21 05:29:29",
        "updated_at": "2019-05-21 05:29:29",
        "room_description": null,
        "room_images": [
            {
                "id": 6,
                "object_id": 33,
                "image": "/storage/room_images/dFRDYw74HBrfVlioT4XKBUFzU2vbH4NexH5X1JAw.jpeg",
                "description": "text description"
            }
        ],
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
                "id": 29,
                "name_ru": "Спортивная площадка",
                "name_en": null
            }
        ]
    }
     *
     * @return array|\Illuminate\Http\JsonResponse|null
     * @throws ApiProblemException
     */
    public function room()
    {
        if (is_null(Auth::user()->object))
            throw new ApiProblemException('У пользователя нет прикрепленного объекта', 422);

        $objectId = Auth::user()->object->id;
        $rooms = $this->objectService->room($objectId);

        return response()->json($rooms, 200);
    }

    /**
     * @api {get} /api/account/object/current-services Получение активных услуг
     * @apiVersion 0.1.0
     * @apiName GetFilterConditionObject
     * @apiGroup AccountObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
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
        }
    }
     *
     * @return array|\Illuminate\Http\JsonResponse|null
     * @throws ApiProblemException
     */
    public function filterCondition()
    {
        if (is_null(Auth::user()->object))
            throw new ApiProblemException('У пользователя нет прикрепленного объекта', 422);

        $objectId = Auth::user()->object->id;
        $services = $this->objectService->filterCondition($objectId);

        return response()->json($services, 200);
    }

    /**
     * @api {post} /api/account/object/room-image добавление изображения номеров к объекту
     * @apiVersion 0.1.0
     * @apiName SaveImageRoom
     * @apiGroup AccountObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {file} image Файл изображения
     * @apiParam {string} description Описание изображения
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "object_id": 2,
        "image": "/storage/room_images/FbVi34zKhPJhseZySPiK5uMfQuUhQV1yp2rolPtJ.jpeg",
        "description": "text description",
        "id": 5
    }

     *
     * @param Request $request
     * @return \App\Models\ObjectRoomImage|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function saveRoomImage(Request $request)
    {
        $objectId = Auth::user()->object->id;
        if (is_null($objectId)){

            return response()->json(['account' =>
                ['У пользователя нет прикрепленного объекта']
            ], 422);
        } else {

            $valid = Validator($request->all(),[
                'image' => 'file|image|max:5128',
            ]);

            if ($valid->fails()){

                $response = [
                    'status' => 400,
                    'errors' => $valid->errors(),
                ];

                return response($response, $response['status']);
            }

            return $this->objectService->saveRoomImages($request, $objectId);
        }
    }

    /**
     * @api {put} /api/account/object/room-image/{imageId} Редактирование описания изображения номеров
     * @apiVersion 0.1.0
     * @apiName UpdateImageRoom
     * @apiGroup AccountObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} description Описание изображения
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "object_id": 2,
        "image": "/storage/room_images/FbVi34zKhPJhseZySPiK5uMfQuUhQV1yp2rolPtJ.jpeg",
        "description": "text description",
        "id": 5
    }
     *
     * @param Request $request
     * @param int $roomImageId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function updateRoomImage(Request $request, int $roomImageId)
    {
        if (is_null(Auth::user()->object))
            throw new ApiProblemException('У пользователя нет прикрепленного объекта', 422);
        $objectId = Auth::user()->object->id;
        $data = $request->only('description');
        $roomImage = $this->objectService->updateRoomImage($data, $objectId, $roomImageId);

        return response()->json($roomImage, 200);
    }

    /**
     * @api {delete} /api/account/object/room-image/{imageId} Удаление изображения номеров к объекту
     * @apiVersion 0.1.0
     * @apiName DeleteRoomImage
     * @apiGroup AccountObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "account": [
            "Изображение удалено"
        ]
    }
     *
     * @param int $imageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteRoomImage(int $imageId)
    {
        $objectId = Auth::user()->object->id;
        if (is_null($objectId)){

            return response()->json(['account' =>
                ['У пользователя нет прикрепленного объекта']
            ], 422);
        } else {

            $deleted = $this->objectService->deleteRoomImage($imageId, $objectId);
            if ($deleted){
                return response()->json(['account' =>
                    ['Изображение удалено']
                ], 200);
            } else {

                return response()->json(['account' =>
                    ['Изображение не наайдено']
                ], 404);
            }
        }
    }

    /**
     *
     * @api {get} /api/account/object/worksheet Получение данных для анкеты
     * @apiVersion 0.1.0
     * @apiName GetWorksheet
     * @apiGroup AccountObject
     *
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
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws ApiProblemException
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     * @throws \PhpOffice\PhpWord\Exception\Exception
    */
    public function getWorksheet(Request $request)
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

        if (is_null($user->object))
            throw new ApiProblemException('У пользователя нет прикрепленного объекта', 422);
        $objectId = $user->object->id;

        $type = $request->get('type') ?? 'doc';
        $download = $request->get('download') ?? true;
        $worksheet = $this->objectService->getWorksheet($objectId, (string)$type, (bool)$download);

        return $worksheet;
    }
}
