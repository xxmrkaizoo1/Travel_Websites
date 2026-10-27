<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class TourPackagePage extends Model
{
    public const SLUGS = ['indonesia', 'thailand', 'vietnam', 'philippines'];

    public const KIND_INTRODUCTION = 'introduction';

    public const KIND_PACKAGE = 'package';

    public const PLACEHOLDER_IMAGE = 'images/placeholder-photo.svg';

    public const PLACEHOLDER_GALLERY_SIZE = 5;

    protected $fillable = [
        'name',
        'kind',
        'theme',
        'parent_slug',
        'smart_muslim_menu_id',
        'meta_title',
        'meta_description',
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
        'intro_image',
        'intro_image_alt',
        'intro_image_caption',
        'highlights_eyebrow',
        'highlights_headline',
        'highlights_body',
        'highlights',
        'destinations_eyebrow',
        'destinations_headline',
        'destinations_body',
        'slides',
        'package_data',
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
            'slides' => 'array',
            'package_data' => 'array',
            'is_custom' => 'boolean',
            'booking_open' => 'boolean',
        ];
    }

    public static function forSlug(string $slug): self
    {
        return static::query()->where('slug', $slug)->firstOrFail();
    }

    /**
     * Bookings clients made online for this package.
     *
     * @return MorphMany<Booking, $this>
     */
    public function bookings(): MorphMany
    {
        return $this->morphMany(Booking::class, 'bookable');
    }

    public function isPackage(): bool
    {
        return $this->kind === self::KIND_PACKAGE;
    }

    public function smartMuslimMenu(): BelongsTo
    {
        return $this->belongsTo(SmartMuslimMenu::class);
    }

    /**
     * Body class for the page's colour theme, e.g. "tour-theme-emerald"; unknown themes fall back to the default.
     */
    public function themeClass(): string
    {
        return 'tour-theme-'.(isset(self::THEMES[$this->theme]) ? $this->theme : self::DEFAULT_THEME);
    }

    public function isSeries(): bool
    {
        return ! empty($this->package_data['series']);
    }

    public function hasItineraries(): bool
    {
        return $this->isPackage() && ! $this->isSeries();
    }

    public function sectionLabel(): string
    {
        return $this->isSeries() ? 'Tour Package (Series)' : 'Tour Package (Private)';
    }

    public function publicUrl(): string
    {
        return $this->is_custom ? route('tour-package.show', $this->slug) : route($this->slug);
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
     * Uploaded (non-URL) image paths currently used by this page.
     *
     * @return array<int, string>
     */
    public function storedImagePaths(): array
    {
        $paths = [$this->hero_image, $this->intro_image];

        foreach ($this->slides ?? [] as $slide) {
            $paths[] = $slide['image'] ?? '';
        }

        foreach ($this->package_data['gallery'] ?? [] as $slide) {
            $paths[] = $slide['image'] ?? '';
        }

        foreach ($this->package_data['series'] ?? [] as $destination) {
            $paths[] = $destination['flag'] ?? '';

            foreach ($destination['gallery'] ?? [] as $photo) {
                $paths[] = $photo['image'] ?? '';
            }
        }

        return array_values(array_unique(array_filter(
            $paths,
            fn (string $path): bool => $path !== '' && parse_url($path, PHP_URL_SCHEME) === null && ! str_starts_with($path, 'images/'),
        )));
    }

    /**
     * Starter content for a newly created page.
     *
     * @return array<string, mixed>
     */
    public const DEFAULT_THEME = 'ocean';

    /**
     * Colour themes for custom pages: name, main colour and dark colour (used for the admin swatches).
     *
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    public const THEMES = [
        'ocean' => ['Ocean Blue', '#2364d2', '#12305e'],
        'emerald' => ['Emerald Green', '#0f7a5c', '#12304f'],
        'sunset' => ['Sunset Orange', '#e0662a', '#4a2412'],
        'royal' => ['Royal Purple', '#6d3fc4', '#2a1a52'],
        'ruby' => ['Ruby Red', '#c62842', '#3d1220'],
    ];

    public const TEMPLATES = [
        self::KIND_INTRODUCTION => [
            'slideshow' => ['Destination slideshow', 'Hero, introduction, three highlights, a sliding destination carousel and contact — like Thailand or Vietnam.'],
            'series' => ['Series destinations', 'Country tabs with an advantages grid, flag card and five-photo gallery per destination — like Series Departures 2026.'],
        ],
        self::KIND_PACKAGE => [
            'standard' => ['Full itinerary', 'Itinerary cards with the day-by-day plan, gallery, inclusions, exclusions and hotels — like Bali or Aceh.'],
            'compact' => ['Compact packages', 'Same sections, but itinerary cards show only the title and summary without the day-by-day list.'],
        ],
    ];

    /**
     * Templates offered for a page kind. Pages inside a series page get the series template first, whatever their kind.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function templatesFor(string $kind, bool $isInsideSeries = false): array
    {
        $templates = self::TEMPLATES[$kind] ?? [];

        if ($isInsideSeries) {
            $templates = ['series' => self::TEMPLATES[self::KIND_INTRODUCTION]['series'], ...$templates];
        }

        return $templates;
    }

    public static function defaultTemplate(string $kind, bool $isInsideSeries = false): string
    {
        return array_key_first(self::templatesFor($kind, $isInsideSeries));
    }

    public static function starterContent(string $kind, string $name, ?string $template = null): array
    {
        $template = array_key_exists((string) $template, self::templatesFor($kind, true)) ? $template : self::defaultTemplate($kind);
        $whatsapp = 'https://wa.me/60165500590?text='.rawurlencode("Hi, I'd like to plan a private trip to {$name}.");
        $beach = 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=85';
        $coast = 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=1000&q=85';
        $isPackage = $kind === self::KIND_PACKAGE;

        $content = [
            'name' => $name,
            'kind' => $kind,
            'meta_title' => "{$name} Private Tours | Smart Muslim by Roystar Travel & Tours",
            'meta_description' => "Discover {$name} with a private tour planned around you.",
            'hero_eyebrow' => 'PRIVATE JOURNEYS',
            'hero_headline' => "Discover {$name}.",
            'hero_highlight' => 'Explore your way.',
            'hero_body' => "Plan a private trip to {$name} at your own pace, shaped around the places and experiences you love.",
            'hero_cta_label' => $isPackage ? 'Explore the packages' : "Plan your {$name} trip",
            'hero_note' => 'THOUGHTFUL TRAVEL · YOUR PACE',
            'hero_image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=2000&q=90',
            'intro_eyebrow' => "WELCOME TO {$name}",
            'intro_headline' => 'Find your own rhythm in',
            'intro_highlight' => "{$name}.",
            'intro_body' => "Tell our team what you would love to see and we will help shape a private itinerary for {$name}.",
            'intro_link_label' => $isPackage ? 'See the itineraries' : 'Explore places to discover',
            'intro_image' => $coast,
            'intro_image_alt' => $name,
            'intro_image_caption' => 'Made for your kind of escape.',
            'highlights_eyebrow' => 'A JOURNEY WITH MORE TO DISCOVER',
            'highlights_headline' => 'Moments worth travelling for.',
            'highlights_body' => 'Build a trip around the places, flavours and moments you want to take home.',
            'highlights' => [
                ['title' => 'Local highlights', 'body' => 'Visit the landmarks and scenery that make this destination special.'],
                ['title' => 'Culture & flavour', 'body' => 'Enjoy local food, traditions and meaningful time with the people who live here.'],
                ['title' => 'Travel at your pace', 'body' => 'Choose a relaxed stay or combine several stops in one thoughtfully planned trip.'],
            ],
            'destinations_eyebrow' => '',
            'destinations_headline' => '',
            'destinations_body' => '',
            'slides' => [],
            'package_data' => null,
            'contact_eyebrow' => "LET'S PLAN YOUR TRIP",
            'contact_headline' => 'Tell us what you would love to discover.',
            'contact_body' => 'Share your preferred places, travel dates and the kind of trip you have in mind.',
            'contact_cta_label' => 'Enquire on WhatsApp',
            'contact_href' => $whatsapp,
        ];

        if ($template === 'series') {
            $content['meta_title'] = "{$name} | Smart Muslim by Roystar Travel & Tours";
            $content['meta_description'] = "Join our {$name} group series departures.";
            $content['hero_eyebrow'] = 'SERIES DEPARTURES';
            $content['destinations_eyebrow'] = 'PICK YOUR DESTINATION';
            $content['destinations_headline'] = "Where will {$name} take you?";
            $content['destinations_body'] = 'Choose a destination to see its advantages and photos.';
            $content['package_data'] = ['series' => [[
                'name' => 'First destination',
                'country' => 'Country',
                'flag' => '',
                'tagline' => 'A short tagline for this destination.',
                'intro' => 'Describe this destination here.',
                'advantages' => [
                    ['icon' => '💰', 'title' => 'First advantage', 'body' => 'Describe the advantage here.'],
                    ['icon' => '🧳', 'title' => 'Second advantage', 'body' => 'Describe the advantage here.'],
                    ['icon' => '👥', 'title' => 'Third advantage', 'body' => 'Describe the advantage here.'],
                    ['icon' => '🛡️', 'title' => 'Fourth advantage', 'body' => 'Describe the advantage here.'],
                ],
                'gallery' => [],
            ]]];

            return $content;
        }

        if (! $isPackage) {
            $content['destinations_eyebrow'] = 'A FEW PLACES TO BEGIN';
            $content['destinations_headline'] = "Where will {$name} take you?";
            $content['destinations_body'] = 'Start with a place that speaks to you. We can help you explore the details.';
            $content['slides'] = [
                ['image' => $beach, 'alt' => 'Sunny tropical beach', 'eyebrow' => 'COASTAL GETAWAY', 'title' => 'First destination', 'body' => 'Describe this destination here.', 'link_label' => '', 'link_url' => ''],
                ['image' => $coast, 'alt' => 'Tropical coastline', 'eyebrow' => 'ISLAND ESCAPE', 'title' => 'Second destination', 'body' => 'Describe this destination here.', 'link_label' => '', 'link_url' => ''],
                ['image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=85', 'alt' => 'Scenic green landscape', 'eyebrow' => 'NATURE', 'title' => 'Third destination', 'body' => 'Describe this destination here.', 'link_label' => '', 'link_url' => ''],
            ];

            return $content;
        }

        $content['package_data'] = [
            'template' => $template,
            'itineraries_eyebrow' => "CHOOSE YOUR {$name} PACKAGE",
            'itineraries_headline' => "Ways to experience {$name}.",
            'itineraries_body' => 'All tours include private transport, accommodation, guided visits and meals as listed.',
            'itineraries' => [[
                'duration' => '3 DAYS · 2 NIGHTS',
                'title' => 'Signature package',
                'summary' => 'A relaxed introduction to the best of the destination.',
                'featured' => false,
                'days' => [
                    ['title' => 'Arrival', 'body' => 'Meet your local representative and transfer to the hotel.', 'meals' => 'MEALS NOT INCLUDED'],
                    ['title' => 'Sightseeing', 'body' => 'A full day of guided visits to the main attractions.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                    ['title' => 'Departure', 'body' => 'Check out and transfer to the airport.', 'meals' => 'BREAKFAST'],
                ],
                'cta_label' => 'Enquire about this package',
                'cta_url' => $whatsapp,
            ]],
            'gallery_eyebrow' => 'A GLIMPSE OF THE DESTINATION',
            'gallery_headline' => "{$name} highlights slideshow",
            'gallery_body' => 'Explore the scenery and places featured in your tour.',
            'gallery' => [
                ['image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1800&q=85', 'alt' => $name, 'eyebrow' => 'DISCOVER', 'title' => $name],
            ],
            'details_eyebrow' => 'GOOD TO KNOW',
            'details_headline' => "What's included in your tour.",
            'details_body' => 'Contact our team for pricing, travel dates and availability.',
            'inclusions' => ['Transportation throughout the trip', 'Hotel accommodation with breakfast', 'Private tours and entrance fees'],
            'exclusions' => ['Airfare', 'Travel insurance', 'Personal expenses'],
            'accommodation_eyebrow' => 'YOUR STAY',
            'accommodation_headline' => 'Where you will stay.',
            'accommodation_body' => 'Hotel accommodation is included in the package.',
            'hotels' => [],
        ];

        return $content;
    }
}
