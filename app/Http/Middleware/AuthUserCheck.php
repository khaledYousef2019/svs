<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthUserCheck
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
                    if ($user->role == USER_ROLE_USER) {
                        // Check if 2FA is enabled and verified
                        if ($user->g2f_enabled && !session()->has('g2f_checked')) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Two-factor authentication check is required.'
                            ], 403);
                        }
                        return $next($request);
                    } else {
                        // Return JSON response for unauthorized access
                        return response()->json([
                            'success' => false,
                            'message' => 'You are not authorized to access this panel.'
                        ], 403);
                    }
                } else {
                    // Inactive account
                    return response()->json([
                        'success' => false,
                        'message' => 'Your account is deactivated, please contact admin.'
                    ], 403);
                }
            } else {
                // Email not verified
                return response()->json([
                    'success' => false,
                    'message' => 'Please verify your email.'
                ], 403);
            }
        } else {
            // User is not authenticated
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.'
            ], 401);
        }
    }
}
