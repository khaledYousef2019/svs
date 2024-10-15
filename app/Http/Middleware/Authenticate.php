<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            // Return a JSON response for React to handle instead of redirecting to login
            return response()->json([
                'message' => 'Unauthenticated.',
                'redirect_url' => route('login'),  // Provide the login URL for client-side navigation
            ], 401); // 401 Unauthorized HTTP status code
        }
    }
}
