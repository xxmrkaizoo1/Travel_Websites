<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ContactPage extends Model
{
    public const MAX_COMPANIES = 6;

    /** Banner used when the admin clears the banner image. */
    public const DEFAULT_HERO_IMAGE = 'images/smart-holidays-banner.jpg';

    protected $fillable = [
        'meta_title',
        'meta_description',
        'hero_eyebrow',
        'hero_title',
        'hero_body',
        'hero_image',
        'companies_heading',
        'companies',
        'map_query',
    ];

    protected function casts(): array
    {
        return [
            'companies' => 'array',
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

    public function mapEmbedUrl(): string
    {
        return 'https://www.google.com/maps?output=embed&q='.rawurlencode($this->map_query);
    }

    public function mapDirectionsUrl(): string
    {
        return 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($this->map_query);
    }

    /**
     * Splits a "03-2143 0188 / 2143 0288" style list into links. A shortened number
     * borrows the missing leading digits (area code) from the first number.
     *
     * @return array<int, array{label: string, href: string}>
     */
    public function numberLinks(string $numbers, bool $whatsapp = false): array
    {
        $links = [];
        $firstDigits = null;

        foreach (preg_split('#\s*/\s*#', trim($numbers), -1, PREG_SPLIT_NO_EMPTY) as $label) {
            $digits = preg_replace('/\D/', '', $label);

            if ($digits === '') {
                continue;
            }

            $firstDigits ??= $digits;

            if (strlen($digits) < strlen($firstDigits)) {
                $digits = substr($firstDigits, 0, strlen($firstDigits) - strlen($digits)).$digits;
            }

            $international = str_starts_with($digits, '60') ? $digits : '6'.$digits;

            $links[] = [
                'label' => $label,
                'href' => $whatsapp ? "https://wa.me/{$international}" : "tel:+{$international}",
            ];
        }

        return $links;
    }

    public function websiteUrl(string $website): string
    {
        return preg_match('#^https?://#', $website) ? $website : 'https://'.$website;
    }

    /**
     * Uploaded images owned by this page, excluding bundled and remote files.
     *
     * @return array<int, string>
     */
    public function storedImagePaths(): array
    {
        return array_values(array_unique(array_filter(
            [$this->hero_image, ...array_column($this->companies ?? [], 'logo')],
            static fn (?string $path): bool => filled($path) && ! str_starts_with($path, 'images/') && ! preg_match('#^https?://#', $path),
        )));
    }
}
