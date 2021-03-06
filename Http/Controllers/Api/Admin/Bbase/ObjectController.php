<?php

namespace App\Http\Controllers\Api\Admin\Bbase;

use App\Exceptions\ApiProblemException;
use App\Models\ObjectPlace;
use App\Rules\IsArray;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Passport\Token;
use App\Services\ObjectImageService;
use App\Services\BbaseService;


class ObjectController extends Controller
{
    const ALEAN_PROVIDER_ID = 1;

    protected $objectImageService;
    protected $bBaseService;

    public function __construct()
    {
        $this->objectImageService = new ObjectImageService();
        $this->bBaseService = new BbaseService();
    }

/**
 * @api {get} /api/admin/bbase/object/{objectId}/{providerId}/medical-info получение медицинской информации о санатории с бронебазы
 * @apiVersion 0.1.0
 * @apiName GetMedicalInfo
 * @apiGroup AdminBbase
 *
 * @apiHeader {string} Authorization access-token
 *
 * @apiSuccessExample {json} Ответ сервера в случае успеха:
HTTP/1.1 200 OK
{
    "name": "им. 30-летия Победы (Железноводск)",
    "description": "<p>Санаторий «имени 30 лет Победы» по праву носит звание ведущей здравницы в области диагностики и лечения урологических заболеваний. Однако на этом список доступных лечебно-оздоровительных услуг не заканчивается. Также с успехом диагностируются и лечатся заболевания, среди которых болезни женских половых органов и желудочно-кишечного тракта.</p><p>В архитектурный ансамбль санатория “Им. 30 лет Победы” (Железноводск) входит три корпуса. Между 1 и 2 корпусом расположены обеденный зал, киноконцертный зал, высокотехнологичный лечебно-диагностический центр. Все корпуса санатория соединяются теплыми переходами.</p><p>Отдельно стоит отметить, что на территории санатория “Им. 30 лет Победы” располагается бювет природной минеральной воды «Славяновская». Отдыхающим предложат как одно- или двухместные комнаты, так и более комфортабельные двух- или трехместные апартаменты “люкс”. Удачное расположение санатория по отношению к природным красотам откроет неповторимые пейзажи из окон всех отдыхающих. Санаторий стоит на берегу живописного озера, окружен естественным лесным массивом.</p><p>Для лечения постояльцев санатория “Им. 30 лет Победы” в Железноводске используется современная и функциональная лечебно-диагностическая база. В дополнение к применяемому спектру основных процедур широко применяется лечение минеральной водой, диетотерапия, бальнеотерапия, лечебные души, гидромассаж, грязелечение и большое количество сопутствующих лечебных процедур. На основе широких собственных возможностей санатория разработаны и применяются несколько различных программ лечения, в их числе и детские оздоровительные мероприятия.</p><p>Санаторий “Им. 30 лет Победы” работает по заказной системе питания. Это означает, что для каждого отдыхающего открыта возможность разработки и внедрения индивидуальной системы питания, что вкупе с проводимыми лечебными процедурами дает наилучший терапевтический эффект.</p><p>Санаторий может предложить отдыхающим и широкий спектр развлекательных услуг. В них входят собственный бассейн, а также многочисленные спортивные помещения, в числе которых комнаты для совместной игры в настольный теннис, шахматы и бильярд. Также к услугам проживающих парикмахерская, залы для маникюра и педикюра.</p><p>Любители спокойного отдыха высоко оценят собственный кинозал санатория, библиотеку и широкий выбор экскурсионных программ. Особенно богатые развлекательные программы в санатории проводятся в период государственных праздничных дней.</p><div><br></div>",
    "required_documents": "<p><strong>Для взрослых:</strong> </p><ul><li>ваучер</li><li>общегражданский российский паспорт</li><li>страховой полис обязательного медицинского страхования (или ДМС)</li><li>санаторно-курортная карта (не более 2 месяцев от даты получения) - <strong>для всех видов программ пребывания</strong></li></ul><p><strong>Для детей</strong>: </p><ul><li>свидетельство о рождении</li><li>страховой полис обязательного медицинского страхования (или ДМС)</li><li>справка об эпидемиологическом окружении (не более 21 дня от даты получения)</li><li>анализ на энтеробиоз (не более 3 месяцев от даты получения)</li><li>заключение врача-дерматолога об отсутствии кожных заболеваний</li><li>справка о прививках</li><li>справка врача-педиатра (или эпидемиолога) об отсутствии контактов с инфицированными больными по месту жительства, в детском саду или школе</li></ul>",
    "contraindications": "",
    "special_requirements": "Лечение возможно при заезде на срок не менее 12 дней. При досрочном отъезде санаторий удерживает стоимость 1-их суток проживания. Полный возврат осуществляется при наличии уважительной причины: смерть близкого родственника (при пред",
    "profiles": "<div><p>Андрологические заболевания, урологические заболевания: </p><ul><li>Хронические воспалительные заболевания мужской половой сферы: болезни предстательной железы (хронический простатит), болезни семенного пузырька (хронический везикулит), эпидидимиты, орхиты.</li></ul></div><div><p>Гинекологические заболевания: </p><ul><li>Невоспалительные заболевания женских половых органов: </li><li>Расстройства менструального цикла: </li><li>Хронические воспалительные заболевания женских половых органов: сальпингит и оофорит хронические, тазовые перитонеальные спайки у женщин.</li></ul></div><div><p>Заболевания желудочно-кишечного тракта: </p><ul><li>Заболевания желудка: гастрит и дуоденит, язвенная болезнь желудка и двенадцатиперстной кишки в стадии ремиссии.</li><li>Заболевания желчного пузыря, желчевыводящих путей и поджелудочной железы: дискинезия желчного пузыря и желчных путей, желчно-каменная болезнь (холелитиаз), панкреатит хронический, состояние после холецистоэктомии, спазм сфинктера Одди, холангит, холециститы хронический.</li><li>Заболевания кишечника: брюшинные спайки, запоры хронические, энтерит/колит хронический.</li><li>Заболевания органов пищеварения: болезнь оперированного желудка, гастроптоз (опущение желудка), перигастриты, перидуодениты, перигепатиты, перихолециститы, периколиты (вне фазы обострения)  .</li><li>Заболевания пищевода: гастроэзофагеальный рефлюкс, эзофагит.</li></ul></div><div><p>Заболевания мочеполовой системы: </p><ul><li>мочекаменная болезнь без обструкции мочевыводящих путей (Д81см).: </li><li>оксалатурия: </li><li>уретрит хронический: </li><li>фосфатурия: </li><li>хронический пиелит, пиелонефрит в фазе ремиссии и латентного воспалительного процесса: </li><li>цистит хронический: </li></ul></div><div><p>Заболевания нервной системы: </p><ul><li>Заболевания вегетативной нервной системы: астеноневротический синдром, психоастении.</li><li>Заболевания периферической нервной системы: люмбаго, остеохондроз позвоночника с корешковым синдромом, радукулопатия, радикулиты  .</li></ul></div><div><p>Заболевания опорно-двигательного аппарата, болезни костно-мышечной системы: </p><ul><li>Заболевания мягких тканей, скелетных мышц, сухожилий: миозит.</li><li>Заболевания суставов и позвоночника: артрозы, остеохондроз позвоночника, подагра идиопатическая, полиартриты, полиартроз, полиостеоартроз, сколиоз (I-II степени), спондилез.</li></ul></div><div><p>Заболевания органов дыхания и лор-органов: </p><ul><li>Заболевания ЛОР-органов: гайморит хронический, ларингит хронический, отиты хронические, синусит хронический, тонзиллит хронический, фарингит хронический, фронтит хронический.</li><li>Хронические заболевания легких: бронхит хронический, трахеит хронический.</li></ul></div><div><p>Заболевания эндокринной системы, расстройства питания и нарушения обмена веществ: </p><ul><li>молочнокислый диатез (подагра): </li><li>ожирение (первичное): </li><li>сахарный диабет (инсулинзависимый) 2 тип.: </li></ul></div>",
    "methods": "<p>Альтернативные методы лечения: ароматерапия, гирудотерапия, фитотерапия.</p><p>Аппаратная физиотерапия: вибрация и ультразвук, ультразвуковая терапия, светолечение, лазеротерапия, электротерапия и магнитотерапия, КВЧ-терапия, УВЧ-терапия, индуктотермия, магнитотерапия, магнитотурботрон, электросон.</p><p>Бальнеотерапия: лечебные ванны, вихревые ванны, жемчужные ванны, йодобромные ванны, пенно-солодковые ванны, лечебные души, веерный душ, восходящий душ, душ Шарко, подводный душ - массаж, циркулярный душ, орошения минеральной водой, орошение десен.</p><p>Газолечение: озонотерапия.</p><p>Грязелечение: гальваногрязелечение, грязевые аппликации, грязевые вагинальные тампоны, грязевые ректальные тампоны, парадонтогрязь, электрогрязь.</p><p>Иглорефлексотерапия: классическая акупунктура.</p><p>Ингаляции: травяные ингаляции, щелочные ингаляции.</p><p>Колонопроктология: гидроколонотерапия, микроклизмы, промывание кишечника.</p><p>Массажное отделение: антицеллюлитный массаж, вакуумный массаж (баночный массаж, аппаратом LPG), зональный лечебный классический массаж, лимфодренажный массаж, мануальный массаж, общий лечебный классический массаж, расслабляющий массаж, термическая массажная кровать.</p><p>Психотерапия: аутогенная тренировка.</p>",
    "diagnostic": "<div><p>Отделение лабораторной диагностики:</p><ul><li>Бактериологическая лаборатория; посев крови. </li><li>Биохимическая лаборатория; биохимические исследования крови, диагностика инфекций методом ПЦР, спермограмма, цитологические исследования. </li><li>Лаборатория клинической иммунологии; иммунологические исследования (выявление маркеров вирусных инфекций, онкомаркеров и др.), СА125,ПСА, иммуноферментный анализ. </li><li>Общеклиническая лаборатория; клинический анализ крови, коагулограмма (свертываемость крови), копрограмма , общий анализ мочи. </li></ul></div><div><p>Отделение функциональной диагностики:</p><ul><li>Диагностика головного мозга (Нейрофизиологические исследования); реоэнцефалография, электроэнцефалография, эхоэнцефалоскопия. </li><li>Диагностика кровеносных сосудов; реовазография верхних конечностей. </li><li>Диагностика сердечной мышцы; суточное мониторирование АД, холтеровское мониторирование (динамическая ЭКГ), электрокардиография (ЭКГ). </li></ul></div><div><p>Отделение эндоскопических исследований:</p><ul><li>Колоноскопия</li><li>Кольпоскопия</li><li>Ректороманоскопия</li><li>Уретроскопия</li><li>Цистоскопия</li><li>Эзофагогастродуоденоскопия (ЭГДС)</li></ul></div><div><p>Рентгенодиагностические исследования:</p><ul><li>Внутривенная урография</li><li>Нисходящая цистография</li><li>Рентгенография</li><li>Томография: легких<-DESC]</li><li>Функциональная рентгенография позвоночника</li></ul></div><div><p>Ультра-звуковые исследования:</p><ul><li>УЗИ внутренних органов</li><li>УЗИ сердца</li><li>Нейросонография (УЗИ головного мозга ребенка)</li><li>Ультразвуковая допплерография</li></ul></div>",
    "department": "<p>Кабинеты нетрадиционной медицины: кабинет ароматерапии, кабинет гирудотерапии, кабинет иглорефлексотерапии.</p>\r\n<p>Лечебные кабинеты: кабинет озонотерапии, кабинет психотерапии, кабинет электросна.</p>\r\n<p>Массажное отделение: кабинет мануальной терапии, массажные кабинеты, термическая массажная кровать.</p>\r\n<p>Отделение стоматологии: стоматологический кабинет.</p>\r\n<p>Процедурные кабинеты: ЛОР-процедурный кабинет, кабинет гинекологических процедур, кабинет колоногидротерапии, кабинет процедур орошения, кабинет урологических процедур, процедурный кабинет (инъекции).</p>\r\n",
    "price_includes": "<ul><li>проживание в номере выбранной категории</li><li>питание 3-разовое меню-заказ</li><li>санаторно-курортное лечение</li><li>пользование бассейном</li><li>пользование тренажерным залом </li></ul>",
    "extra_charged": "<ul><li>пользование бильярдом</li><li>пользование СПА</li><li>курортный сбор – 50 рублей в сутки с человека</li></ul>",
    "service_list": "<p>Интернет - wi-fi в номерах; wi-fi на территории.</p>"
}
     *
     * @param integer $objectId - id санатория на Здравпродукте
     * @param integer $providerId id поставщика
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMedicalInfo(int $objectId, int $providerId)
    {
        $objectPlace = ObjectPlace::find($objectId);

        if (empty($objectPlace)) {
            return response()->json(['message' => 'Объект здравпродукта не найден'], 404);
        }

        if (empty($objectPlace->bbase->bbase_id)) {
            return response()->json(['message' => 'Объект здравпродукта не связан с объектом бронебазы'], 404);
        }

        $bbaseObjectId = $objectPlace->bbase->bbase_id;

        $medicalInfo = $this->bBaseService->getMedicalInfo($bbaseObjectId, $providerId);

        return response()->json($medicalInfo, 200);

        //if ($medicalInfo->success == true) return response()->json($medicalInfo->data, 200);
    }

    /**
     * @api {get} /api/admin/bbase/object/{objectId}/{providerId}/images получение изображений санатория с бронебазы
     * @apiVersion 0.1.0
     * @apiName GetImages
     * @apiGroup AdminBbase
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "url": "https://uploads2.stells.info/storage/jpg/8/a7/8a77dd4974a40dfe4dca2931d16b7510.jpg",
            "description": "Внешний вид. Корпус"
        },
        {
            "url": "https://uploads2.stells.info/storage/jpg/e/3f/e3fbcd7f5a5ff1e68f6d46870326666d.jpg",
            "description": "Вход на территорию"
        }
     ]
     *
     * @param integer $objectId
     * @param integer $providerId id поставщика
     * @return \Illuminate\Http\JsonResponse
     */
    public function getImages(int $objectId, int $providerId)
    {
        $objectPlace = ObjectPlace::find($objectId);

        if (empty($objectPlace)) {
            return response()->json(['message' => 'Объект здравпродукта не найден'], 404);
        }

        if (empty($objectPlace->bbase->bbase_id)) {
            return response()->json(['message' => 'Объект здравпродукта не связан с объектом бронебазы'], 404);
        }

        $bbaseObjectId = $objectPlace->bbase->bbase_id;

        $images = $this->bBaseService->getObjectImages($bbaseObjectId, $providerId);

        if ($images->success == true) return response()->json($images->data[0]->HotelImageList, 200);



    }

