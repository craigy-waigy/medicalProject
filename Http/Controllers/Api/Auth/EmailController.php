<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Notifications\EmailConfirmed;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Client;

class EmailController extends Controller
{
    /**
     * @api {post} /api/auth/confirm-email Подтверждение почты
     * @apiVersion 0.1.0
     * @apiName EmailConfirmation
     * @apiGroup Auth
     *
     * @apiParam {String} confirm_token токен подтверждения
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
        HTTP/1.1 200 OK
        {
            "status": 200,
            "errors": {
                "user": [
                    "Почта успешно подтверждена"
                ]
            }
        }
     *
     * * @apiErrorExample {json} Не верный токен или подтверждение уже произошло ранее:
     *     HTTP/1.1 404 Not Found
     *     {
     *          "status": 404,
                "errors": {
                    "user": [
                        "Пользователь не найден"
                    ]
                }
     *     }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function confirmEmail(Request $request)
    {
        $valid = validator($request->only('confirm_token','client_secret'), [
            'confirm_token' => 'required',
        ],[
            'confirm_token.required' => "Необходимо предоставить confirm_token",
        ]);

        if ($valid->fails()) {

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        $data = $request->only('confirm_token');

        $user = User::where([
            ['confirm_token', $data['confirm_token']],
            ['email_confirmed', false],
        ])->first();

        if ( is_null($user)) {
            $response = [
                'status' => 404,
                'errors' => [ 'user' => ['Пользователь не найден']],
            ];
            return response($response, $response['status']);
        }

        $user->email_confirmed = true;
        $user->self_registered = true;
        $user->save();

        $user->notify( new EmailConfirmed() );

        $response = [
            'status' => 200,
            'errors' => [ 'user' => ['Почта успешно подтверждена']],
        ];
        return response($response, $response['status']);
    }
}
