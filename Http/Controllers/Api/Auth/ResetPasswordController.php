<?php

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\ApiProblemException;
use App\Models\User;
use App\Notifications\PasswordResetSuccess;
use App\Notifications\SendPasswordReset;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ResetPasswordController extends Controller
{
    /**
     * @api {post} /api/auth/forgot-password Запрос на обновление пароля
     * @apiVersion 0.1.0
     * @apiName ForgotPassword
     * @apiGroup Auth
     *
     * @apiParam {String} email почта
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "На почту было отправлено письмо с данными для восстановления"
    }
     * @apiErrorExample {json} Ответ сервера в случае превышения лимита запросов:
    HTTP/1.1 429 Too Many Requests
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function forgotPassword(Request $request)
    {
        $valid = Validator($request->all(), [
            'email' => 'required|email'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $email = $request->get('email');
        $user = User::where('email', $email)->first();
        if (is_null($user)) throw new ApiProblemException('Пользователь не найден', 404);
        $user->password_reset_token = str_random(50);
        $user->save();
        $url = app('config')['app']['url'] . '/password-reset/' . $user->password_reset_token;

        $user->notify(new SendPasswordReset($url));

        return response()->json([
            'message' => 'На почту было отправлено письмо с данными для восстановления'
        ],200);
    }

    /**
     * @api {post} /api/auth/reset-password Обновление пароля
     * @apiVersion 0.1.0
     * @apiName ResetPassword
     * @apiGroup Auth
     *
     * @apiParam {String} email Почта
     * @apiParam {String} password Новый пароль
     * @apiParam {String} token Токен из ссылки в почте
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Пароль успешно изменён"
    }
     * @apiErrorExample {json} Ответ сервера в случае превышения лимита запросов:
    HTTP/1.1 429 Too Many Requests
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function resetPassword(Request $request)
    {
        $valid = Validator($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'token' => 'required|string|min:6',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $email = $request->get('email');
        $password = $request->get('password');
        $token = $request->get('token');

        $user = User::where('email', $email)->where('password_reset_token', $token)->first();

        if (is_null($user)) throw new ApiProblemException('Пользователь не найден', 404);
        $user->password = bcrypt($password);
        $user->password_reset_token = null;
        $user->save();
        $user->notify(new PasswordResetSuccess());

        return response()->json([
            'message' => 'Пароль успешно изменён'
        ],200);

    }
}
