<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomepageCampaign extends Model
{
    protected $fillable = [
        'slug',
        'design_key',
        'layout',
        'position',
        'kicker',
        'headline',
        'headline_highlight',
        'body',
        'cta_label',
        'image_main',
        'image_top',
        'image_bottom',
        'image_alt',
        'sticker',
        'ribbon',
        'brand_top',
        'brand_bottom',
    ];

    public function isBanner(): bool
    {
        return $this->layout === 'banner';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function imageUrl(string $path): string
    {
        $scheme = parse_url($path, PHP_URL_SCHEME);

        if (in_array($scheme, ['http', 'https'], true)) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
