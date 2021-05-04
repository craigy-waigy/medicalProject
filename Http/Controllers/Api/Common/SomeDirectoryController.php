<?php

namespace App\Http\Controllers\Api\Common;

use App\Services\SomeDirectoryService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SomeDirectoryController extends Controller
{
    /**
     * @var SomeDirectoryService
     */
    protected $someDirectoryService;

    /**
     * SomeDirectoryController constructor.
     *
     * @param SomeDirectoryService $someDirectoryService
     */
    public function __construct(SomeDirectoryService $someDirectoryService)
    {
        $this->someDirectoryService = $someDirectoryService;
    }

    /**
     * @api {get} /api/some-directory Получение каких нибудь справочников)
     * @apiVersion 0.1.0
     * @apiName GetPublicDirectories
     * @apiGroup SomeDirectories
     *
     * @apiParam {string} [type] Тип списка (Для получение конкретного списка)
     *
     * @apiSuccessExample {json} Если type не отправлен:
    HTTP/1.1 200 OK
    {
        "certified_personal": [
            "Аллерголог-иммунолог",
            "Гинеколог",
            "Врач восстановительной медицины"
        ],
        "climatic_factors": [
            "приморский",
            "горный",
            "предгорный",
            "лесостепной зоны"
        ],
        "contingent": [
            "Взрослые",
            "Дети без родителей",
            "Родители с детьми"
        ],
        "healing_mud": [
            "торфяная",
            "сульфидная иловая",
            "сапропелевая"
        ],
        "messengers": [
            "skype",
            "telegram",
            "whatsapp",
            "viber"
        ],
        "mini_bar": [
            "Коньяк",
            "Водка",
            "Вино"
        ],
        "room_equipment": [
            "ТВ",
            "Радио",
            "Халат",
            "Кресло"
        ],
        "for_disabilities": [
            "лифты",
            "бассейны"
        ],
        "relief": [
            "Ровный",
            "Холмистый",
            "Смешанный",
            "Смешанный"
        ],
        "water_type": [
            "гидрокарбонатная",
            "хлоридная",
            "сульфатная",
            "гидрокарбонатно-хлоридная",
            "гидрокарбонатно-сульфатная",
            "гидрокарбонатно-хлоридно-сульфатная",
            "сложного состава"
        ],
        "drinking_water_type": [
            "слабо минерализированная",
            "минерализированная",
            "водопроводная"
        ]
    }
     * @apiSuccessExample {json} Если type = certified_personal:
    [
        "Аллерголог-иммунолог",
        "Гинеколог",
        "Врач восстановительной медицины"
    ]
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $valid = Validator($request->all(), [
            'type' => 'string|nullable'
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $type = $request->get('type');
        if (is_null($type))
            $directories = $this->someDirectoryService->getAll();
        else
            $directories = $this->someDirectoryService->get($type);

        return response()->json($directories, 200);
    }
}