    /**
     * @api {get} /api/admin/bbase/object/getProviderBbaseObjects/{providerId}?search= получение объектов провайдера с бронебазы
     * @apiVersion 0.1.0
     * @apiName GetBbaseProviderObjects
     * @apiGroup AdminBbase
     *
     * @apiParam {string} [$search] ключ для поиска санаториев в бронебазе
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
    "provider_id": 1,
    "objects": [
            {
                "id": 7,
                "short_name": "Казахстан (Ессентуки)",
                "resort_name": "Казахстан (Ессентуки)",
                "resort_type": "Санаторий",
                "country": "Россия",
                "area": "Кавказские Минеральные Воды",
                "region": "Ставропольский край",
                "address": "357600, г. Ессентуки, ул. Пятигорская 44",
                "is_sanatorium": true
            },
            {
                "id": 12,
                "short_name": "им. 30-летия Победы (Жел",
                "resort_name": "им. 30-летия Победы (Железноводск)",
                "resort_type": "Санаторий",
                "country": "Россия",
                "area": "Кавказские Минеральные Воды",
                "region": "Ставропольский край",
                "address": "ул. Ленина 2а",
                "is_sanatorium": true
            }
        ]
    }
     *
     *
     * @param int $providerId id поставщика
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProviderBbaseObjects(int $providerId, Request $request)
    {
        $search = $request->get('search');

        if ($providerId != self::ALEAN_PROVIDER_ID) {
            return response()->json(['message' => 'Провайдер не найден'], 404);
        }

        $results = $this->bBaseService->getAleanBbaseObjects($search);

        if (!$results) {
            $resultWithProvider = [
                'provider_id' => self::ALEAN_PROVIDER_ID,
                'objects' => []
            ];

            return response()->json($resultWithProvider, 200);
        }

        if (isset($results->success) && $results->success == true){
            $resultWithProvider = [
                'provider_id' => self::ALEAN_PROVIDER_ID,
                'objects' => $results->data
            ];

            return response()->json($resultWithProvider, 200);
        }
    }

    /**
     * @api {get} /api/admin/bbase/object/provider-relations/{objectId} получение связи объекта здравпродукта с объектом провайдера
     * @apiVersion 0.1.0
     * @apiName getProviderRelations
     * @apiGroup AdminBbase
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 11,
            "bbase_object": {
            "id": 5,
            "name": "Ромашка"
            },
            "provider": {
            "id": 1,
            "name": "Алеан"
            }
        }
    ]
     *
     * @param int $objectId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRelations(int $objectId)
    {

        $results = $this->bBaseService->getBbaseRelations($objectId);

        if ($results) {
            return response()->json([$results], 200);
        } else {
            return response()->json([], 200);
        }


    }

    /**
     * @api {post} /api/admin/bbase/object/provider-relation установление связи объекта здравпродукта с объектом провайдера
     * @apiVersion 0.1.0
     * @apiName setProviderRelations
     * @apiGroup AdminBbase
     *
     * @apiDescription Если у передаваемого санатория здравпродукта была связь с другим санаторием Бронебазы, то предыдущая связь удаляется.Если у передаваемого санатория бронебазы была связь с другим санаторием Здравпродукта, то предыдущая связь удаляется.
     *
     * @apiParam {integer} provider_id ID поставщика
     * @apiParam {integer} object_id ID санатория в здравпродукте
     * @apiParam {integer} bbase_id ID санатория в бронебазе
     * @apiParam {string} bbase_object_name имя санатория в бронебазе
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Связь установлена"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setRelation(Request $request)
    {
        $valid = Validator($request->only('object_id', 'bbase_id', 'provider_id', 'bbase_object_name'),[
            'provider_id' => 'integer|required',
            'object_id' => 'integer|required',
            'bbase_id' => 'integer|required',
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $providerId = $request->get('provider_id');
        $objectId = $request->get('object_id');
        $bbaseId = $request->get('bbase_id');
        $bbaseObjectName = $request->get('bbase_object_name');

        if ($providerId != self::ALEAN_PROVIDER_ID) {
            return response()->json(['message' => 'Провайдер не найден'], 404);
        }


        $this->bBaseService->setBbaseRelation($objectId, $bbaseId, $providerId, $bbaseObjectName);

        return response()->json(['message' => 'Связь установлена']);
    }

    /**
     * @api {delete} /api/admin/bbase/object/provider-relation/{id} удаление связи объекта здравпродукта с объектом провайдера
     * @apiVersion 0.1.0
     * @apiName DeleteProviderRelations
     * @apiGroup AdminBbase
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Связь удалена"
    }
     *
     * @param int $id id связи санатория на здравпродукте и санатория у поставщика
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteRelation($id)
    {
        $results = $this->bBaseService->deleteBbaseRelation($id);

        return response()->json(['message' => 'Связь удалена']);
    }

}
