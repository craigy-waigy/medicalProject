<?php

namespace App\Http\Controllers\Api\Counters;

use App\Models\ObjectViewingCount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ObjectController extends Controller
{
    /**
     * @api {post} /api/counter/object-viewing Счетчик просмотров объекта
     * @apiVersion 0.1.0
     * @apiName ObjectViewing
     * @apiGroup Counters
     *
     * @apiParam {integer} object_id ID объекта
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {"counter":"success incremented"}
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function countViewing(Request $request)
    {
        //TODO: Сделать ограничение по IP адресу.
        $valid = Validator($request->all(), [
           'object_id' => 'required|integer|exists:objects,id'
        ], [
            'object_id.exists' => ':attribute не найден'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        ObjectViewingCount::create([
           'object_id' => $request->get('object_id')
        ]);

        return response()->json(['counter' => 'success incremented'], 200);
    }
}
