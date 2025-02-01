<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    protected $fillable = [
        'name',
        'type',
        'status',
        'is_withdrawal',
        'is_deposit',
        'is_buy',
        'is_sell',
        'coin_icon',
        'is_base',
        'is_currency',
        'is_primary',
        'is_wallet',
        'is_transferable',
        'is_virtual_amount',
        'trade_status',
        'sign',
        'minimum_buy_amount',
        'minimum_sell_amount',
        'minimum_withdrawal',
        'maximum_withdrawal',
        'withdrawal_fees',
        'usd',
        'usd_24h_vol',
        'usd_24h_change',
        'last_updated_at',
        'description',
        'coin_rank',
        'website',
        'bg_color',
        'subbly',
        'coingecko_id',
        'coincodex_id'
    ];
}
