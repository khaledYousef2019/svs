<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name', 'mplan', 'price', 'min_price', 'max_price', 'fees_type', 'fees', 
        'expected_return', 'type', 'increment_interval', 'increment_type', 
        'increment_amount', 'expiration', 'gift', 'status'
    ];
    // public function clup()
    // {
    //     return $this->belongsTo(MembershipPlan::class,'mplan');
    // }
}
