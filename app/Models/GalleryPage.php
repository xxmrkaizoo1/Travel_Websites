<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GalleryPage extends Model
{
    public const MAX_DESTINATIONS = 12;

    public const MAX_ALBUM_PHOTOS = 8;

    protected $fillable = [
        'meta_title',
        'meta_description',
        'hero_eyebrow',
        'hero_title',
        'hero_body',
        'hero_image',
        'moments_eyebrow',
        'moments_heading',
        'moments_body',
        'moments_card_label',
        'moments_card_eyebrow',
        'destinations',
    ];

    protected function casts(): array
    {
        return [
            'destinations' => 'array',
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

    /**
     * Photos shown in a destination's popup album, falling back to its postcard photo.
     *
     * @param  array<string, mixed>  $destination
     * @return array<int, array{src: string, caption: string}>
     */
    public function albumPhotos(array $destination): array
    {
        $photos = array_map(fn (array $photo): array => [
            'src' => $this->imageUrl($photo['image']),
            'caption' => $photo['caption'] ?? '',
        ], $destination['photos'] ?? []);

        return $photos ?: [['src' => $this->imageUrl($destination['photo']), 'caption' => $destination['photo_alt']]];
    }

    /**
     * Uploaded images owned by this page, excluding bundled and remote images.
     *
     * @return array<int, string>
     */
    public function storedImagePaths(): array
    {
        $destinations = $this->destinations ?? [];
        $paths = [$this->hero_image, ...array_column($destinations, 'flag'), ...array_column($destinations, 'photo')];

        foreach ($destinations as $destination) {
            array_push($paths, ...array_column($destination['photos'] ?? [], 'image'));
        }

        return array_values(array_unique(array_filter(
            $paths,
            static fn (?string $path): bool => filled($path) && ! str_starts_with($path, 'images/') && ! preg_match('#^https?://#', $path),
        )));
    }
}
