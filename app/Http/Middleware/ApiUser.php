<?php

namespace App\Http\Middleware;

use App\Exceptions\UserApiException;
use Closure;
use Illuminate\Support\Facades\Auth;

class ApiUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->role == USER_ROLE_USER && $user->status == STATUS_SUCCESS) {
            if ($user->is_verified == 1) {
                return $next($request);
            } else {
                throw new UserApiException(__('Your email is not verified. Please verify your email.'), 401);
            }
        } else {
            throw new UserApiException(__('You are not authorized.'), 401);
        }
    }
}
