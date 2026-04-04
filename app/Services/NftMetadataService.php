<?php

namespace App\Services;

use App\Models\WeaponTemplate;

class NftMetadataService
{
    public function buildTemplateMetadata(WeaponTemplate $template, ?int $tokenId = null): array
    {
        $displayName = $tokenId !== null
            ? "{$template->name} #{$tokenId}"
            : $template->name;

        return [
            'name' => $displayName,
            'description' => $template->description ?: 'Weapon NFT item',
            'image' => $template->image_uri, // ipfs://...
            'external_url' => config('app.url') . '/weapons/' . $template->slug,

            'attributes' => [
                [
                    'trait_type' => 'Weapon Type',
                    'value' => $template->weapon_type,
                ],
                [
                    'trait_type' => 'Rarity',
                    'value' => $template->rarity,
                ],
                [
                    'trait_type' => 'Base Attack',
                    'value' => (int) $template->base_attack,
                ],
                [
                    'trait_type' => 'Base Defense',
                    'value' => (int) $template->base_defense,
                ],
                [
                    'trait_type' => 'Category',
                    'value' => 'Weapon',
                ],
            ],
        ];
    }
}