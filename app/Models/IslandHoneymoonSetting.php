<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class IslandHoneymoonSetting extends Model
{
    protected $fillable = [
        'hero_eyebrow',
        'hero_headline',
        'hero_highlight',
        'hero_body',
        'hero_cta_label',
        'hero_note',
        'hero_image',
        'intro_eyebrow',
        'intro_headline',
        'intro_highlight',
        'intro_body',
        'intro_link_label',
        'intro_link_href',
        'intro_image',
        'intro_image_alt',
        'intro_image_caption',
        'highlights_eyebrow',
        'highlights_headline',
        'highlights_body',
        'highlights',
        'contact_eyebrow',
        'contact_headline',
        'contact_body',
        'contact_cta_label',
        'contact_href',
    ];

    protected function casts(): array
    {
        return [
            'highlights' => 'array',
        ];
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
