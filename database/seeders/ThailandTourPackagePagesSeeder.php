<?php

namespace Database\Seeders;

use App\Models\TourPackagePage;
use Illuminate\Database\Seeder;

class ThailandTourPackagePagesSeeder extends Seeder
{
    private const INCLUSIONS = [
        'Transportation throughout the trip',
        'Hotel accommodation with breakfast',
        'Private tours and entrance tickets',
        'Mineral water (1 bottle during tours)',
        'Full meals (breakfast, lunch and dinner)',
    ];

    private const EXCLUSIONS = [
        'Airfare',
        'Travel insurance',
        'Visa (if required)',
        'Peak season surcharge',
        'Optional tours and activities',
        'Personal expenses such as laundry and souvenirs',
    ];

    /**
     * @param  list<string>  $slugs  Only these pages are added; empty adds them all.
     */
    public function run(array $slugs = []): void
    {
        $pages = $slugs === [] ? $this->pages() : array_intersect_key($this->pages(), array_flip($slugs));

        foreach ($pages as $slug => $definition) {
            if (TourPackagePage::query()->where('slug', $slug)->exists()) {
                continue;
            }

            $content = array_replace(
                TourPackagePage::starterContent(TourPackagePage::KIND_PACKAGE, $definition['name']),
                $definition['content'],
            );
            $page = new TourPackagePage($content);
            $page->slug = $slug;
            $page->parent_slug = 'thailand';
            $page->is_custom = true;
            $page->save();
        }
    }

