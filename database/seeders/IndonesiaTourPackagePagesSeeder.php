<?php

namespace Database\Seeders;

use App\Models\TourPackagePage;
use Illuminate\Database\Seeder;

class IndonesiaTourPackagePagesSeeder extends Seeder
{
    private const INCLUSIONS = [
        'Transportation throughout the trip',
        'Hotel accommodation with daily breakfast',
        'Private tours and entrance tickets',
        'Mineral water (1 bottle during tours)',
        'Full board meals (breakfast, lunch and dinner)',
    ];

    private const EXCLUSIONS = [
        'Airfare',
        'Travel insurance',
        'Visa (if required)',
        'Peak season surcharge',
        'Optional tours',
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
            $page->parent_slug = 'indonesia';
            $page->is_custom = true;
            $page->save();
        }

        $this->linkIndonesiaDestinations();
    }

    /**
     * @return array<string, array{name: string, content: array<string, mixed>}>
     */
    private function pages(): array
    {
        $whatsapp = fn (string $name): string => 'https://wa.me/60165500590?text='.rawurlencode("Hi, I'd like to ask about the {$name} private tour package.");

        $package = fn (string $name, string $headline, string $body, array $itineraries, array $hotels = [], array $gallery = []): array => [
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
            'accommodation_headline' => "Where you will stay in {$name}.",
            'accommodation_body' => 'Hotels are subject to availability at the time of booking.',
            'hotels' => $hotels,
        ];

        return [
            'yogyakarta' => [
                'name' => 'Yogyakarta',
                'content' => [
                    'meta_title' => 'Yogyakarta Tour Packages | Smart Muslim by Roystar Travel & Tours',
                    'meta_description' => 'Explore Borobudur, Mount Merapi, Prambanan and Yogyakarta on private 4D3N and 5D4N tours.',
                    'hero_eyebrow' => 'JAVA · INDONESIA',
                    'hero_headline' => 'Discover Yogyakarta.',
                    'hero_highlight' => 'Temples, culture & nature.',
                    'hero_body' => 'Explore Borobudur and Prambanan, discover Mount Merapi by jeep and experience the culture and landscapes of Yogyakarta.',
                    'hero_note' => 'BOROBUDUR · MERAPI · PRAMBANAN',
                    'hero_image' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=2000&q=90',
                    'intro_eyebrow' => 'WELCOME TO YOGYAKARTA',
                    'intro_headline' => 'Discover the heart of',
                    'intro_highlight' => 'Java.',
                    'intro_body' => 'From temples and royal landmarks to volcanic scenery, traditional crafts and sunset coastlines, Yogyakarta offers a rich mix of culture and nature.',
                    'intro_image' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=1000&q=85',
                    'intro_image_alt' => 'Historic architecture in Yogyakarta',
                    'intro_image_caption' => 'A journey through Java’s culture and heritage.',
                    'highlights_eyebrow' => 'TEMPLES · CULTURE · NATURE',
                    'highlights_headline' => 'A memorable journey through Java.',
                    'highlights_body' => 'Choose a temple, nature or extended cultural itinerary for your Yogyakarta visit.',
                    'highlights' => [
                        ['title' => 'Borobudur & Prambanan', 'body' => 'Visit two of Indonesia’s most celebrated temple complexes.'],
                        ['title' => 'Mount Merapi by jeep', 'body' => 'Explore volcanic landmarks and the Sisa Hartaku Museum on a 4×4 lava tour.'],
                        ['title' => 'Royal heritage & coast', 'body' => 'Discover the Sultan’s Palace, Taman Sari and the beaches around Parangtritis.'],
                    ],
                    'contact_headline' => 'Plan your Yogyakarta getaway.',
                    'contact_body' => 'Share your preferred itinerary and travel dates with our team.',
                    'contact_href' => $whatsapp('Yogyakarta'),
                    'package_data' => $package('YOGYAKARTA', 'Choose your Yogyakarta journey.', 'Three private itineraries combining temples, culture, volcanic scenery and local experiences.', [
                        [
                            'duration' => '4 DAYS · 3 NIGHTS',
                            'title' => '4D3N Yogyakarta Borobudur',
                            'summary' => 'A cultural journey featuring Borobudur, Mount Merapi and Prambanan.',
                            'days' => [
                                ['title' => 'Arrival in Yogyakarta', 'body' => 'Upon arrival at Yogyakarta International Airport (YIA), meet the tour guide and transfer for lunch at a local restaurant. Visit the Pottery Museum at Kasongan Village, famous for traditional handicrafts. Enjoy sunset at Parangtritis Beach or Gumuk Pasir before dinner and hotel check-in.', 'meals' => 'LUNCH · DINNER'],
                                ['title' => 'Borobudur Temple', 'body' => 'After breakfast, take a 4×4 jeep lava tour around Mount Merapi, visiting the Sisa Hartaku Museum, Alien Rock and Kaliadem Bunker. After lunch, continue to Borobudur Temple, a UNESCO World Heritage Site. Dinner before returning to the hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Cultural & historical tour', 'body' => 'Visit the Kraton Yogyakarta, the official palace of the Sultan, and Taman Sari, the former royal water garden. After lunch, explore Prambanan Temple, the largest Hindu temple complex in Indonesia. Dinner and return to the hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Departure from Yogyakarta', 'body' => 'After breakfast and hotel check-out, transfer to Yogyakarta International Airport (YIA) for your return flight.', 'meals' => 'BREAKFAST'],
                            ],
                        ],
                        [
                            'duration' => '4 DAYS · 3 NIGHTS',
                            'title' => '4D3N Yogyakarta Nature',
                            'summary' => 'A nature-focused itinerary with Mount Merapi, scenic viewpoints and sunset by the coast.',
                            'days' => [
                                ['title' => 'Arrival in Yogyakarta', 'body' => 'Arrive at Yogyakarta Airport and meet the tour guide. Enjoy lunch featuring traditional Javanese cuisine, then visit Kasongan Village to see its pottery. Watch sunset at Parangtritis Beach or Gumuk Pasir, followed by dinner and hotel check-in.', 'meals' => 'LUNCH · DINNER'],
                                ['title' => 'Volcano tour', 'body' => 'After breakfast, take the Mount Merapi Lava Tour by 4×4 jeep, visiting the Sisa Hartaku Museum, Alien Rock and Kaliadem Bunker. After lunch, continue to Breksi Cliff and HeHa Sky View. Dinner and return to the hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Culture & coastline', 'body' => 'Visit Kotagede, the center of traditional silver craftsmanship, and the scenic Pinus Pengger Pine Forest. After lunch, enjoy beach activities at Parangtritis and sunset at Obelix Sea View. Dinner and return to the hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Departure from Yogyakarta', 'body' => 'After breakfast and check-out, stop for souvenir shopping before transfer to the airport for your return flight.', 'meals' => 'BREAKFAST'],
                            ],
                        ],
                        [
                            'duration' => '5 DAYS · 4 NIGHTS',
                            'title' => '5D4N Yogyakarta Bonanza',
                            'summary' => 'Combine Borobudur, Yogyakarta heritage and a day exploring the sights and markets of Solo.',
                            'days' => [
                                ['title' => 'Arrival in Yogyakarta', 'body' => 'Arrive at Yogyakarta Airport, meet the tour guide and have lunch. Visit Kasongan Village for pottery arts, then enjoy sunset at Parangtritis Beach or Gumuk Pasir. Dinner and hotel check-in.', 'meals' => 'LUNCH · DINNER'],
                                ['title' => 'Borobudur Temple', 'body' => 'After breakfast, take a Mount Merapi jeep lava tour, visiting the Sisa Hartaku Museum, Alien Rock and Kaliadem Bunker. Visit Borobudur Temple, have lunch and shop along Malioboro Street. Dinner and return to the hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Cultural & historical tour', 'body' => 'Visit the Kraton Yogyakarta (Sultan’s Palace) and Taman Sari Water Castle. After lunch, continue to Prambanan Temple. Dinner and overnight at the hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Shopping tour in Solo', 'body' => 'Travel to Solo to visit Mangkunegaran Palace, Triwindu Market and Batik Laweyan. Shop at Klewer Market or PGS. Lunch and dinner are provided before returning to Yogyakarta.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Departure from Yogyakarta', 'body' => 'After breakfast and hotel check-out, transfer to the airport for your return flight.', 'meals' => 'BREAKFAST'],
                            ],
                        ],
                    ], [
                        ['label' => 'Yogyakarta', 'name' => 'Meliá Purosani Yogyakarta', 'address' => 'Jl. Mayor Suryotomo No.31, Ngupasan, Kec. Gondomanan, Kota Yogyakarta, Daerah Istimewa Yogyakarta 55122, Indonesia', 'phone' => '+62 274 589521'],
                    ]),
                ],
            ],
            'medan' => [
                'name' => 'Medan',
                'content' => [
                    'meta_title' => 'Medan & Lake Toba Tour Packages | Smart Muslim by Roystar Travel & Tours',
                    'meta_description' => 'Discover Medan, Lake Toba, Samosir Island and Berastagi on private 4D3N and 5D4N North Sumatra tours.',
                    'hero_eyebrow' => 'NORTH SUMATRA · INDONESIA',
                    'hero_headline' => 'Explore Medan.',
                    'hero_highlight' => 'Lake Toba & highlands.',
                    'hero_body' => 'Travel from Medan to Lake Toba, Samosir Island and the highlands of Berastagi on a private North Sumatra journey.',
                    'hero_note' => 'MEDAN · LAKE TOBA · BERASTAGI',
                    'hero_image' => 'images/series/medan-5.jpg',
                    'intro_eyebrow' => 'WELCOME TO NORTH SUMATRA',
                    'intro_headline' => 'From the city to',
                    'intro_highlight' => 'Lake Toba.',
                    'intro_body' => 'Discover Batak heritage on Samosir Island, see the scale of Lake Toba, visit Sipiso-Piso Waterfall and enjoy the cool highlands of Berastagi.',
                    'intro_image' => 'images/series/medan-1.jpg',
                    'intro_image_alt' => 'Sipiso-Piso Waterfall plunging into a green canyon',
                    'intro_image_caption' => 'Discover the landscapes of North Sumatra.',
                    'highlights_eyebrow' => 'CITY · ISLAND · HIGHLANDS',
                    'highlights_headline' => 'The many sides of North Sumatra.',
                    'highlights_body' => 'Pair Medan’s landmarks with Lake Toba, Samosir Island and Berastagi.',
                    'highlights' => [
                        ['title' => 'Lake Toba & Samosir', 'body' => 'Cross the lake by ferry and discover the villages and scenery of Samosir Island.'],
                        ['title' => 'Batak culture', 'body' => 'Visit Tomok and Ambarita villages, known for their cultural heritage.'],
                        ['title' => 'Berastagi & Medan', 'body' => 'See Sipiso-Piso Waterfall, local markets and Medan’s historic landmarks.'],
                    ],
                    'contact_headline' => 'Plan your North Sumatra journey.',
                    'contact_body' => 'Tell us whether you prefer a 4D3N or 5D4N tour and our team will help with your plans.',
                    'contact_href' => $whatsapp('Medan and Lake Toba'),
                    'package_data' => $package('MEDAN & LAKE TOBA', 'Choose your North Sumatra itinerary.', 'Private 4D3N and 5D4N journeys through Medan, Parapat, Lake Toba, Samosir Island and Berastagi.', [
                        [
                            'duration' => '4 DAYS · 3 NIGHTS',
                            'title' => '4D3N Medan · Lake Toba · Berastagi',
                            'summary' => 'A four-day journey from Medan to Lake Toba, Samosir Island and Berastagi.',
                            'days' => [
                                ['title' => 'Medan · Parapat · Samosir Island', 'body' => 'Upon arrival at Medan Airport, meet your representative and transfer to Lake Toba. Stop at Kedai Patent for local delicacies and visit Sidamanik Tea Plantation. In Parapat, take a ferry across Lake Toba to Samosir Island, check in and enjoy dinner at a local restaurant.', 'meals' => 'LUNCH · DINNER'],
                                ['title' => 'Samosir Island · Parapat · Berastagi', 'body' => 'After breakfast and check-out, explore Tomok and Ambarita villages. Return to Parapat for lunch, then travel to Berastagi with a stop at Sipiso-Piso Waterfall. Dinner and hotel check-in in Berastagi.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Berastagi · Medan City', 'body' => 'After breakfast and check-out, return to Medan. Stop at the Fruit Market and Lumbini Park. In Medan, visit Maimoon Palace, the Grand Al-Mashun Mosque, fabric shops, Pasar Ikan, Sun Plaza and Medan Mall. Dinner and hotel check-in.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Departure from Medan', 'body' => 'Breakfast at the hotel and free time until check-out. Transfer from the hotel to Medan Airport for your departure flight.', 'meals' => 'BREAKFAST'],
                            ],
                        ],
                        [
                            'duration' => '5 DAYS · 4 NIGHTS',
                            'title' => '5D4N Medan · Lake Toba · Berastagi',
                            'summary' => 'An extended route with more time around Lake Toba and Samosir Island.',
                            'days' => [
                                ['title' => 'Arrival in Medan · Parapat', 'body' => 'Arrive in Medan for meet and greet, then travel to Lake Toba. Stop at the Patent Shop for Medan souvenirs. Lunch and dinner at local restaurants, hotel check-in and overnight stay in Parapat.', 'meals' => 'LUNCH · DINNER'],
                                ['title' => 'Parapat · Samosir Island', 'body' => 'After breakfast, explore the southern and western sides of Lake Toba. Visit Bukit Senyum, Huta Ginjang, Janji Waterfall, Efrata Waterfall, Holbung Hills, Ambarita and Tomok Village. Lunch and dinner at local restaurants, then overnight on Samosir Island.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Samosir Island · Parapat · Berastagi', 'body' => 'After breakfast and check-out, take a ferry back to Parapat. Continue to Berastagi, stopping at Pantai Bebas, Sidamanik Tea Plantation and Sipiso-Piso Waterfall. Visit Berastagi Fruit Market, then dinner and overnight at the hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Berastagi · Medan City', 'body' => 'After breakfast and check-out, return to Medan with stops at the Fruit Market and Lumbini Park. Visit Maimoon Palace, the Grand Al-Mashun Mosque, fabric shops, Pasar Ikan, Sun Plaza and Medan Mall. Dinner and hotel check-in.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Departure from Medan', 'body' => 'Breakfast at the hotel, free time until check-out, then transfer to Medan Airport for your departure flight.', 'meals' => 'BREAKFAST'],
                            ],
                        ],
                    ], [
                        ['label' => 'Parapat', 'name' => 'Hotel Niagara Parapat', 'address' => 'Jl. Pembangunan No.1, Parapat, Kec. Girsang Sipangan Bolon, Kabupaten Simalungun, Sumatera Utara 21174, Indonesia', 'phone' => '+62 625 41028'],
                        ['label' => 'Samosir Island', 'name' => 'Marianna Resort & Convention Tuktuk', 'address' => 'MV92+7JW, JL. PUD, Tuktuk Siadong, Simanindo, Kabupaten Samosir, Sumatera Utara 22395, Indonesia', 'phone' => '+62 819-7896-999'],
                        ['label' => 'Berastagi', 'name' => 'Taman Simalem Resort', 'address' => 'Jl. Raya Merek No.KM. 9, Kodon-Kodon, Merek, Karo Regency, 22173 Kodon-Kodon, Karo, North Sumatra, Indonesia', 'phone' => '+62-61-4577-616'],
                        ['label' => 'Medan City', 'name' => 'Grand City Hall Medan', 'address' => 'Jl. Balai Kota No.1, Kesawan, Kec. Medan Bar., Kota Medan, Sumatera Utara 20112, Indonesia', 'phone' => '+62 61 4557000'],
                    ], [
                        ['image' => 'images/series/medan-5.jpg', 'alt' => 'Traditional Batak houses with curved roofs on Samosir Island', 'eyebrow' => 'BATAK CULTURE', 'title' => 'Samosir Island'],
                        ['image' => 'images/series/medan-2.jpg', 'alt' => 'Sunset over the calm water of Lake Toba', 'eyebrow' => 'THE WORLD’S LARGEST VOLCANIC LAKE', 'title' => 'Lake Toba'],
                        ['image' => 'images/series/medan-1.jpg', 'alt' => 'Sipiso-Piso Waterfall plunging into a green canyon', 'eyebrow' => 'ON THE WAY TO BERASTAGI', 'title' => 'Sipiso-Piso Waterfall'],
                        ['image' => 'images/series/medan-3.jpg', 'alt' => 'Maimoon Palace in Medan under a blue sky', 'eyebrow' => 'ROYAL HERITAGE', 'title' => 'Maimoon Palace'],
                        ['image' => 'images/series/medan-4.jpg', 'alt' => 'The Grand Al-Mashun Mosque of Medan with its black domes', 'eyebrow' => 'FAITH & ARCHITECTURE', 'title' => 'Grand Al-Mashun Mosque'],
                    ]),
                ],
            ],
            'surabaya' => [
                'name' => 'Surabaya',
                'content' => [
                    'meta_title' => 'Surabaya, Bromo & Malang Tour Packages | Smart Muslim by Roystar Travel & Tours',
                    'meta_description' => 'Explore Mount Bromo, Malang and Surabaya on private 4D3N and 5D4N East Java tours.',
                    'hero_eyebrow' => 'EAST JAVA · INDONESIA',
                    'hero_headline' => 'Discover East Java.',
                    'hero_highlight' => 'Bromo, Batu & Malang.',
                    'hero_body' => 'Catch sunrise at Mount Bromo, explore Malang and Batu, and enjoy time in Surabaya on a private East Java journey.',
                    'hero_note' => 'SURABAYA · BROMO · MALANG',
                    'hero_image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=2000&q=90',
                    'intro_eyebrow' => 'WELCOME TO EAST JAVA',
                    'intro_headline' => 'From sunrise at Bromo to',
                    'intro_highlight' => 'Malang.',
                    'intro_body' => 'Experience an early-morning Mount Bromo jeep tour, waterfalls and attractions around Batu, Malang’s heritage and Surabaya’s city sights.',
                    'intro_image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1000&q=85',
                    'intro_image_alt' => 'Mountain and highland scenery in East Java',
                    'intro_image_caption' => 'Explore East Java at your own pace.',
                    'highlights_eyebrow' => 'VOLCANO · CITY · HIGHLANDS',
                    'highlights_headline' => 'A varied East Java escape.',
                    'highlights_body' => 'Combine an iconic volcano sunrise with city heritage, nature and local shopping.',
                    'highlights' => [
                        ['title' => 'Mount Bromo sunrise', 'body' => 'Travel by 4WD jeep to Penanjakan Peak and visit Bromo Crater, Savana Field and Whispering Sand.'],
                        ['title' => 'Batu & Malang', 'body' => 'Visit Coban Rondo Waterfall, Batu Orchard and Museum Angkut.'],
                        ['title' => 'Surabaya city time', 'body' => 'Enjoy local shopping and time to explore Surabaya before your flight home.'],
                    ],
                    'contact_headline' => 'Plan your East Java tour.',
                    'contact_body' => 'Ask our team about the 4D3N and 5D4N Bromo, Batu and Malang itineraries.',
                    'contact_href' => $whatsapp('Surabaya, Bromo and Malang'),
                    'package_data' => $package('SURABAYA & EAST JAVA', 'Choose your East Java itinerary.', 'Private 4D3N and 5D4N journeys through Surabaya, Mount Bromo, Batu and Malang.', [
                        [
                            'duration' => '4 DAYS · 3 NIGHTS',
                            'title' => '4D3N Surabaya · Bromo · Batu · Malang',
                            'summary' => 'A four-day East Java tour with a Bromo sunrise and a day in Batu.',
                            'days' => [
                                ['title' => 'Kuala Lumpur · Surabaya · Bromo', 'body' => 'Arrive at Juanda Airport in Surabaya. Have lunch at a local restaurant, then visit Bhakti Alam Fruit Garden. Transfer to Probolinggo with dinner along the way, check in at a hotel in the Bromo area and rest.', 'meals' => 'LUNCH · DINNER'],
                                ['title' => 'Bromo · Malang', 'body' => 'Depart at 2:00 AM by 4WD jeep to Penanjakan Peak for sunrise over Mount Bromo. Visit Bromo Crater, Savana Field and Whispering Sand. Have breakfast and check out, then transfer to Malang for lunch and hotel check-in. Visit Kayutangan Heritage in the afternoon.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Malang · Batu · Malang', 'body' => 'Visit Coban Rondo Waterfall and enjoy apple picking at Batu Orchard. Stop for souvenirs at Brawijaya, have lunch and visit Museum Angkut. Dinner and return to the hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Malang · Surabaya · Kuala Lumpur', 'body' => 'After breakfast and hotel check-out, stop for photos at Malang Monument and shop for souvenirs in Surabaya. Transfer to Juanda Airport for your flight back to Kuala Lumpur.', 'meals' => 'BREAKFAST'],
                            ],
                        ],
                        [
                            'duration' => '5 DAYS · 4 NIGHTS',
                            'title' => '5D4N Surabaya · Bromo · Batu · Malang',
                            'summary' => 'Add an extra day in Surabaya for local markets, layered cake and shopping.',
                            'days' => [
                                ['title' => 'Kuala Lumpur · Surabaya · Bromo', 'body' => 'Arrive at Juanda Airport, Surabaya, and have lunch at a nearby restaurant. Visit Bhakti Alam Fruit Garden to taste fresh fruits, then travel for around 2–3 hours to Probolinggo. Dinner near Probolinggo, hotel check-in and rest.', 'meals' => 'LUNCH · DINNER'],
                                ['title' => 'Bromo · Malang', 'body' => 'At 2:00 AM, take a jeep to see sunrise at Mount Bromo. Visit Bromo Crater, Savana Field and Whispering Sand. Return to the hotel for breakfast and check out. Travel to Malang, have lunch and check in at the hotel. Visit Kayutangan Heritage in the afternoon.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Malang · Batu · Malang', 'body' => 'Visit Coban Rondo Waterfall, go apple picking at Batu Orchard and shop for souvenirs at Brawijaya. Have lunch at a local restaurant and visit Museum Angkut, a classic vehicle exhibition. Dinner and return to the hotel.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Malang · Surabaya', 'body' => 'After breakfast and check-out, stop for photos at Malang Monument and visit Oro-oro Dowo Market or Klojen Market. Travel to Surabaya for lunch, stop for layered cake at Lapis Spikoe and enjoy free time at Tunjungan Plaza. Check in at the hotel in Surabaya.', 'meals' => 'BREAKFAST · LUNCH'],
                                ['title' => 'Surabaya · Kuala Lumpur', 'body' => 'Breakfast at the hotel and free time to buy souvenirs around Surabaya. Transfer to Juanda Airport for your return flight to Kuala Lumpur.', 'meals' => 'BREAKFAST'],
                            ],
                        ],
                    ], [
                        ['label' => 'Bromo', 'name' => 'Lereng Bromo Hotel', 'address' => 'Jl. Bromo, Ledok Sari, Wonokitri, Kec. Tosari, Pasuruan, Jawa Timur 67177, Indonesia', 'phone' => '+62 813-3659-8889'],
                        ['label' => 'Malang', 'name' => 'Ijen Suites Resort & Convention Malang', 'address' => '2J98+M6M Nirwana Raya, Jl. Ijen No.16 Blok A, Bareng, Kec. Klojen, Kota Malang, Jawa Timur 65116, Indonesia', 'phone' => '+62 341 3301000'],
                        ['label' => 'Surabaya', 'name' => 'Holiday Inn Express Surabaya', 'address' => 'Jl. Kedung Doro No.54-58, Sawahan, Kec. Sawahan, Surabaya, Jawa Timur 60251, Indonesia', 'phone' => '+62 31 5355500'],
                    ]),
                ],
            ],
            'padang' => [
                'name' => 'Padang',
                'content' => [
                    'meta_title' => 'Padang & Bukittinggi Tour Packages | Smart Muslim by Roystar Travel & Tours',
                    'meta_description' => 'Explore Padang, Bukittinggi, Lembah Harau and Pagaruyung Palace on private 4D3N and 5D4N West Sumatra tours.',
                    'hero_eyebrow' => 'WEST SUMATRA · INDONESIA',
                    'hero_headline' => 'Explore West Sumatra.',
                    'hero_highlight' => 'Padang & Bukittinggi.',
                    'hero_body' => 'Discover Minangkabau heritage, highland scenery, waterfalls and the coast on a private West Sumatra journey.',
                    'hero_note' => 'PADANG · BUKITTINGGI · PAGARUYUNG',
                    'hero_image' => 'https://images.unsplash.com/photo-1754615842174-7e6c8d1f0ca3?auto=format&fit=crop&w=2000&q=90',
                    'intro_eyebrow' => 'WELCOME TO WEST SUMATRA',
                    'intro_headline' => 'Discover a region shaped by',
                    'intro_highlight' => 'heritage & nature.',
                    'intro_body' => 'Travel between Padang and Bukittinggi, visit Lembah Anai and Lembah Harau, explore Pagaruyung Palace and enjoy the landscapes around Lake Maninjau.',
                    'intro_image' => 'https://images.unsplash.com/photo-1657453725781-21c0e61e5069?auto=format&fit=crop&w=1000&q=85',
                    'intro_image_alt' => 'Close view of the Jam Gadang clock tower in Bukittinggi',
                    'intro_image_caption' => 'A private journey through West Sumatra.',
                    'highlights_eyebrow' => 'HIGHLANDS · HERITAGE · COAST',
                    'highlights_headline' => 'A West Sumatra journey to remember.',
                    'highlights_body' => 'Explore the culture, landscapes and local experiences around Padang and Bukittinggi.',
                    'highlights' => [
                        ['title' => 'Bukittinggi landmarks', 'body' => 'See Sianok Canyon, Jam Gadang, traditional markets and the Japanese Tunnels.'],
                        ['title' => 'Minangkabau heritage', 'body' => 'Visit Pagaruyung Palace and discover local crafts and traditional cuisine.'],
                        ['title' => 'Highlands & coast', 'body' => 'Take in Lembah Harau, Kelok 9, Lake Maninjau viewpoints and Padang Beach.'],
                    ],
                    'contact_headline' => 'Plan your Padang and Bukittinggi tour.',
                    'contact_body' => 'Choose a 4D3N or 5D4N itinerary and share your travel dates with our team.',
                    'contact_href' => $whatsapp('Padang and Bukittinggi'),
                    'package_data' => $package('PADANG & BUKITTINGGI', 'Choose your West Sumatra itinerary.', 'Private 4D3N and 5D4N journeys through Padang, Bukittinggi and the landscapes of West Sumatra.', [
                        [
                            'duration' => '4 DAYS · 3 NIGHTS',
                            'title' => '4D3N Padang · Bukittinggi',
                            'summary' => 'A four-day route through Bukittinggi’s highlands and Padang’s coastal landmarks.',
                            'days' => [
                                ['title' => 'Arrival in Padang · Bukittinggi', 'body' => 'Upon arrival at Minangkabau International Airport, meet your representative and travel to Bukittinggi (approximately two hours). Stop at Lembah Anai Waterfall, Rumah Gadang Museum in Padang Panjang, Bika Neraka cake shop and traditional fabric and embroidery shops. Check in at the hotel; lunch and dinner at local restaurants.', 'meals' => 'LUNCH · DINNER'],
                                ['title' => 'Explore the history of Bukittinggi', 'body' => 'Visit Lembah Harau and Kelok 9 Bridge, then continue to Batusangkar to explore Pagaruyung Palace. Stop at Kiniko for coffee, smoked bananas and local delicacies. Lunch and dinner provided. A traditional Minangkabau dance performance is optional in the evening.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Bukittinggi · Padang', 'body' => 'After breakfast and hotel check-out, visit Sianok Canyon, Jam Gadang, Pasar Atas, Landi River and Puncak Lawang for views of Lake Maninjau. Travel to Padang and check in. Lunch and dinner at local restaurants.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Departure from Padang', 'body' => 'After breakfast and check-out, enjoy a Padang city tour including the Grand Mosque, Padang Beach, Siti Nurbaya Bridge and Chinatown. Transfer to Minangkabau Airport for your return flight.', 'meals' => 'BREAKFAST'],
                            ],
                        ],
                        [
                            'duration' => '5 DAYS · 4 NIGHTS',
                            'title' => '5D4N Padang · Bukittinggi',
                            'summary' => 'An extended West Sumatra trip with extra time in Bukittinggi and the highlands.',
                            'days' => [
                                ['title' => 'Arrival in Padang · Bukittinggi', 'body' => 'Arrive at Minangkabau International Airport, meet your representative and travel to Bukittinggi (approximately two hours). Stop at Lembah Anai Waterfall, Rumah Gadang Museum in Padang Panjang, Bika Neraka cake shop and traditional fabric and embroidery shops. Check in at the hotel. Lunch and dinner at local restaurants.', 'meals' => 'LUNCH · DINNER'],
                                ['title' => 'Explore Bukittinggi', 'body' => 'Visit Sianok Canyon and Jam Gadang Clock Tower, then explore Pasar Atas and Pasar Bawah. After lunch, visit the Japanese Tunnels (Lobang Jepang) and Fort de Kock. Dinner at a local restaurant. Optional traditional Minangkabau dance performance in the evening.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Leisure & nature tour', 'body' => 'Visit Lembah Harau and its waterfalls, then continue to Kelok 9 Bridge and Batusangkar for Pagaruyung Palace. Stop at Kiniko for local coffee, smoked bananas and snacks. Lunch and dinner included; overnight in Bukittinggi.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Bukittinggi · Padang', 'body' => 'After breakfast and check-out, travel to Padang via Puncak Lawang for views of Lake Maninjau. Visit Landi River en route and check in at the Padang hotel. Enjoy an afternoon city tour including Padang Beach and Siti Nurbaya Bridge. Lunch and dinner at local restaurants.', 'meals' => 'BREAKFAST · LUNCH · DINNER'],
                                ['title' => 'Padang city tour & departure', 'body' => 'After breakfast and check-out, explore Padang including the Grand Mosque and Chinatown. Have lunch at a local restaurant, then transfer to Minangkabau International Airport for your return flight.', 'meals' => 'BREAKFAST · LUNCH'],
                            ],
                        ],
                    ], [], [
                        ['image' => 'https://images.unsplash.com/photo-1754615842174-7e6c8d1f0ca3?auto=format&fit=crop&w=1800&q=85', 'alt' => 'Jam Gadang clock tower in the centre of Bukittinggi', 'eyebrow' => 'WEST SUMATRA', 'title' => 'Jam Gadang'],
                        ['image' => 'https://images.unsplash.com/photo-1657453725781-21c0e61e5069?auto=format&fit=crop&w=1800&q=85', 'alt' => 'Close view of the Jam Gadang clock tower', 'eyebrow' => 'BUKITTINGGI', 'title' => 'The heart of Bukittinggi'],
                        ['image' => 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1800&q=85', 'alt' => 'Sun setting over green highland hills', 'eyebrow' => 'HIGHLAND VIEWS', 'title' => 'Puncak Lawang'],
                    ]),
                ],
            ],
        ];
    }

    private function linkIndonesiaDestinations(): void
    {
        $page = TourPackagePage::query()->where('slug', 'indonesia')->first();

        if (! $page) {
            return;
        }

        $destinationSlugs = [
            'Medan' => 'medan',
            'Padang' => 'padang',
            'Surabaya' => 'surabaya',
            'Yogyakarta' => 'yogyakarta',
        ];
        $slides = $page->slides;

        foreach ($slides as &$slide) {
            $slug = $destinationSlugs[$slide['title'] ?? ''] ?? null;

            // Cards are only linked once their package page exists, so they never lead to a missing page.
            if (! $slug || ! TourPackagePage::query()->where('slug', $slug)->exists()) {
                continue;
            }

            $slide['link_label'] = 'Explore '.$slide['title'].' tour packages';
            $slide['link_url'] = route('tour-package.show', $slug, false);
        }
        unset($slide);

        $page->update(['slides' => $slides]);
    }
}
