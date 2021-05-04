<?php

namespace App\Http\Controllers\Api\Counters;

use App\Exceptions\ApiProblemException;
use App\Models\Banner;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BannerController extends Controller
{
    /**
     * @api {post} /api/counter/banner-click Счетчик кликов по баннеру
     * @apiVersion 0.1.0
     * @apiName BannerClick
     * @apiGroup Counters
     *
     * @apiParam {integer} banner_id ID баннера
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "счетчик обновлен"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function countClick(Request $request)
    {
        //TODO: Сделать ограничение по IP адресу.
        $valid = Validator($request->all(), [
            'banner_id' => 'required|integer'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $bannerId = $request->get('banner_id');
        $banner = Banner::find($bannerId);
        if (is_null($banner)) throw new ApiProblemException('Баннер не найден', 404);
        $banner->increment('count_clicks');

        return response()->json(['message' => 'счетчик обновлен'], 200);
    }
}
