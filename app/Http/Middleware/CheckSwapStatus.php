<?php

namespace App\Http\Middleware;

use App\Model\AdminSetting;
use Closure;

class CheckSwapStatus
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
        $data = AdminSetting::where('slug', 'swap_enabled')->first();

        if ($data) {
            if ($data->value) {
                // If the swap feature is enabled, proceed with the request
                return $next($request);
            } else {
                // Return JSON response indicating that the feature is disabled
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'Swap feature is disabled. Please try again later.'
                ], 503); // 503 Service Unavailable
            }
        } else {
            // If the setting is not found, proceed with the request
            return $next($request);
        }
    }
}
