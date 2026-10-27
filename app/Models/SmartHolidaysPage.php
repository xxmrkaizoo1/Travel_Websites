<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SmartHolidaysPage extends Model
{
    public const STAT_COUNT = 4;

    public const SERVICE_COUNT = 4;

    public const MAX_JOURNEY_SLIDES = 8;

    /** Largest background video an admin may upload (50 MB). */
    public const MAX_VIDEO_KILOBYTES = 51200;

    protected $fillable = [
        'meta_title',
        'meta_description',
        'hero_eyebrow',
        'hero_title',
        'hero_body',
        'hero_cta_label',
        'hero_secondary_label',
        'hero_image',
        'hero_image_alt',
        'stats',
        'services_eyebrow',
        'services_heading',
        'services',
        'journey_slides',
        'popular_eyebrow',
        'popular_heading',
        'popular_body',
        'popular_video',
        'brands_eyebrow',
        'brands_heading',
        'cta_heading',
        'cta_body',
        'cta_label',
        'whatsapp_number',
    ];

    protected function casts(): array
    {
        return [
            'stats' => 'array',
            'services' => 'array',
            'journey_slides' => 'array',
        ];
    }

    public function imageUrl(string $path): string
    {
        if (in_array(parse_url($path, PHP_URL_SCHEME), ['http', 'https'], true)) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }

    public function whatsappUrl(): string
    {
        return 'https://wa.me/'.$this->whatsapp_number;
    }

    /**
     * Uploaded images and videos owned by this page, excluding bundled and remote files.
     *
     * @return array<int, string>
     */
    public function storedImagePaths(): array
    {
        $slides = $this->journey_slides ?? [];
        $paths = [$this->hero_image, $this->popular_video, ...array_column($slides, 'image'), ...array_column($slides, 'video')];

        return array_values(array_unique(array_filter(
            $paths,
            static fn (?string $path): bool => filled($path) && ! str_starts_with($path, 'images/') && ! preg_match('#^https?://#', $path),
        )));
    }
}
