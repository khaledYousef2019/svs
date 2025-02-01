<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Chain extends Model
{
    protected $fillable = [
        'name',
        'network_type',
        'chain_links',
        'chain_id',
        'previous_block_count',
        'gas_limit',
        'status',
    ];

    /**
     * Get the real wallet associated with the chain.
     */
    public function realWallet()
    {
        return $this->hasOne(RealWallet::class);
    }

    /**
     * Get the tokens for the chain.
     */
    public function tokens()
    {
        return $this->hasMany(Token::class);
    }
    public function walletAddressHistories()
    {
        return $this->hasMany(WalletAddressHistory::class, 'chain_id');
    }
}
