<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NftTransfer extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_from',
        'wallet_to',
        'contract_address',
        'token_id',
        'amount',
        'tx_hash',
        'chain',
        'status',
    ];
}
