<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPlans extends Model
{
    protected $dates = [
        'created_at',
        'updated_at', 
        'activated_at',
        'last_growth',
        'last_fees'
    ];

    protected $fillable = [
        'plan',
        'user',
        'amount',
        'expected_return',
        'active',
        'fees',
        'inv_duration',
        'increment_amount',
        'increment_interval',
        'last_fees',
        'expire_date',
        'activated_at',
        'last_growth'
    ];

    public function dplan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan', 'id');
    }

    public function duser(): HasOne 
    {
        return $this->hasOne(User::class, 'id', 'user');
    }

    public function dclub(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'mplan', 'id');
    }

    public function dtransations(): HasMany
    {
        return $this->hasMany(InvplanTransactions::class, 'user', 'user')
            ->where('status', 'Credit');
    }
}
