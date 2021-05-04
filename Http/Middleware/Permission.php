<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiProblemException;
use App\Models\User;
use Closure;

class Permission
{
    /**
     * Handle an incoming request.
     *
     * @param $request
     * @param Closure $next
     * @param string $allow
     * @return mixed
     * @throws ApiProblemException
     */
    public function handle($request, Closure $next, ?string $allow = null)
    {
        if ( auth('api')->check() ){
            $user = auth('api')->user();
            if ( in_array($allow, $user->role->permissions) )
                return $next($request);
            else
                throw new ApiProblemException('Доступ запрещен, нет соответствующего разрешения', 403);
        } else {
            throw new ApiProblemException('Ошибка авторизации', 401);
        }
    }
}
