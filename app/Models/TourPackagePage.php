<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TourPackagePage extends Model
{
    public const SLUGS = ['indonesia', 'thailand', 'vietnam', 'philippines'];

    public const KIND_INTRODUCTION = 'introduction';

    public const KIND_PACKAGE = 'package';

    protected $fillable = [
        'name',
        'kind',
        'parent_slug',
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
        ];
    }

    public static function forSlug(string $slug): self
    {
        return static::query()->where('slug', $slug)->firstOrFail();
    }

    public function isPackage(): bool
    {
        return $this->kind === self::KIND_PACKAGE;
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

        return array_values(array_unique(array_filter(
            $paths,
            fn (string $path): bool => $path !== '' && parse_url($path, PHP_URL_SCHEME) === null,
        )));
    }

    /**
     * Starter content for a newly created page.
     *
     * @return array<string, mixed>
     */
    public static function starterContent(string $kind, string $name): array
    {
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