    /**
     * @return array<string, array{name: string, content: array<string, mixed>}>
     */
    private function pages(): array
    {
        $whatsapp = fn (string $name): string => 'https://wa.me/60165500590?text='.rawurlencode("Hi, I'd like to ask about the {$name} private tour package.");

        $photo = fn (string $id, int $width = 1800): string => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w={$width}&q=85";

        $package = fn (string $name, string $headline, string $body, array $itineraries, array $hotels, array $gallery = []): array => [
            'itineraries_eyebrow' => "EXPLORE {$name}",
            'itineraries_headline' => $headline,
            'itineraries_body' => $body,
            'itineraries' => array_map(
                fn (array $itinerary): array => $itinerary + [
                    'featured' => false,
                    'cta_label' => 'Enquire about this package',
                    'cta_url' => $whatsapp($itinerary['title']),
                ],
                $itineraries,
            ),
            'gallery_eyebrow' => 'DESTINATION PHOTOS',
            'gallery_headline' => "{$name} gallery",
            'gallery_body' => $gallery === [] ? 'Photos for this destination will be added soon.' : 'A glimpse of the places on your itinerary.',
            'gallery' => $gallery,
            'details_eyebrow' => 'GOOD TO KNOW',
            'details_headline' => "What's included in your {$name} tour.",
            'details_body' => 'Package inclusions and exclusions are listed below.',
            'inclusions' => self::INCLUSIONS,
            'exclusions' => self::EXCLUSIONS,
            'accommodation_eyebrow' => 'YOUR STAY',
            'accommodation_headline' => "Where you will stay on your {$name} tour.",
            'accommodation_body' => 'Hotels are subject to availability at the time of booking.',
            'hotels' => $hotels,
        ];

        return [
            'bangkok' => [
                'name' => 'Bangkok',
                'content' => [
                    'meta_title' => 'Bangkok Tour Packages | Smart Muslim by Roystar Travel & Tours',
                    'meta_description' => 'Explore Bangkok, its historic landmarks, river, markets and shopping districts on a private 4D3N tour.',
                    'hero_eyebrow' => 'BANGKOK · THAILAND',
                    'hero_headline' => 'Discover Bangkok.',
                    'hero_highlight' => 'Culture, river & city life.',
                    'hero_body' => 'Explore Bangkok’s historic landmarks, lively markets and riverside attractions with a private 4D3N itinerary.',
                    'hero_note' => 'CITY · CULTURE · SHOPPING',
                    'hero_image' => $photo('1747235156815-3b39a7097b7b', 2000),
                    'intro_eyebrow' => 'WELCOME TO BANGKOK',
                    'intro_headline' => 'Experience the energy of',
                    'intro_highlight' => 'Thailand’s capital.',
                    'intro_body' => 'Discover the city’s contrasting character, from Chinatown and historic palaces to a Chao Phraya River boat ride, modern malls and colourful local markets.',
                    'intro_image' => 'https://images.unsplash.com/photo-1508009603885-50cf7c579365?auto=format&fit=crop&w=1000&q=85',
                    'intro_image_alt' => 'Neon signs and tuk-tuks on a Bangkok street at night',
                    'intro_image_caption' => 'A city journey at your own pace.',
                    'highlights_eyebrow' => 'LANDMARKS · RIVER · MARKETS',
                    'highlights_headline' => 'See the many sides of Bangkok.',
                    'highlights_body' => 'Combine landmark visits, river scenery and time to shop.',
                    'highlights' => [
                        ['title' => 'Historic Bangkok', 'body' => 'See Chinatown, the Flower Market and the Grand Palace from outside.'],
                        ['title' => 'Chao Phraya River', 'body' => 'Cruise by boat to ICONSIAM and enjoy time by the river.'],
                        ['title' => 'Markets & shopping', 'body' => 'Visit Mae Klong Railway Market, Damnoen Saduak Floating Market and Bangkok malls.'],
                    ],
                    'contact_headline' => 'Plan your Bangkok city break.',
                    'contact_body' => 'Share your travel dates and our team will help plan your private Bangkok tour.',
                    'contact_href' => $whatsapp('Bangkok'),
                    'package_data' => $package('BANGKOK', 'Explore Bangkok in four days.', 'A private 4D3N city itinerary with local markets, historic sights, a river boat ride and shopping.', [
                        [
                            'duration' => '4 DAYS · 3 NIGHTS',
                            'title' => 'Bangkok 4D3N',
                            'summary' => 'A four-day Bangkok city tour with markets, river scenery and shopping.',
                            'days' => [
                                ['title' => 'Arrival in Bangkok', 'body' => 'Meet your guide on arrival in Bangkok. Visit Erawadee herbal products and shop at Platinum Mall. In the afternoon, explore Asiatique Riverfront. Enjoy a Halal dinner, then check in at the hotel.', 'meals' => 'DINNER'],
                                ['title' => 'Bangkok city tour', 'body' => 'After breakfast, explore Chinatown, the Flower Market, the Grand Palace (viewed from outside) and Ananta Samakhom Palace. Stop at Gems Gallery and enjoy a Halal lunch. In the afternoon, take a boat along the Chao Phraya River to ICONSIAM for shopping. Halal dinner and return to the hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Markets & shopping', 'body' => 'Depart early for Mae Klong Railway Market and Damnoen Saduak Floating Market, including a boat ride. Enjoy a Halal lunch, visit a honey farm and shop at MBK Mall. Halal dinner and overnight at the hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Departure from Bangkok', 'body' => 'Breakfast at the hotel and free time. Check out and transfer to the airport for your departure flight.', 'meals' => 'BREAKFAST'],
                            ],
                        ],
                    ], [
                        ['label' => 'Bangkok', 'name' => 'Al Meroz Hotel Bangkok', 'address' => '4 Ramkhamhaeng 5 Alley, Suan Luang, Bangkok 10250, Thailand', 'phone' => '+66 2 136 8700'],
                    ], [
                        ['image' => $photo('1747235156815-3b39a7097b7b'), 'alt' => 'Wat Arun temple rising towards the sky', 'eyebrow' => 'TEMPLE OF DAWN', 'title' => 'Wat Arun'],
                        ['image' => $photo('1591357820463-55f8f8d7d483'), 'alt' => 'Visitors among the porcelain-tiled spires of Wat Arun', 'eyebrow' => 'UP CLOSE', 'title' => 'Wat Arun’s spires'],
                        ['image' => $photo('1568321385520-b9252021b491'), 'alt' => 'White and gold temple spires in Bangkok', 'eyebrow' => 'BANGKOK', 'title' => 'Temple quarter'],
                        ['image' => $photo('1508009603885-50cf7c579365'), 'alt' => 'Neon signs and tuk-tuks on a Bangkok street at night', 'eyebrow' => 'AFTER DARK', 'title' => 'Bangkok by night'],
                    ]),
                ],
            ],
            'pattaya' => [
                'name' => 'Pattaya',
                'content' => [
                    'meta_title' => 'Bangkok & Pattaya Tour Package | Smart Muslim by Roystar Travel & Tours',
                    'meta_description' => 'Enjoy a private 4D3N Bangkok and Pattaya tour with Nong Nooch Tropical Garden, city sights, river cruise and shopping.',
                    'hero_eyebrow' => 'BANGKOK & PATTAYA · THAILAND',
                    'hero_headline' => 'Bangkok to Pattaya.',
                    'hero_highlight' => 'A city and coast escape.',
                    'hero_body' => 'Pair Bangkok’s city landmarks and shopping with time to unwind on Pattaya Beach on this private 4D3N tour.',
                    'hero_note' => 'BANGKOK · PATTAYA · PRIVATE TOUR',
                    'hero_image' => $photo('1620373901514-4319a7c81cdb', 2000),
                    'intro_eyebrow' => 'WELCOME TO BANGKOK & PATTAYA',
                    'intro_headline' => 'From the city to',
                    'intro_highlight' => 'the coast.',
                    'intro_body' => 'Enjoy Pattaya Beach and Nong Nooch Tropical Garden, then return to Bangkok for heritage sights, a Chao Phraya boat ride and shopping.',
                    'intro_image' => $photo('1475645486310-a1e31c4cbfee', 1000),
                    'intro_image_alt' => 'Pier and blue sea at Koh Larn near Pattaya',
                    'intro_image_caption' => 'A relaxed city and coast escape.',
                    'highlights_eyebrow' => 'CITY · GARDENS · COAST',
                    'highlights_headline' => 'Two destinations, one easy journey.',
                    'highlights_body' => 'Balance Bangkok’s landmarks with Pattaya’s beach and garden attractions.',
                    'highlights' => [
                        ['title' => 'Pattaya Beach', 'body' => 'Relax by the beach and enjoy time for photographs after arriving in Thailand.'],
                        ['title' => 'Nong Nooch Tropical Garden', 'body' => 'Explore the tropical garden and its cultural attractions.'],
                        ['title' => 'Bangkok by river', 'body' => 'See city landmarks and take a Chao Phraya boat ride to ICONSIAM.'],
                    ],
                    'contact_headline' => 'Plan your Bangkok and Pattaya getaway.',
                    'contact_body' => 'Contact our team to enquire about the private 4D3N tour and travel dates.',
                    'contact_href' => $whatsapp('Bangkok and Pattaya'),
                    'package_data' => $package('BANGKOK & PATTAYA', 'Explore Bangkok and Pattaya.', 'A private 4D3N tour combining Pattaya, Bangkok landmarks, river scenery and shopping.', [
                        [
                            'duration' => '4 DAYS · 3 NIGHTS',
                            'title' => 'Bangkok · Pattaya 4D3N',
                            'summary' => 'A four-day city and coast tour featuring Pattaya, Bangkok and the Chao Phraya River.',
                            'days' => [
                                ['title' => 'Kuala Lumpur · Bangkok · Pattaya', 'body' => 'Arrive in Bangkok and meet your guide. Enjoy a Halal lunch before travelling to Pattaya. Relax at Pattaya Beach and take photographs. Have a Halal dinner, check in at the hotel and rest.', 'meals' => 'LUNCH · DINNER'],
                                ['title' => 'Pattaya · Bangkok', 'body' => 'After breakfast, visit Nong Nooch Tropical Garden to see its cultural attractions. Enjoy a Halal lunch, then stop at a honey farm, Erawadee Herbal Shop and Great & Grand Sweet Destination. Return to Bangkok for a Halal dinner and overnight stay.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Bangkok city tour', 'body' => 'After breakfast, explore Bangkok including Chinatown and the Grand Palace. Visit Gems Gallery, then enjoy a Halal lunch. Take a relaxing boat ride along the Chao Phraya River to ICONSIAM for shopping. An optional dinner cruise is available. Halal dinner and return to the hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Departure from Bangkok', 'body' => 'Enjoy breakfast and free time for shopping or relaxing. Check out and transfer to the airport for your return flight.', 'meals' => 'BREAKFAST'],
                            ],
                        ],
                    ], [
                        ['label' => 'Bangkok', 'name' => 'Al Meroz Hotel Bangkok', 'address' => '4 Ramkhamhaeng 5 Alley, Suan Luang, Bangkok 10250, Thailand', 'phone' => '+66 2 136 8700'],
                        ['label' => 'Pattaya', 'name' => 'Centre Point Prime Hotel Pattaya', 'address' => '275 Sukhumvit Road, Muang Pattaya, Bang Lamung District, Chon Buri 20150, Thailand', 'phone' => '+66 38 186 888'],
                    ], [
                        ['image' => $photo('1620373901514-4319a7c81cdb'), 'alt' => 'Pattaya Beach lined with hotels and visitors', 'eyebrow' => 'THE GULF COAST', 'title' => 'Pattaya Beach'],
                        ['image' => $photo('1475645486310-a1e31c4cbfee'), 'alt' => 'Pier and blue sea at Koh Larn near Pattaya', 'eyebrow' => 'ISLAND DAY TRIP', 'title' => 'Koh Larn'],
                        ['image' => $photo('1747235156815-3b39a7097b7b'), 'alt' => 'Wat Arun temple in Bangkok', 'eyebrow' => 'BANGKOK', 'title' => 'Wat Arun'],
                        ['image' => $photo('1508009603885-50cf7c579365'), 'alt' => 'Bangkok street with neon signs at night', 'eyebrow' => 'CITY NIGHTS', 'title' => 'Bangkok by night'],
                    ]),
                ],
            ],
            'kanchanaburi' => [
                'name' => 'Kanchanaburi',
                'content' => [
                    'meta_title' => 'Bangkok & Kanchanaburi Tour Package | Smart Muslim by Roystar Travel & Tours',
                    'meta_description' => 'Explore Kanchanaburi Skywalk, the Bridge over the River Kwai, safari attractions and Bangkok markets on a private 4D3N tour.',
                    'hero_eyebrow' => 'BANGKOK & KANCHANABURI · THAILAND',
                    'hero_headline' => 'Discover Kanchanaburi.',
                    'hero_highlight' => 'River, nature & city.',
                    'hero_body' => 'Travel from Bangkok to Kanchanaburi for river landmarks and safari attractions, then return to the capital for markets and shopping.',
                    'hero_note' => 'KANCHANABURI · RIVER KWAI · BANGKOK',
                    'hero_image' => $photo('1624807177672-468d85ff1803', 2000),
                    'intro_eyebrow' => 'WELCOME TO KANCHANABURI',
                    'intro_headline' => 'A journey beyond',
                    'intro_highlight' => 'Bangkok.',
                    'intro_body' => 'Discover Kanchanaburi’s river scenery and local landmarks, with a safari experience and Bangkok’s famous railway and floating markets.',
                    'intro_image' => $photo('1624807177672-468d85ff1803', 1000),
                    'intro_image_alt' => 'Railway line running beside the River Kwai',
                    'intro_image_caption' => 'Explore more of Thailand on a private tour.',
                    'highlights_eyebrow' => 'RIVER · SAFARI · MARKETS',
                    'highlights_headline' => 'A varied Thailand adventure.',
                    'highlights_body' => 'Combine Kanchanaburi sightseeing with a day exploring Bangkok’s markets.',
                    'highlights' => [
                        ['title' => 'Kanchanaburi Skywalk', 'body' => 'Enjoy a visit to the Skywalk after travelling from Bangkok.'],
                        ['title' => 'River Kwai & safari', 'body' => 'See the Bridge over the River Kwai and visit Safari Open Zoo.'],
                        ['title' => 'Bangkok markets', 'body' => 'Visit Mae Klong Railway Market and Damnoen Saduak Floating Market by boat.'],
                    ],
                    'contact_headline' => 'Plan your Kanchanaburi tour.',
                    'contact_body' => 'Ask our team about this private 4D3N Bangkok and Kanchanaburi itinerary.',
                    'contact_href' => $whatsapp('Kanchanaburi'),
                    'package_data' => $package('KANCHANABURI', 'Explore Kanchanaburi and Bangkok.', 'A private 4D3N itinerary with river landmarks, safari attractions and Bangkok markets.', [
                        [
                            'duration' => '4 DAYS · 3 NIGHTS',
                            'title' => 'Bangkok · Kanchanaburi 4D3N',
                            'summary' => 'A four-day tour featuring Kanchanaburi, the River Kwai and Bangkok markets.',
                            'days' => [
                                ['title' => 'Kuala Lumpur · Bangkok · Kanchanaburi', 'body' => 'Arrive at Bangkok Airport and meet your guide. Enjoy lunch at a Halal restaurant, then travel approximately three hours to Kanchanaburi. Visit Kanchanaburi Skywalk, check in at the hotel and have dinner at a Halal restaurant.', 'meals' => 'LUNCH · DINNER'],
                                ['title' => 'Kanchanaburi · Bangkok', 'body' => 'After breakfast, visit the Bridge over the River Kwai. Continue to Safari Open Zoo for a safari bus ride and animal shows. Enjoy a Halal lunch and stop at Tomi Edo Village before returning to Bangkok. Dinner and overnight in Bangkok.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Bangkok markets & shopping', 'body' => 'After breakfast, visit Mae Klong Railway Market and Damnoen Saduak Floating Market, including a boat ride. Enjoy a Halal lunch, then visit a honey farm and Erawadee Herbal Shop. Shop at MBK or Platinum Mall. Dinner and overnight at the hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Departure from Bangkok', 'body' => 'Breakfast at the hotel and free time. After check-out, transfer to the airport for your return flight.', 'meals' => 'BREAKFAST'],
                            ],
                        ],
                    ], [
                        ['label' => 'Bangkok', 'name' => 'Al Meroz Hotel Bangkok', 'address' => '4 Ramkhamhaeng 5 Alley, Suan Luang, Bangkok 10250, Thailand', 'phone' => '+66 2 136 8700'],
                        ['label' => 'Kanchanaburi', 'name' => 'Royal River Khwae Resort', 'address' => '88 Moo 2, Kaeng Sian, Mueang Kanchanaburi District, Kanchanaburi 71000, Thailand', 'phone' => '+66 34 670 621'],
                    ], [
                        ['image' => $photo('1624807177672-468d85ff1803'), 'alt' => 'Railway line running beside the River Kwai', 'eyebrow' => 'HISTORY BY THE RIVER', 'title' => 'River Kwai railway'],
                        ['image' => $photo('1591357820463-55f8f8d7d483'), 'alt' => 'Porcelain-tiled spires of Wat Arun in Bangkok', 'eyebrow' => 'BANGKOK', 'title' => 'Wat Arun'],
                        ['image' => $photo('1508009603885-50cf7c579365'), 'alt' => 'Bangkok street market lights at night', 'eyebrow' => 'MARKETS & SHOPPING', 'title' => 'Bangkok by night'],
                    ]),
                ],
            ],
            'chiang-mai-chiang-rai' => [
                'name' => 'Chiang Mai & Chiang Rai',
                'content' => [
                    'meta_title' => 'Chiang Mai & Chiang Rai Tour Packages | Smart Muslim by Roystar Travel & Tours',
                    'meta_description' => 'Explore Chiang Mai and Chiang Rai on private 4D3N and 5D4N Northern Thailand tours.',
                    'hero_eyebrow' => 'NORTHERN THAILAND',
                    'hero_headline' => 'Discover Chiang Mai & Chiang Rai.',
                    'hero_highlight' => 'Mountains, culture & temples.',
                    'hero_body' => 'Travel through Northern Thailand for local crafts, mountain scenery, night markets and visits to Chiang Rai.',
                    'hero_note' => 'CHIANG MAI · CHIANG RAI · NORTHERN THAILAND',
                    'hero_image' => 'https://images.unsplash.com/photo-1528181304800-259b08848526?auto=format&fit=crop&w=2000&q=90',
                    'intro_eyebrow' => 'WELCOME TO NORTHERN THAILAND',
                    'intro_headline' => 'Explore the character of',
                    'intro_highlight' => 'the north.',
                    'intro_body' => 'Discover Chiang Mai’s historic streets and craft villages, travel to Chiang Rai through the northern highlands and explore tea country, markets and the Golden Triangle.',
                    'intro_image' => 'https://images.unsplash.com/photo-1528181304800-259b08848526?auto=format&fit=crop&w=1000&q=85',
                    'intro_image_alt' => 'Green mountain scenery in Northern Thailand',
                    'intro_image_caption' => 'A private journey through Northern Thailand.',
                    'highlights_eyebrow' => 'CULTURE · MOUNTAINS · MARKETS',
                    'highlights_headline' => 'A deeper look at Northern Thailand.',
                    'highlights_body' => 'Discover craft villages, Chiang Rai landmarks and northern landscapes.',
                    'highlights' => [
                        ['title' => 'Chiang Mai culture', 'body' => 'Visit local landmarks, craft villages and the Chiang Mai Night Bazaar.'],
                        ['title' => 'Chiang Rai & tea country', 'body' => 'Explore Singha Park and Chui Fong Tea Plantation, with the White Temple as an optional visit.'],
                        ['title' => 'Golden Triangle', 'body' => 'Visit Mae Sai and the Golden Triangle, where Thailand, Myanmar and Laos meet.'],
                    ],
                    'contact_headline' => 'Plan your Northern Thailand journey.',
                    'contact_body' => 'Ask our team about the 4D3N or 5D4N Chiang Mai and Chiang Rai itineraries.',
                    'contact_href' => $whatsapp('Chiang Mai and Chiang Rai'),
                    'package_data' => $package('CHIANG MAI & CHIANG RAI', 'Choose your northern Thailand itinerary.', 'Private 4D3N and 5D4N routes featuring Chiang Mai, Chiang Rai, local crafts and mountain scenery.', [
                        [
                            'duration' => '4 DAYS · 3 NIGHTS',
                            'title' => 'Chiang Mai · Chiang Rai 4D3N',
                            'summary' => 'A four-day journey through Chiang Mai, Chiang Rai and the Golden Triangle.',
                            'days' => [
                                ['title' => 'Arrival in Chiang Mai', 'body' => 'Arrive at Chiang Mai Airport and meet your Muslim-friendly guide. Visit local craft attractions including Home Industries, Bo Sang Umbrella Village and Thai Silk Village. After hotel check-in, enjoy a Halal Khantoke dinner with cultural performances. Overnight in Chiang Mai.', 'meals' => 'DINNER'],
                                ['title' => 'Chiang Mai · Chiang Rai', 'body' => 'After breakfast and check-out, travel approximately four hours to Chiang Rai, stopping at Mae Khajan Hot Springs. Enjoy a Halal lunch and visit Singha Park, including a farm tour by shuttle bus. The White Temple is an optional visit. Shop at Chiang Rai Night Bazaar and stay overnight in Chiang Rai.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Chiang Rai · Chiang Mai', 'body' => 'After breakfast and check-out, visit Mae Sai and the Golden Triangle, where Thailand, Myanmar and Laos meet. A boat trip to the Laos border is optional. Visit a Karen hill-tribe village, then return to Chiang Mai for a Halal dinner and shopping at the Chiang Mai Night Bazaar. Overnight in Chiang Mai.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Departure from Chiang Mai', 'body' => 'After breakfast, enjoy free time to relax or buy souvenirs. Check out and transfer to the airport for your return flight.', 'meals' => 'BREAKFAST'],
                            ],
                        ],
                        [
                            'duration' => '5 DAYS · 4 NIGHTS',
                            'title' => 'Chiang Mai · Chiang Rai 5D4N',
                            'summary' => 'An extended northern tour with extra Chiang Mai sightseeing and craft villages.',
                            'days' => [
                                ['title' => 'Arrival in Chiang Mai', 'body' => 'Arrive in Chiang Mai and visit Banhaw Mosque, Tha Phae Gate, Warorot Market and One Nimman. Enjoy a Halal Khantoke dinner with a cultural performance. Overnight in Chiang Mai.', 'meals' => 'DINNER'],
                                ['title' => 'Chiang Mai · Chiang Rai', 'body' => 'Travel approximately four hours to Chiang Rai, stopping at Mae Khajan Hot Springs. Visit Singha Park, including the shuttle farm tour. The White Temple is optional. Shop at the Night Bazaar and stay overnight in Chiang Rai.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Chiang Rai · Chiang Mai', 'body' => 'Visit Chui Fong Tea Plantation, Mae Sai border and the Golden Triangle. A boat trip to the Laos border is optional. Return to Chiang Mai and enjoy shopping at the Night Bazaar. Overnight in Chiang Mai.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Chiang Mai nature & craft villages', 'body' => 'Visit a flower farm and Mae Taeng Elephant Camp, including the show, elephant ride and Karen village, with a Halal lunch. Continue to craft villages including Bo Sang, Home Industries and Thai Silk Village. Chiang Mai Night Safari is optional. Overnight in Chiang Mai.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Departure from Chiang Mai', 'body' => 'Enjoy breakfast and free time before hotel check-out and transfer to the airport for your return flight.', 'meals' => 'BREAKFAST'],
                            ],
                        ],
                    ], [
                        ['label' => 'Chiang Mai', 'name' => 'Duangtawan Hotel Chiang Mai', 'address' => '132 Loi Kroh Road, Tambon Chang Khlan, Mueang Chiang Mai District, Chiang Mai 50100, Thailand', 'phone' => '+66 53 905 000'],
                        ['label' => 'Chiang Rai', 'name' => 'Grand Vista Hotel Chiang Rai', 'address' => 'Moo 25 Phahonyothin Road, Tambon Rop Wiang, Mueang Chiang Rai District, Chiang Rai 57000, Thailand', 'phone' => '+66 53 746 053'],
                    ], [
                        ['image' => $photo('1671188893377-ee825a53d27f'), 'alt' => 'The White Temple reflected in still water in Chiang Rai', 'eyebrow' => 'CHIANG RAI', 'title' => 'Wat Rong Khun'],
                        ['image' => $photo('1528181304800-259b08848526'), 'alt' => 'Golden Lanna temples in Chiang Mai', 'eyebrow' => 'CHIANG MAI', 'title' => 'Lanna temples'],
                        ['image' => $photo('1573822619717-605a97a3856c'), 'alt' => 'Ornate white spires of Wat Rong Khun', 'eyebrow' => 'THE WHITE TEMPLE', 'title' => 'A work of art'],
                        ['image' => $photo('1532925562556-266f9db734ad'), 'alt' => 'Misty countryside and rice fields in Northern Thailand', 'eyebrow' => 'NORTHERN THAILAND', 'title' => 'Mountain valleys'],
                    ]),
                ],
            ],
        ];
    }
}
