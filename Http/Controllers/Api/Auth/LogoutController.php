<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LogoutController extends Controller
{
    /**
     * @api {post} /api/auth/logout выход пользователя
     * @apiVersion 0.1.0
     * @apiName Logout
     * @apiGroup Auth
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Successfully logged out"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        activity()->performedOn($user)->log('Пользователь вышел');

        $user->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
