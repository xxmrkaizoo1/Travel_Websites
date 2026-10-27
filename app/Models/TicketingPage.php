<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TicketingPage extends Model
{
    public const MAX_AIRLINES = 24;

    public const MAX_PROMOTIONS = 9;

    /** Banner used when the admin clears the banner image. */
    public const DEFAULT_HERO_IMAGE = 'images/ticketing/banner.png';

    protected $fillable = [
        'meta_title',
        'meta_description',
        'hero_eyebrow',
        'hero_title',
        'hero_highlight',
        'hero_body',
        'hero_image',
        'airlines_label',
        'airlines_heading',
        'airlines',
        'promotions_label',
        'promotions_heading',
        'promotions_body',
        'promotions',
        'enquiry_label',
        'enquiry_heading',
        'enquiry_body',
        'whatsapp_number',
        'email',
        'phone',
    ];

    protected function casts(): array
    {
        return [
            'airlines' => 'array',
            'promotions' => 'array',
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

    public function whatsappUrl(?string $message = null): string
    {
        return 'https://wa.me/'.$this->whatsapp_number.($message === null ? '' : '?text='.rawurlencode($message));
    }

    /**
     * @param  array<string, string>  $promotion
     */
    public function promotionEnquiryUrl(array $promotion): string
    {
        return $this->whatsappUrl("Hi, I'd like to ask about the {$promotion['airline']} {$promotion['title']} fare ({$promotion['fare']}).");
    }

    /**
     * Uploaded images owned by this page, excluding bundled and remote files.
     *
     * @return array<int, string>
     */
    public function storedImagePaths(): array
    {
        return array_values(array_unique(array_filter(
            [$this->hero_image, ...array_column($this->airlines ?? [], 'logo'), ...array_column($this->promotions ?? [], 'image')],
            static fn (?string $path): bool => filled($path) && ! str_starts_with($path, 'images/') && ! preg_match('#^https?://#', $path),
        )));
    }
}
