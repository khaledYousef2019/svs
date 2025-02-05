<?php

namespace App\Helpers;

use Carbon\Carbon;

class PlanHelper
{
    public static function getPlanExpirationDate(string $duration): string
    {
        $parts = explode(' ', $duration);
        if (count($parts) !== 2) {
            return now()->addMonth()->toDateTimeString();
        }

        [$value, $unit] = $parts;
        $method = 'add' . ucfirst(strtolower($unit));
        
        if (method_exists(Carbon::class, $method)) {
            return now()->$method($value)->toDateTimeString();
        }

        return now()->addMonth()->toDateTimeString();
    }
} 