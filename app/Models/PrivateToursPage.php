<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PrivateToursPage extends Model
{
    public const MAX_DESTINATIONS = 12;

    public const MAX_PERKS = 6;

    public const WHATSAPP_NUMBER = '60165500590';

    /** Banner photo used when the admin clears the banner image. */
    public const DEFAULT_HERO_IMAGE = 'https://images.unsplash.com/photo-1528181304800-259b08848526?auto=format&fit=crop&w=2000&q=90';

    /** Introduction photo used when the admin clears the introduction image. */
    public const DEFAULT_INTRO_IMAGE = 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1000&q=85';

    protected $fillable = [
        'meta_title',
        'meta_description',
        'hero_eyebrow',
        'hero_title',
        'hero_highlight',
        'hero_body',
        'hero_cta_label',
        'hero_image',
        'hero_note',
        'perks',
        'intro_label',
        'intro_heading',
        'intro_highlight',
        'intro_body',
        'intro_link_label',
        'intro_image',
        'intro_image_alt',
        'intro_caption',
        'destinations_label',
        'destinations_heading',
        'destinations_body',
        'destinations',
        'contact_label',
        'contact_heading',
        'contact_body',
        'contact_cta_label',
    ];

    protected function casts(): array
    {
        return [
            'perks' => 'array',
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

    public function heroImageUrl(): string
    {
        return $this->imageUrl(filled($this->hero_image) ? $this->hero_image : self::DEFAULT_HERO_IMAGE);
    }

    public function introImageUrl(): string
    {
        return $this->imageUrl(filled($this->intro_image) ? $this->intro_image : self::DEFAULT_INTRO_IMAGE);
    }

    public function contactUrl(): string
    {
        return 'https://wa.me/'.self::WHATSAPP_NUMBER.'?text='.rawurlencode("Hi, I'd like to ask about private tour packages.");
    }

    /**
     * Uploaded images owned by this page, excluding bundled and remote files.
     *
     * @return array<int, string>
     */
    public function storedImagePaths(): array
    {
        return array_values(array_unique(array_filter(
            [$this->hero_image, $this->intro_image, ...array_column($this->destinations ?? [], 'image')],
            static fn (?string $path): bool => filled($path) && ! str_starts_with($path, 'images/') && ! preg_match('#^https?://#', $path),
        )));
    }
}
