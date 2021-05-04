<?php

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\ApiProblemException;
use App\Models\User;
use App\Notifications\EmailConfirmation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Passport\Client;

class RegisterController extends Controller
{
    /**
     * @api {post} /api/auth/register Регистрация пользователя
     * @apiVersion 0.1.0
     * @apiName RegisterUser
     * @apiGroup Auth
     *
     * @apiParam {String} name Имя
     * @apiParam {String} last_name Фамилие
     * @apiParam {String} father_name Отчество
     * @apiParam {String} phone телефон
     * @apiParam {String} email почта
     * @apiParam {String} password пароль
     * @apiParam {string} client_secret passport secret
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
        HTTP/1.1 200 OK
       {
            "token_type": "Bearer",
            "expires_in": 1296000,
            "access_token": "access-token",
            "refresh_token": "refresh-token"
        }

     */
    public function register(Request $request)
    {
        $valid = validator($request->only('email', 'name', 'password','client_secret'), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
            'client_secret' => 'required',
        ],[
            'email.unique' => "Указанный email уже существует"
        ]);

        if ($valid->fails()) {

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }
        $data = request()->only('email','name','password', 'client_secret', 'father_name', 'last_name', 'phone');

        $client = Client::where([
            ['secret', $data['client_secret']],
            ['password_client', true],
        ])->first();

        if (is_null($client)) {
            $response = [
                'status' => 401,
                'errors' => 'Application authentication failed',
            ];
            return response($response, $response['status']);
        }

        $userExist = User::where('email', $request->get('email'))->where('self_registered', true)->count();
        if ($userExist > 0)
            throw new ApiProblemException('Пользователь с указанным email уже зарегистрирован', 422);


        $newUser = User::where('email', $request->get('email'))->where('self_registered', false)->first();
        if (is_null($newUser)){
            $newUser = new User();
        }
        $confirmToken = str_random(50);
        $newUser->confirm_token = $confirmToken;
        $newUser->name =  $data['name'];
        $newUser->email = $data['email'];
        $newUser->role_id = User::ROLE_USER;
        $newUser->father_name = $data['father_name'] ?? null;
        $newUser->last_name =   $data['last_name'] ?? null;
        $newUser->phone =       $data['phone'] ?? null;
        $newUser->password = bcrypt($data['password']);
        $newUser->self_registered = true;
        $newUser->save();

        $newUser->notify(new EmailConfirmation( $confirmToken ));

        $request->request->add([
            'grant_type'    => 'password',
            'client_id'     => $client->id,
            'client_secret' => $client->secret,
            'username'      => $data['email'],
            'password'      => $data['password'],
            'scope'         => null,
        ]);

        $token = Request::create(
            'oauth/token',
            'POST'
        );

        return \Route::dispatch($token);
    }
}
