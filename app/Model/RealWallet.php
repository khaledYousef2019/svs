<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RealWallet extends Model
{
    protected $fillable = [
        'chain_id',
        'mnemonic',
        'xpub',
        'private_key',
        'address',
    ];

    /**
     * Get the chain associated with the real wallet.
     */
    public function chain()
    {
        return $this->belongsTo(Chain::class);
    }
}
