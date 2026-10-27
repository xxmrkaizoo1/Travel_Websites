<?php

namespace App\Models;

use Database\Factories\GroupTourDestinationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GroupTourDestination extends Model
{
    /** @use HasFactory<GroupTourDestinationFactory> */
    use HasFactory;

    public const MAX_STATES = 24;

    /** Slugs already taken by other pages under /group-tours. */
    public const RESERVED_SLUGS = ['booking-conditions'];

    public const MAX_REGIONS = 8;

    /**
     * Regions a page starts with until the admin sets its own, in display order.
     *
     * @var array<string, string>
     */
    public const DEFAULT_REGIONS = [
        'northern' => 'Northern',
        'central' => 'Central',
        'southern' => 'Southern',
    ];

    protected $fillable = [
        'slug',
        'name',
        'meta_description',
        'hero_eyebrow',
        'heading',
        'intro',
        'hero_image',
        'states_heading',
        'regions',
        'place_term',
        'states',
        'region_images',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'states' => 'array',
            'regions' => 'array',
            'region_images' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
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
     * This page's regions in display order, keyed by region slug.
     *
     * @return array<string, string>
     */
    public function regionOptions(): array
    {
        return filled($this->regions) ? array_column($this->regions, 'label', 'key') : self::DEFAULT_REGIONS;
    }

    /**
     * Regions as stored: a list keeps their order, which a JSON object column would not.
     *
     * @param  array<string, string>  $regions
     * @return array<int, array{key: string, label: string}>
     */
    public static function regionList(array $regions): array
    {
        return array_map(static fn (string $key, string $label): array => ['key' => $key, 'label' => $label], array_keys($regions), $regions);
    }

    /**
     * Regions that have at least one place, keyed by region slug.
     *
     * @return array<string, string>
     */
    public function regionsInUse(): array
    {
        $used = array_column($this->states ?? [], 'region');

        return array_filter($this->regionOptions(), static fn (string $label, string $key): bool => in_array($key, $used, true), ARRAY_FILTER_USE_BOTH);
    }

    /**
     * The word for the places this page lists, e.g. "state" or "destinations".
     */
    public function placeTerm(int $count = 1): string
    {
        return Str::plural($this->place_term ?: 'state', $count);
    }

    /**
     * The small badge above a place's name: "Capital · Kangar" for states, the area as written otherwise.
     */
    public function placeBadge(string $capital): string
    {
        if (blank($capital) || ($this->place_term ?: 'state') !== 'state' || $capital === 'Federal Territory') {
            return $capital;
        }

        return 'Capital · '.$capital;
    }

    /**
     * Photo for a region slide: the region's own photo, else the first state photo in it, else the banner.
     *
     * @return array{image: string, alt: string}
     */
    public function regionCover(string $regionKey): array
    {
        $regionImage = $this->region_images[$regionKey] ?? [];

        if (filled($regionImage['image'] ?? '')) {
            return ['image' => $this->imageUrl($regionImage['image']), 'alt' => (string) ($regionImage['alt'] ?? '')];
        }

        foreach ($this->states ?? [] as $state) {
            if ($state['region'] === $regionKey && filled($state['image'])) {
                return ['image' => $this->imageUrl($state['image']), 'alt' => (string) $state['alt']];
            }
        }

        return ['image' => $this->imageUrl($this->hero_image), 'alt' => ''];
    }

    public static function stateAnchor(string $stateName): string
    {
        return 'state-'.Str::slug($stateName);
    }

    /**
     * Uploaded images owned by this page, excluding bundled and remote images.
     *
     * @return array<int, string>
     */
    public function storedImagePaths(): array
    {
        return array_values(array_unique(array_filter(
            [$this->hero_image, ...array_column($this->states ?? [], 'image'), ...array_column($this->region_images ?? [], 'image')],
            static fn (?string $path): bool => filled($path) && ! str_starts_with($path, 'images/') && ! preg_match('#^https?://#', $path),
        )));
    }
}
