<?php

namespace Database\Seeders;

use App\Models\TourPackagePage;
use Illuminate\Database\Seeder;

class IslandHoneymoonPackagePagesSeeder extends Seeder
{
    private const IMAGES = [
        'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=85',
        'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=1800&q=85',
        'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1800&q=85',
        'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1800&q=85',
    ];

    public function run(): void
    {
        foreach ($this->pages() as $slug => $definition) {
            $content = TourPackagePage::starterContent(TourPackagePage::KIND_PACKAGE, $definition['name']);
            $content['meta_title'] = $definition['name'].' Honeymoon Packages | Smart Muslim by Roystar Travel & Tours';
            $content['meta_description'] = $definition['description'];
            $content['hero_headline'] = $definition['name'].' honeymoon';
            $content['hero_highlight'] = 'made for two.';
            $content['hero_body'] = $definition['description'];
            $content['hero_note'] = 'HONEYMOON · PRIVATE TOUR';
            $content['intro_body'] = $definition['description'];
            $content['package_data'] = $definition['package'];

            $page = TourPackagePage::query()->firstOrNew(['slug' => $slug]);

            if ($page->exists) {
                continue;
            }

            $page->fill($content);
            $page->slug = $slug;
            $page->parent_slug = 'island-honeymoon';
            $page->is_custom = true;
            $page->save();
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function pages(): array
    {
        $whatsapp = fn (string $name): string => 'https://wa.me/60165500590?text='.rawurlencode("Hi, I'd like to ask about the {$name} honeymoon package.");

        $gallery = fn (string $name): array => array_map(
            fn (string $image, int $index): array => [
                'image' => $image,
                'alt' => $name.' scenery',
                'eyebrow' => 'DISCOVER',
                'title' => $name.' · '.($index + 1),
            ],
            self::IMAGES,
            array_keys(self::IMAGES),
        );

        $package = fn (string $name, array $itineraries, array $inclusions, array $exclusions, array $hotel, string $detailsBody) => [
            'itineraries_eyebrow' => "CHOOSE YOUR {$name} PACKAGE",
            'itineraries_headline' => "Ways to experience {$name}.",
            'itineraries_body' => 'All tours include private transport, accommodation, guided visits and meals as listed.',
            'itineraries' => array_map(
                fn (array $itinerary): array => $itinerary + [
                    'cta_label' => 'Enquire about this package',
                    'cta_url' => $whatsapp($itinerary['title']),
                ],
                $itineraries,
            ),
            'gallery_eyebrow' => 'A GLIMPSE OF THE DESTINATION',
            'gallery_headline' => "{$name} gallery",
            'gallery_body' => 'Explore the scenery and places featured in your tour.',
            'gallery' => $gallery($name),
            'details_eyebrow' => 'GOOD TO KNOW',
            'details_headline' => "What's included in your tour.",
            'details_body' => $detailsBody,
            'inclusions' => $inclusions,
            'exclusions' => $exclusions,
            'accommodation_eyebrow' => 'YOUR STAY',
            'accommodation_headline' => 'Where you will stay.',
            'accommodation_body' => 'Hotel accommodation is included in the package.',
            'hotels' => [$hotel],
        ];

        $englishIncludes = [
            'Transportation throughout the trip',
            'Hotel accommodation with daily breakfast',
            'Private tours and entrance fees',
            'Mineral water (1 bottle during the tour)',
            'Full meals (breakfast, lunch, dinner)',
        ];
        $englishExcludes = [
            'Airfare',
            'Travel insurance',
            'Visa (if required)',
            'Peak season surcharge',
            'Optional tours',
            'Personal expenses such as laundry and souvenirs',
        ];

        return [
            'krabi' => [
                'name' => 'Krabi',
                'description' => 'Island hopping, candlelit dinners and Halal-friendly stays in Krabi — three itineraries for couples.',
                'package' => $package('Krabi', [
                    [
                        'duration' => '4 DAYS · 3 NIGHTS',
                        'title' => '4D3N Krabi Honeymoon',
                        'summary' => 'Phi Phi by speedboat, a spa day and a romantic dinner.',
                        'featured' => true,
                        'days' => [
                            ['title' => 'Arrival in Krabi', 'body' => 'Arrive in Krabi (before 12 noon), greeted by the driver. Visit Buddha Hill, Krabi Town, Crab Statue & OTOP. Halal lunch, check-in at the hotel, and rest. Evening: candle light dinner at the hotel or a halal restaurant.', 'meals' => 'DINNER'],
                            ['title' => 'Phi Phi Islands', 'body' => 'Breakfast, full-day tour to Phi Phi Islands by speedboat. Activities include snorkeling, swimming, and visiting Maya Bay, Bamboo Island, Piley Bay, and Viking Cave. Halal lunch and dinner provided.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                            ['title' => 'Romantic dinner', 'body' => 'A relaxed day with spa treatment, followed by a romantic dinner on the beach.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                            ['title' => 'Departure from Krabi', 'body' => 'Free morning at the hotel before transfer to Krabi Airport for your departure flight.', 'meals' => 'BREAKFAST · LUNCH'],
                        ],
                    ],
                    [
                        'duration' => '3 DAYS · 2 NIGHTS',
                        'title' => '3D2N Krabi 7 Island',
                        'summary' => 'A short escape with a 7-island tour and night snorkeling.',
                        'featured' => false,
                        'days' => [
                            ['title' => 'Arrival in Krabi', 'body' => 'Arrive in Krabi and check in at the hotel. In the afternoon, enjoy a halal seafood buffet at Ton Talay. Evening free at Ao Nang – shopping, spa, or massage.', 'meals' => 'DINNER'],
                            ['title' => 'Krabi 7-island exploration', 'body' => 'Breakfast at the hotel. Depart for an island-hopping tour to 7 islands: Railay, Koh Yawasam, Tan Ming, Chicken Island, Tub & Mor Island. Halal BBQ at Poda Island while watching the sunset. Night snorkeling with glowing plankton. Return to the hotel.', 'meals' => 'BREAKFAST · DINNER'],
                            ['title' => 'Departure from Krabi', 'body' => 'Breakfast, check-out, and transfer to the airport. End of a short but experience-packed vacation.', 'meals' => 'BREAKFAST'],
                        ],
                    ],
                    [
                        'duration' => '4 DAYS · 3 NIGHTS',
                        'title' => '4D3N Krabi + Trang',
                        'summary' => 'Island tour by longtail boat plus a full-day Trang tour.',
                        'featured' => false,
                        'days' => [
                            ['title' => 'Arrival in Krabi', 'body' => 'Arrive at Krabi Airport. Visit the Crab Statue, local product centers, and enjoy the scenic view from Buddha Hill. Afterwards, check in at the hotel. Evening free for a leisurely stroll at Ao Nang Walking Street.', 'meals' => ''],
                            ['title' => '4-island tour – longtail boat', 'body' => 'Island tour – Tub Island, Chicken Island, Mo Island, Poda Island, and Railey Beach. Swimming, snorkeling, and exploring Princess Cave. Picnic-style lunch on the island. Return to the hotel with free time to rest or explore.', 'meals' => 'BREAKFAST · LUNCH'],
                            ['title' => 'Full-day Trang tour', 'body' => 'Visit the Elephant Cave (Tham Chang Hai), then lunch at a paddy field or a Muslim-friendly restaurant. Continue to a traditional silk weaving factory and a saltwater hot spring. End with shopping at a premium outlet before returning to the hotel.', 'meals' => 'BREAKFAST · LUNCH'],
                            ['title' => 'Departure from Krabi', 'body' => 'Final morning free at the hotel before transfer to Krabi Airport for your departure flight.', 'meals' => 'BREAKFAST'],
                        ],
                    ],
                ], $englishIncludes, $englishExcludes, [
                    'label' => 'KRABI ACCOMMODATION',
                    'name' => 'Aonang Princeville Krabi',
                    'address' => '164 Moo 2, Ao Nang, Mueang Krabi District, Krabi 81180, Thailand',
                    'phone' => '+66 75 637 971',
                ], 'Contact our team for pricing, travel dates and availability.'),
            ],
            'maldives' => [
                'name' => 'Maldives',
                'description' => 'Snorkeling, dolphin cruises and a beach candlelight dinner on Maafushi Island.',
                'package' => $package('Maldives', [
                    [
                        'duration' => '4 DAYS · 3 NIGHTS',
                        'title' => '4D3N Maldives Island Tour',
                        'summary' => 'Maafushi Island with snorkeling, a dolphin cruise and a romantic dinner.',
                        'featured' => true,
                        'days' => [
                            ['title' => 'Arrival in Maldives, Maafushi', 'body' => 'Arrive at Velana International Airport, meet the tour representative, and transfer to Maafushi by speedboat. Check in to the hotel and enjoy free time. Dinner will be served at the hotel.', 'meals' => 'DINNER'],
                            ['title' => 'Snorkeling & dolphin cruise', 'body' => 'Breakfast at the hotel, then proceed to the jetty for snorkeling at a sandbank. Picnic lunch will be provided. After that, enjoy a dolphin-watching cruise by boat. Return to the hotel. Dinner at own arrangement.', 'meals' => 'BREAKFAST · LUNCH'],
                            ['title' => 'Romantic dinner', 'body' => 'Breakfast at the hotel. Relax on the beach or join optional activities (at own expense). In the afternoon, join a night fishing trip. In the evening, enjoy a romantic candlelight dinner by the beach.', 'meals' => 'BREAKFAST · DINNER'],
                            ['title' => 'Departure from Maldives', 'body' => 'Breakfast and check-out from the hotel at 12:00 noon. Transfer by speedboat to Malé and proceed to the airport. End of the tour.', 'meals' => 'BREAKFAST'],
                        ],
                    ],
                    [
                        'duration' => '5 DAYS · 4 NIGHTS',
                        'title' => '5D4N Maldives Island Tour',
                        'summary' => 'An extra leisure day for optional tours and beach time.',
                        'featured' => false,
                        'days' => [
                            ['title' => 'Arrival in Maldives, Maafushi', 'body' => 'Arrive at Velana International Airport, meet the representative, and transfer to Maafushi by speedboat. Check in to the hotel and enjoy free time. Dinner will be provided at the hotel.', 'meals' => 'DINNER'],
                            ['title' => 'Snorkeling & dolphin cruise', 'body' => 'Breakfast at the hotel. Trip to a sandbank for snorkeling and swimming. Picnic-style lunch will be provided. Continue with dolphin sightseeing. Return to the hotel and enjoy free time.', 'meals' => 'BREAKFAST · LUNCH'],
                            ['title' => 'Romantic dinner', 'body' => 'Breakfast at the hotel. Free activities such as relaxing on the beach or joining optional tours. In the afternoon, enjoy a night fishing activity. Dinner at the hotel.', 'meals' => 'BREAKFAST · DINNER'],
                            ['title' => 'Maldives leisure day', 'body' => 'Breakfast at the hotel. Free time or join additional optional tours. Dinner at the hotel.', 'meals' => 'BREAKFAST · DINNER'],
                            ['title' => 'Departure from Maldives', 'body' => 'Breakfast at the hotel. Check-out at 12:00 noon. Transfer to the airport by speedboat. End of the program.', 'meals' => 'BREAKFAST'],
                        ],
                    ],
                ], $englishIncludes, $englishExcludes, [
                    'label' => 'ACCOMMODATION (MAAFUSHI ISLAND)',
                    'name' => 'Arena Beach Hotel',
                    'address' => 'Ziyaaraiy Magu Road, Maafushi 08090, Maldives',
                    'phone' => '+960 665-6000',
                ], 'Optional tours: Real Break Tour – RM 360 per person (snorkeling with friendly sharks, shipwreck visit, island hopping, lunch at a sandbank). Whale Shark Tour – RM 490 per person (snorkeling with whale sharks and manta rays).'),
            ],
            'lombok' => [
                'name' => 'Lombok',
                'description' => 'Gili Trawangan, Senaru waterfalls and a romantic beach dinner — a 4-day, 3-night honeymoon package.',
                'package' => $package('Lombok', [
                    [
                        'duration' => '4 DAYS · 3 NIGHTS',
                        'title' => '4D3N Lombok Honeymoon',
                        'summary' => 'Gili Trawangan, Senaru and a romantic dinner on the beach.',
                        'featured' => true,
                        'days' => [
                            ['title' => 'Arrival in Lombok', 'body' => 'Arrive in Lombok, greeted by your tour guide, check in at the hotel and rest.', 'meals' => 'DINNER'],
                            ['title' => 'Gili Trawangan', 'body' => 'Visit Villa Hantu and Malimbu Hill, then take a boat to Gili Trawangan. In the afternoon, stop at Pusuk Pass, have dinner at Tanjung Bias, and shop for souvenirs.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                            ['title' => 'Romantic dinner', 'body' => 'Visit Senaru Village, Sendang Gile & Tiu Kelep Waterfalls, Bayan Ancient Mosque and the Islamic Centre, enjoy a spa treatment, and finish with a romantic dinner on the beach.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                            ['title' => 'Departure from Lombok', 'body' => 'Visit Banyumulek, Sukarara Village, Sade Village, Kuta Beach & Merese Hill before heading to the airport for departure.', 'meals' => 'BREAKFAST · LUNCH'],
                        ],
                    ],
                    [
                        'duration' => '4 DAYS · 3 NIGHTS',
                        'title' => '4D3N Lombok Island',
                        'summary' => 'A full itinerary with Gili Trawangan, Senaru and traditional villages.',
                        'featured' => false,
                        'days' => [
                            ['title' => 'Arrival in Lombok', 'body' => 'Arrive at Lombok Airport and go straight to a dinner of local Lombok specialities. Then check in at the hotel and rest.', 'meals' => 'DINNER'],
                            ['title' => 'Gili Trawangan', 'body' => 'After breakfast, visit Villa Hantu and Malaka Hill for views of the 3 Gilis. Take a boat to Gili Trawangan for free activities (snorkeling, cycling, horse carriage at your own expense) and lunch on the island. In the afternoon, stop at Pusuk Pass to feed the monkeys, watch the sunset at Tanjung Bias Beach over grilled fish, and shop for souvenirs before returning to the hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                            ['title' => 'Sendang Gila Waterfall', 'body' => 'After breakfast, visit the traditional Senaru Village, Sendang Gila and Tiu Kelep Waterfalls for a swim and photos. Lunch in Senaru. Stop at Bayan Ancient Mosque and the Islamic Center Mataram. Dinner, then return to the hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                            ['title' => 'Departure from Lombok', 'body' => 'Visit Banyumulek (pottery), Sukarara (weaving) and Sade (traditional houses) villages, then Kuta Beach Lombok and Merese Hill. Lunch, then transfer to the airport for departure.', 'meals' => 'BREAKFAST · LUNCH'],
                        ],
                    ],
                ], [
                    'Transportation throughout the trip',
                    '3 nights hotel accommodation with breakfast',
                    'Private tours and entrance fees',
                    'Mineral water (1 bottle during the tour)',
                    'Full meals (breakfast, lunch, dinner)',
                ], [
                    'Airfare',
                    'Travel insurance',
                    'Visa (if required)',
                    'Peak season surcharge',
                    'Optional tours',
                    'Personal expenses such as laundry and souvenirs',
                ], [
                    'label' => 'LOMBOK ACCOMMODATION',
                    'name' => 'Merumatta Senggigi Lombok',
                    'address' => 'Jalan Pantai Senggigi, Senggigi, Kec. Batu Layar, Kabupaten Lombok Barat, Nusa Tenggara Bar. 83355, Indonesia',
                    'phone' => '+62 370 693210',
                ], 'Contact our team for pricing, travel dates and availability.'),
            ],
        ];
    }
}
