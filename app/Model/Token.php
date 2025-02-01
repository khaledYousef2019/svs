<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $fillable = [
        'name',
        'symbol',
        'chain_id',
        'contract_address',
        'holder_address',
        'cap',
        'supply',
        'decimals',
        'price',
        'base_pair',
        'network',
        'status',
        'withdraw_fee',
        'withdraw_max',
        'withdraw_min',
        'coingecko_id',
        'coincodex_id',
    ];

    /**
     * Get the chain associated with the token.
     */
    public function chain()
    {
        return $this->belongsTo(Chain::class);
    }
}
