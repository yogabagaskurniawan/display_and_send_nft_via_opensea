<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nft extends Model
{
    protected $fillable = [
        'contract_address',
        'token_id',
        'name',
        'description',
        'image',
        'token_standard',
        'supply',
    ];

    public function owners()
    {
        return $this->hasMany(NftOwner::class);
    }
}
