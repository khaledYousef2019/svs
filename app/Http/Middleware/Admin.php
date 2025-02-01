<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Admin
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

        if (!empty($user)) {
            if (!empty($user->is_verified)) {
                if ($user->status == STATUS_ACTIVE) {
                    if ($user->role == USER_ROLE_ADMIN) {
                        return $next($request);
                    } else {
                        // Return JSON response for unauthorized access
                        return response()->json([
                            'success' => false,
                            'message' => 'You are not eligible to access this panel.'
                        ], 403);
                    }
                } else {
                    // Return JSON response for deactivated account
                    return response()->json([
                        'success' => false,
                        'message' => 'Your account is deactivated. Please contact the admin.'
                    ], 403);
                }
            } else {
                // Return JSON response for unverified email
                return response()->json([
                    'success' => false,
                    'message' => 'Please verify your email.'
                ], 403);
            }
        } else {
            // Return JSON response for unauthenticated user
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.'
            ], 401);
        }
    }
}
