<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MicePage extends Model
{
    protected $fillable = [
        'hero_eyebrow',
        'hero_title',
        'hero_highlight',
        'hero_body',
        'hero_cta_label',
        'hero_image',
        'intro_eyebrow',
        'intro_heading',
        'intro_highlight',
        'intro_body',
        'intro_image',
        'intro_image_alt',
        'intro_image_caption',
        'why_eyebrow',
        'why_heading',
        'highlights',
        'contact_eyebrow',
        'contact_heading',
        'contact_body',
        'phone_primary',
        'phone_secondary',
        'email',
        'whatsapp_number',
        'clientele_eyebrow',
        'clientele_heading',
        'clientele_body',
    ];

    protected function casts(): array
    {
        return [
            'highlights' => 'array',
        ];
    }

    public function imageUrl(string $path): string
    {
        return static::resolveImageUrl($path);
    }

    public static function resolveImageUrl(string $path): string
    {
        if (in_array(parse_url($path, PHP_URL_SCHEME), ['http', 'https'], true)) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }
}
