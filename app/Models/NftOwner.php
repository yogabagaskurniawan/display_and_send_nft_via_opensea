<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NftOwner extends Model
{
    protected $fillable = [
        'nft_id',
        'wallet_address',
        'balance',
    ];

    public function nft()
    {
        return $this->belongsTo(Nft::class);
    }
}
