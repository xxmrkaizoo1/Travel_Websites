<?php

use App\Models\TourPackagePage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const INCLUSIONS = [
        'Private air-conditioned vehicle',
        '3-star accommodation (twin/double sharing)',
        'Halal meals (breakfast, lunch and dinner)',
        'Malay/English-speaking tour guide',
        'Entrance tickets to the attractions',
        'Mineral water daily',
    ];

    private const EXCLUSIONS = [
        'Flight tickets and airport taxes',
        'Personal expenses (laundry, drinks, etc.)',
        'Tips for the driver and tour guide',
    ];

    public function up(): void
    {
        foreach ($this->pages() as $slug => $definition) {
            $page = TourPackagePage::query()->where('slug', $slug)->first();

            if ($page && ($page->package_data['itineraries'][0]['title'] ?? '') !== 'Signature package') {
                continue;
            }

            $content = array_replace(
                TourPackagePage::starterContent(TourPackagePage::KIND_PACKAGE, $definition['name']),
                $definition['content'],
            );

            $page ??= new TourPackagePage;
            $page->fill($content);
            $page->slug = $slug;
            $page->parent_slug = 'vietnam';
            $page->is_custom = true;
            $page->save();
        }
    }

    public function down(): void
    {
        //
    }

    /**
     * @return array<string, array{name: string, content: array<string, mixed>}>
     */
    private function pages(): array
    {
        $whatsapp = fn (string $name): string => 'https://wa.me/60165500590?text='.rawurlencode("Hi, I'd like to ask about the {$name} private tour package.");

        $package = fn (string $name, string $headline, string $body, array $itinerary, array $hotels): array => [
            'itineraries_eyebrow' => "EXPLORE {$name}",
            'itineraries_headline' => $headline,
            'itineraries_body' => $body,
            'itineraries' => [$itinerary + [
                'featured' => true,
                'cta_label' => 'Enquire about this package',
                'cta_url' => $whatsapp($itinerary['title']),
            ]],
            'gallery_eyebrow' => 'DESTINATION PHOTOS',
            'gallery_headline' => "{$name} gallery",
            'gallery_body' => 'Photos for this destination will be added soon.',
            'gallery' => [],
            'details_eyebrow' => 'GOOD TO KNOW',
            'details_headline' => "What's included in your {$name} tour.",
            'details_body' => 'Package inclusions and exclusions are listed below.',
            'inclusions' => self::INCLUSIONS,
            'exclusions' => self::EXCLUSIONS,
            'accommodation_eyebrow' => 'YOUR STAY',
            'accommodation_headline' => "Where you will stay in {$name}.",
            'accommodation_body' => 'Hotels are subject to availability at the time of booking.',
            'hotels' => $hotels,
        ];

        return [
            'da-nang' => [
                'name' => 'Da Nang',
                'content' => [
                    'meta_title' => 'Da Nang, Hoi An & Ba Na Hills Tour Package | Smart Muslim by Roystar Travel & Tours',
                    'meta_description' => 'A 5D4N private tour of Central Vietnam: Da Nang, Hoi An Ancient Town and the Golden Bridge at Ba Na Hills.',
                    'hero_eyebrow' => 'CENTRAL VIETNAM',
                    'hero_headline' => 'Discover Da Nang.',
                    'hero_highlight' => 'Golden Bridge & Hoi An.',
                    'hero_body' => 'Cross the Golden Bridge at Ba Na Hills, drift through a coconut village by basket boat and stroll the lantern-lit streets of Hoi An.',
                    'hero_note' => 'DA NANG · HOI AN · BA NA HILLS',
                    'intro_eyebrow' => 'WELCOME TO CENTRAL VIETNAM',
                    'intro_headline' => 'Coastlines, heritage and',
                    'intro_highlight' => 'golden views.',
                    'intro_body' => 'From the Son Tra Peninsula and Marble Mountain to Hoi An Ancient Town and the hilltop theme park of Ba Na Hills, Central Vietnam blends nature, culture and fun.',
                    'intro_image_alt' => 'Da Nang and Central Vietnam',
                    'intro_image_caption' => 'Central Vietnam, planned around you.',
                    'highlights_eyebrow' => 'DA NANG · HOI AN · BA NA HILLS',
                    'highlights_headline' => 'A memorable journey through Central Vietnam.',
                    'highlights_body' => 'One private itinerary that brings the region’s best-loved places together.',
                    'highlights' => [
                        ['title' => 'Ba Na Hills & Golden Bridge', 'body' => 'Ride one of the world’s longest cable cars to the famous Golden Bridge held by giant stone hands.'],
                        ['title' => 'Hoi An Ancient Town', 'body' => 'Visit the Japanese Bridge, Tan Ky Old House and the market by the Thu Bon River.'],
                        ['title' => 'Coconut Village', 'body' => 'Paddle a bamboo basket boat through the water coconut groves of Cam Thanh.'],
                    ],
                    'contact_headline' => 'Plan your Da Nang getaway.',
                    'contact_body' => 'Share your preferred travel dates with our team.',
                    'contact_href' => $whatsapp('Da Nang'),
                    'package_data' => $package('DA NANG', 'Da Nang, Hoi An & Ba Na Hills.', 'A private Central Vietnam itinerary with Halal meals throughout.', [
                        'duration' => '5 DAYS · 4 NIGHTS',
                        'title' => 'Da Nang – Hoi An – Ba Na Hills (5D4N)',
                        'summary' => 'Golden Bridge, Hoi An Ancient Town and the coconut village of Cam Thanh.',
                        'days' => [
                            ['title' => 'Arrival in Da Nang', 'body' => 'Upon arrival at Da Nang International Airport, you will be welcomed by our representative and taken for lunch at a local restaurant. After hotel check-in and a short rest, enjoy an afternoon tour of the Son Tra Peninsula with its stunning coastal and nature views. Then visit Linh Ung Pagoda, home to the tallest Lady Buddha statue in Vietnam, and Marble Mountain, a marble hill filled with caves, temples and spiritual history. After dinner, return to the hotel for an overnight stay in Da Nang.', 'meals' => 'LUNCH · DINNER'],
                            ['title' => 'Coconut Village – Hoi An', 'body' => 'After breakfast, travel to Cam Thanh Coconut Village for a bamboo basket boat ride through the water coconut groves while watching the daily life of local villagers. Lunch is served at a Halal restaurant. In the afternoon, visit Hoi An Ancient Town: Tan Ky Old House, the iconic Japanese Bridge, the Phuc Kien Assembly Hall and Hoi An Market by the Thu Bon River. After dinner, return to Da Nang for an overnight stay.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                            ['title' => 'Ba Na Hills Full-Day Tour', 'body' => 'After breakfast, head to Ba Na Hills, a famous hilltop destination with cool weather and spectacular views. Ride the world’s longest cable car and walk across the Golden Bridge, supported by giant stone hands. Visit the French Village, which recreates classic European scenery, and enjoy the Fantasy Park and the Wax Museum. Lunch and dinner are served at Ba Na Hills before returning to the hotel for an overnight stay in Da Nang.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                            ['title' => 'Shopping and departure', 'body' => 'After breakfast and hotel check-out, visit Han Market to shop for local products and souvenirs. Afterwards, transfer to the airport for your flight home. Your holiday ends with beautiful memories of Da Nang and Hoi An.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                        ],
                    ], [
                        ['label' => 'Da Nang stay', 'name' => 'Sea Front Hotel', 'address' => '240 Vo Nguyen Giap, Phuoc My, Son Tra, Da Nang 550000, Vietnam', 'phone' => '+84 236 3943 768'],
                    ]),
                ],
            ],
            'hanoi' => [
                'name' => 'Hanoi',
                'content' => [
                    'meta_title' => 'Hanoi, Sapa & Halong Bay Tour Package | Smart Muslim by Roystar Travel & Tours',
                    'meta_description' => 'A 5D4N private tour of Northern Vietnam: Hanoi, Sapa with the Fansipan cable car, and a Halong Bay cruise.',
                    'hero_eyebrow' => 'NORTHERN VIETNAM',
                    'hero_headline' => 'Discover Hanoi.',
                    'hero_highlight' => 'Sapa & Halong Bay.',
                    'hero_body' => 'Wind through terraced rice fields to Sapa, ride the Fansipan cable car and cruise among the limestone islands of Halong Bay.',
                    'hero_note' => 'HANOI · SAPA · HALONG BAY',
                    'intro_eyebrow' => 'WELCOME TO NORTHERN VIETNAM',
                    'intro_headline' => 'Mountains, bays and',
                    'intro_highlight' => 'old-world charm.',
                    'intro_body' => 'From the misty mountains of Sapa and the Hmong village of Cat Cat to the Old Quarter of Hanoi and the emerald waters of Halong Bay, Northern Vietnam is full of variety.',
                    'intro_image_alt' => 'Northern Vietnam landscape',
                    'intro_image_caption' => 'Northern Vietnam, planned around you.',
                    'highlights_eyebrow' => 'HANOI · SAPA · HALONG BAY',
                    'highlights_headline' => 'A memorable journey through Northern Vietnam.',
                    'highlights_body' => 'One private itinerary that brings the north’s best-loved places together.',
                    'highlights' => [
                        ['title' => 'Sapa & Fansipan Legend', 'body' => 'Take the cable car to the summit of Fansipan, the roof of Indochina.'],
                        ['title' => 'Halong Bay cruise', 'body' => 'Sail past Cock Fighting Islet and Thumb Islet, then explore Surprising Cave and Luon Cave.'],
                        ['title' => 'Hanoi Old Quarter', 'body' => 'Visit Tran Quoc Pagoda, Hoan Kiem Lake and shop at Dong Xuan Market.'],
                    ],
                    'contact_headline' => 'Plan your Hanoi getaway.',
                    'contact_body' => 'Share your preferred travel dates with our team.',
                    'contact_href' => $whatsapp('Hanoi'),
                    'package_data' => $package('HANOI', 'Hanoi, Sapa & Halong Bay.', 'A private Northern Vietnam itinerary with Halal meals throughout.', [
                        'duration' => '5 DAYS · 4 NIGHTS',
                        'title' => 'Hanoi – Sapa – Halong (5D4N)',
                        'summary' => 'Sapa’s terraced fields, the Fansipan cable car and a Halong Bay cruise.',
                        'days' => [
                            ['title' => 'Arrival in Hanoi – Sapa', 'body' => 'Arrive in Hanoi, meet your guide and travel straight to Sapa (about 5 hours), enjoying views of beautiful terraced rice fields along the way. On arrival, visit Cat Cat Village, a village of the Hmong ethnic group. Dinner and hotel check-in. Overnight in Sapa at Bora Hotel.', 'meals' => 'DINNER'],
                            ['title' => 'Sapa – Fansipan Legend – Sapa', 'body' => 'After breakfast at the hotel, ride the Fansipan Legend cable car, one of the longest cable car systems in the world, to the Roof of Indochina – Mount Fansipan. Enjoy the mountain scenery and lunch. Return to the hotel for dinner and rest. Overnight in Sapa at Bora Hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                            ['title' => 'Sapa – Hanoi', 'body' => 'After breakfast, travel back to Hanoi. The afternoon tour covers Tran Quoc Pagoda, Hoan Kiem Lake and the Old Quarter, with free time to shop at Dong Xuan Market. Dinner at a local restaurant. Overnight in Hanoi at Diamond King Hotel or Lakeside Hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                            ['title' => 'Hanoi – Halong Bay – Hanoi', 'body' => 'After breakfast, travel to Tuan Chau Port to board a Halong Bay cruise. Enjoy lunch on board while admiring unique limestone formations such as Cock Fighting Islet and Thumb Islet. Visit Surprising Cave, go kayaking or take a bamboo boat at Luon Cave, and climb Titov Island for a panoramic view of Halong Bay. In the afternoon, return to shore and drive back to Hanoi. Overnight in Hanoi at Diamond King Hotel or Lakeside Hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                            ['title' => 'Departure from Hanoi', 'body' => 'Breakfast at the hotel. Enjoy free time before heading to the airport for your flight back to Malaysia.', 'meals' => 'BREAKFAST'],
                        ],
                    ], [
                        ['label' => 'Hanoi stay', 'name' => 'Diamond King Hotel & Restaurant', 'address' => '86 Ma May, Hang Buom, Hoan Kiem, Hanoi 110000, Vietnam', 'phone' => '+84 24 3828 2282'],
                        ['label' => 'Sapa stay', 'name' => 'Bora Hotel', 'address' => '54B Cau May Street, Sa Pa Town, Sa Pa, Lao Cai 33000, Vietnam', 'phone' => '+84 214 3871 313'],
                    ]),
                ],
            ],
        ];
    }
};
