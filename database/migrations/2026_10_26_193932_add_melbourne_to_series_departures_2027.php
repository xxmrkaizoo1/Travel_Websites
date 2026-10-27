<?php

use App\Models\TourPackagePage;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the Melbourne 7D6N (Royal Brunei) package from the 23 Oct brochure to Series Departures 2027,
 * replacing the "First destination" placeholder and the page's generic starter text.
 */
return new class extends Migration
{
    private const PLACEHOLDER_NAME = 'First destination';

    private const STARTER_HERO_BODY = 'Plan a private trip to Series 2027 at your own pace, shaped around the places and experiences you love.';

    public function up(): void
    {
        $page = TourPackagePage::query()->where('slug', 'series-2027')->first();

        if ($page === null) {
            return;
        }

        $packageData = $page->package_data ?? [];
        $series = array_values(array_filter(
            $packageData['series'] ?? [],
            fn (array $destination): bool => $destination['name'] !== self::PLACEHOLDER_NAME,
        ));

        if (! in_array('Melbourne', array_column($series, 'name'), true)) {
            $series[] = $this->melbourne();
        }

        $packageData['series'] = $series;
        $page->package_data = $packageData;

        if ($page->hero_body === self::STARTER_HERO_BODY) {
            $page->fill($this->pageText());
        }

        $page->save();
    }

    public function down(): void
    {
        $page = TourPackagePage::query()->where('slug', 'series-2027')->first();

        if ($page === null) {
            return;
        }

        $packageData = $page->package_data ?? [];
        $packageData['series'] = array_values(array_filter(
            $packageData['series'] ?? [],
            fn (array $destination): bool => $destination !== $this->melbourne(),
        ));
        $page->package_data = $packageData;
        $page->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function pageText(): array
    {
        return [
            'meta_title' => 'Series Departures 2027 | Smart Muslim by Roystar Travel & Tours',
            'meta_description' => 'Join our 2027 group series departures, including Melbourne 7D6N with a Brunei city tour by Royal Brunei. Halal meals, a tour leader from Malaysia and a fully arranged itinerary.',
            'hero_eyebrow' => 'GROUP TOURS · 2027',
            'hero_headline' => 'Travel together.',
            'hero_highlight' => 'Series Departures 2027.',
            'hero_body' => 'Fixed-date group departures for 2027 with flights, hotels, halal meals and a tour leader all arranged for you.',
            'hero_cta_label' => 'Ask about 2027 departures',
            'hero_note' => 'MELBOURNE · BRUNEI',
            'intro_eyebrow' => 'WHY TRAVEL IN A GROUP',
            'intro_headline' => 'Great journeys are better',
            'intro_highlight' => 'shared.',
            'intro_body' => 'Group departures share the overall cost among the participants, so they are usually cheaper. The whole itinerary is arranged for you, with halal meals and a tour leader from Malaysia, so you can simply enjoy the trip.',
            'intro_image_alt' => 'Series Departures 2027',
            'intro_image_caption' => 'Your next group adventure.',
            'highlights_headline' => 'Why join a series departure?',
            'highlights_body' => 'Four good reasons to travel with a group.',
            'highlights' => [
                ['body' => 'The overall cost is shared among the participants, so group packages are usually cheaper.', 'title' => 'Lower cost'],
                ['body' => 'The whole itinerary is planned by the travel agency, so you do not have to organise a thing.', 'title' => 'Everything arranged'],
                ['body' => 'Enjoy the chance to socialise and meet new people, with an experienced tour leader sharing the stories of each place.', 'title' => 'Meet fellow travellers'],
                ['body' => 'Travelling in a group is more secure and the risk of getting lost is low.', 'title' => 'Safer together'],
            ],
            'destinations_eyebrow' => '2027 DESTINATIONS',
            'destinations_headline' => 'Where will you go in 2027?',
            'destinations_body' => 'Choose a destination to see its itinerary, departure dates, prices and photos.',
            'contact_eyebrow' => 'JOIN A DEPARTURE',
            'contact_headline' => 'Ready to travel with us?',
            'contact_body' => 'Tell us which destination interests you and we will share the available 2027 dates and prices.',
            'contact_cta_label' => 'Enquire on WhatsApp',
            'contact_href' => 'https://wa.me/60165500590?text='.rawurlencode("Hi, I'd like to know more about the Series Departures 2027."),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function melbourne(): array
    {
        $departure = static fn (string $date): array => [
            'date' => $date,
            'price' => 'RM 7,790',
            'availability' => 'Promo · normal price RM 8,090',
        ];
        $day = static fn (int $number, string $title, string $meals, string $transfer, string $places): array => [
            'day' => "DAY {$number}",
            'meals' => $meals,
            'title' => $title,
            'places' => $places,
            'transfer' => $transfer,
        ];

        return [
            'flag' => 'images/series/flag-au.png',
            'name' => 'Melbourne',
            'intro' => 'Our 7D6N Melbourne series flies Royal Brunei with a Brunei city tour on the way. Ride the Puffing Billy steam train, watch the Penguin Parade on Phillip Island, cruise the Yarra River and see the Brighton bathing boxes, with halal meals and a tour leader from Malaysia.',
            'country' => 'Australia',
            'gallery' => [
                ['alt' => 'Puffing Billy steam train crossing a wooden trestle bridge', 'image' => 'images/series/melbourne-1.jpg', 'caption' => 'Puffing Billy Steam Train'],
                ['alt' => 'Colourful bathing boxes on Brighton Beach', 'image' => 'images/series/melbourne-2.jpg', 'caption' => 'Brighton Bathing Boxes'],
                ['alt' => 'Visitors watching little penguins come ashore at the Penguin Parade', 'image' => 'images/series/melbourne-3.jpg', 'caption' => 'Penguin Parade'],
                ['alt' => 'Smart Muslim travellers at Maru Koala and Animal Park', 'image' => 'images/series/melbourne-4.jpg', 'caption' => 'Maru Koala Park'],
                ['alt' => 'A traveller posing with street art in Hosier Lane', 'image' => 'images/series/melbourne-5.jpg', 'caption' => 'Hosier Lane'],
            ],
            'tagline' => '7D6N · Puffing Billy, Penguin Parade & a Brunei city tour.',
            'advantages' => [
                ['body' => 'A one-way ride on the century-old steam train from Belgrave through the Dandenong Ranges forest. Ticket included.', 'icon' => '🚂', 'title' => 'Puffing Billy Steam Train'],
                ['body' => 'Watch little penguins waddle ashore at sunset on Phillip Island, then enjoy a seafood platter dinner.', 'icon' => '🐧', 'title' => 'Penguin Parade'],
                ['body' => 'Two visits to Bandar Seri Begawan, including Omar Ali Saifuddien Mosque, the waterfront and the Sultan\'s palace.', 'icon' => '🕌', 'title' => 'Brunei City Tour'],
                ['body' => 'Halal meals as listed, a tour leader from Malaysia and tipping all included.', 'icon' => '🍽️', 'title' => 'Halal Meals & Tour Leader'],
            ],
            'package_details' => [
                'note' => 'Now only RM 7,790 per person (normal price RM 8,090). Free consultation for your Australia visa (ETA) application. Contact us: +6016-550 0287 / +6017-388 6708.',
                'hotels' => [
                    ['name' => 'Oaks Melbourne on Market Hotel or similar', 'note' => 'Plus 1 night in Bandar Seri Begawan on Day 6.', 'room' => '', 'location' => 'Melbourne'],
                ],
                'return' => [
                    'time' => '13:30 – 20:25 (+1)',
                    'route' => 'Melbourne (MEL) → Kuala Lumpur (KUL)',
                    'flight' => 'BI006 · BI873',
                    'duration' => 'Via Bandar Seri Begawan, with 1 night in Brunei',
                ],
                'weather' => [
                    ['season' => 'Summer · Dec–Feb', 'description' => 'Summer', 'temperature' => '17°C to 30°C'],
                    ['season' => 'Autumn · Mar–May', 'description' => 'Autumn', 'temperature' => '13°C to 26°C'],
                    ['season' => 'Winter · Jun–Aug', 'description' => 'Winter · no snow in Melbourne', 'temperature' => '8°C to 19°C'],
                    ['season' => 'Spring · Sep–Nov', 'description' => 'Spring', 'temperature' => '11°C to 23°C'],
                ],
                'duration' => '7D6N',
                'excludes' => [
                    'Travel insurance',
                    'Visa (AUD 20)',
                    'Additional meals, except as stated in the itinerary',
                    'Personal expenses',
                ],
                'headline' => 'Melbourne 7D6N with Brunei City Tour',
                'includes' => [
                    'Royal Brunei flight tickets (20kg baggage + meal on board)',
                    'Private coach with driver',
                    'Hotel: Oaks On Market or similar',
                    'Halal meals as mentioned',
                    'Tour itinerary as mentioned',
                    'Tour leader from Malaysia',
                    'Tipping fee',
                ],
                'outbound' => [
                    'time' => '12:10 – 05:00 (+1)',
                    'route' => 'Kuala Lumpur (KUL) → Melbourne (MEL)',
                    'flight' => 'BI872 · BI005',
                    'duration' => 'Via Bandar Seri Begawan, with a Brunei city tour',
                ],
                'itinerary' => [
                    $day(1, 'Kuala Lumpur – Brunei City Tour – Melbourne', 'MOB', 'Flight', 'Assemble at KLIA 3 hours before departure. Fly to Bandar Seri Begawan (BI872, 12:10 – 14:35) for a Brunei city tour (own meal), then return to the airport for the overnight flight to Melbourne (BI005, 19:00).'),
                    $day(2, 'Melbourne City Tour', 'L, D', 'Private coach', 'Arrive at Tullamarine Airport at 05:00 and meet our local representative. City tour: Chinatown, Federation Square, St Patrick\'s Cathedral, Flinders Street Station, Victoria Parliament Building, Treasury Building and Fitzroy Gardens. Western lunch at The Meat, a 1-hour Yarra River cruise (ticket included), a photo stop at Hosier Lane and Indonesian dinner.'),
                    $day(3, 'Puffing Billy Train Ride', 'B, L', 'Private coach', 'Photo stop at the Brighton bathing boxes, then a one-way Puffing Billy steam train ride from Belgrave Station to Menzies Station (ticket included). Fish & chips lunch, then back to the city for shopping at Queen Victoria Market or DFO.'),
                    $day(4, 'Phillip Island Tour', 'B, L, D', 'Private coach', 'Maru Koala & Animal Park with a one-course lunch, Phillip Island Chocolate Factory (Panny\'s Amazing World of Chocolate), a photo stop at The Nobbies Centre and the Penguin Parade (standard viewing). Seafood platter dinner before returning to the hotel.'),
                    $day(5, 'Melbourne Leisure', 'B', '', 'Free day: a walking tour around the city or a free tram ride, or join the optional Great Ocean Road full-day tour (seat-in-coach, about AUD 150 per person).'),
                    $day(6, 'Melbourne – Bandar Seri Begawan', 'B, MOB', 'Private coach and flight', 'Check out and transfer to Tullamarine Airport. Fly to Bandar Seri Begawan (BI006, 13:30 – 17:40), meet our local representative and stay 1 night in Bandar Seri Begawan.'),
                    $day(7, 'Brunei City Tour – Kuala Lumpur', 'B', 'Private coach and flight', 'Check out for a city tour of Bandar Seri Begawan: Balai Khazanah, the waterfront, Omar Ali Saifuddien Mosque and the Sultan\'s palace. Fly back to KLIA 1 (BI873, 17:55 – 20:25). End of service.'),
                ],
                'tour_code' => '7MMELS',
                'departures' => [
                    $departure('6 Mar 2027'),
                    $departure('26 Apr 2027'),
                    $departure('22 May 2027'),
                    $departure('5 Jun 2027'),
                ],
                'essentials' => [
                    'time' => 'GMT +10 (GMT +11 until early April)',
                    'visa' => 'Australian ETA · AUD 20 · free consultation',
                    'currency' => 'Australian Dollar (AUD)',
                    'language' => 'English',
                    'arrival_card' => '',
                ],
                'price_from' => 'Now only RM 7,790 · normal price RM 8,090',
                'travel_dates' => 'March – June 2027',
                'itinerary_note' => 'Itineraries may be subject to change due to unforeseen circumstances.',
            ],
        ];
    }
};
