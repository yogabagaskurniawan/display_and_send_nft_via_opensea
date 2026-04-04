<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class WeaponTemplate extends Model
{
    protected $fillable = [
        'template_id_onchain',
        'name',
        'slug',
        'description',
        'rarity',
        'weapon_type',
        'base_attack',
        'base_defense',
        'image_ipfs_hash',
        'image_uri',
        'image_url',
        'metadata_ipfs_hash',
        'metadata_uri',
        'metadata_url',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name . '-' . uniqid());
            }
        });
    }

}
