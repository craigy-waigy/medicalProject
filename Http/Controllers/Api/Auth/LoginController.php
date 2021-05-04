<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Client;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $valid = validator($request->all(), [
            'username' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
            'client_secret' => 'required',
            'grant_type' => 'required',
            'client_id' => 'required',
            'scope' => 'present',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $client = Client::where([
            ['id', $request->get('client_id')],
            ['secret', $request->get('client_secret')],
            ['password_client', true],
        ])->first();

        if (is_null($client)) {
            $response = [
                'status' => 401,
                'errors' => 'Application authentication failed',
            ];
            return response($response, $response['status']);
        }

        $credentials = ['email' => mb_strtolower($request->get('username')), 'password' => $request->get('password')];
        if(!Auth::attempt($credentials)) {
            return response()->json([
                'error' => 'invalid_credentials', 'message' => "The user credentials were incorrect."
            ], 401);
        }

        $user = $request->user();
        $tokenResult = $user->createToken($client->name);
        $token = $tokenResult->token;
        $token->save();

        activity()
            ->performedOn($user)
            ->log("Вход пользователя");

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => (new \DateTime($tokenResult->token->expires_at))->getTimestamp()
    ]);


//        if (\Auth::attempt(['email' => $request->get('username'), 'password' =>  $request->get('password')])) {
//            $user = \Auth::user();
//            $success['token'] = $user->createToken('Laravel Personal Access Client')->accessToken;
//            return response()->json(['success' => $success], 200);
//        } else {
//            var_dump('asdasdad');die;
//            return response()->json(['error' => 'Unauthorised'], 401);
//        }
    }
}
