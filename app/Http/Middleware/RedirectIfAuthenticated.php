<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // If the user is already authenticated
        if (Auth::guard($guard)->check()) {
            return response()->json([
                'message' => 'User is already authenticated.',
                'redirect_url' => '/home',
            ], 403);  // Use an appropriate HTTP status code
        }

        return $next($request);
    }
}
