<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_package_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();
            $table->string('name', 60);
            $table->string('meta_title', 160);
            $table->string('meta_description', 255);
            $table->string('hero_eyebrow', 120);
            $table->string('hero_headline', 120);
            $table->string('hero_highlight', 160);
            $table->text('hero_body');
            $table->string('hero_cta_label', 100);
            $table->string('hero_note', 120);
            $table->string('hero_image', 2048);
            $table->string('intro_eyebrow', 120);
            $table->string('intro_headline', 160);
            $table->string('intro_highlight', 100);
            $table->text('intro_body');
            $table->string('intro_link_label', 120);
            $table->string('intro_image', 2048);
            $table->string('intro_image_alt', 255);
            $table->string('intro_image_caption', 120);
            $table->string('highlights_eyebrow', 120);
            $table->string('highlights_headline', 160);
            $table->string('highlights_body', 255);
            $table->json('highlights');
            $table->string('destinations_eyebrow', 120);
            $table->string('destinations_headline', 160);
            $table->string('destinations_body', 255);
            $table->json('slides');
            $table->string('contact_eyebrow', 120);
            $table->string('contact_headline', 160);
            $table->text('contact_body');
            $table->string('contact_cta_label', 100);
            $table->string('contact_href', 2048);
            $table->timestamps();
        });

        $now = now();

        foreach ($this->pages() as $page) {
            DB::table('tour_package_pages')->insert([
                ...$page,
                'highlights' => json_encode($page['highlights'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'slides' => json_encode($page['slides'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_package_pages');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pages(): array
    {
        return [
            [
                'slug' => 'indonesia',
                'name' => 'Indonesia',
                'meta_title' => 'Indonesia Private Tours | Smart Muslim by Roystar Travel & Tours',
                'meta_description' => 'Discover Indonesia\'s islands, culture and nature with a private journey planned around you.',
                'hero_eyebrow' => 'PRIVATE JOURNEYS THROUGH INDONESIA',
                'hero_headline' => 'A world of islands.',
                'hero_highlight' => 'One journey, your way.',
                'hero_body' => 'From Bali’s cultural heart to Lombok’s quiet coastlines, discover Indonesia at your own pace with a trip shaped around the places and experiences you love.',
                'hero_cta_label' => 'Plan your Indonesia trip',
                'hero_note' => '17,000+ ISLANDS · ENDLESS WAYS TO EXPLORE',
                'hero_image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=2000&q=90',
                'intro_eyebrow' => 'WELCOME TO INDONESIA',
                'intro_headline' => 'Find your own rhythm in',
                'intro_highlight' => 'the archipelago.',
                'intro_body' => 'Indonesia brings together tropical scenery, rich local traditions and welcoming communities across thousands of islands. Whether you imagine a relaxed beach break, time among historic landmarks or a little of both, our team can help you shape a private itinerary that suits your travel style.',
                'intro_link_label' => 'Explore places to discover',
                'intro_image' => 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=1000&q=85',
                'intro_image_alt' => 'Tropical Indonesian coastline with deep blue water',
                'intro_image_caption' => 'Made for your kind of escape.',
                'highlights_eyebrow' => 'A JOURNEY WITH MORE TO DISCOVER',
                'highlights_headline' => 'Many islands. Many ways to feel away.',
                'highlights_body' => 'Build a trip around the scenery, culture and moments you want to take home.',
                'highlights' => [
                    0 => [
                        'title' => 'Island landscapes',
                        'body' => 'Move between beaches, green highlands and volcanic scenery as your itinerary allows.',
                    ],
                    1 => [
                        'title' => 'Culture & heritage',
                        'body' => 'Discover local traditions, historic places, crafts and distinctive regional character.',
                    ],
                    2 => [
                        'title' => 'Travel at your pace',
                        'body' => 'Choose a slower island stay or combine several stops into one thoughtfully planned trip.',
                    ],
                ],
                'destinations_eyebrow' => 'A FEW PLACES TO BEGIN',
                'destinations_headline' => 'Where will Indonesia take you?',
                'destinations_body' => 'Start with a place that speaks to you. We can help you explore the details.',
                'slides' => [
                    0 => [
                        'image' => 'https://images.unsplash.com/photo-1518005020951-eccb494ad742?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Mosque architecture in Aceh',
                        'eyebrow' => 'HERITAGE & COASTLINES',
                        'title' => 'Aceh',
                        'body' => 'Discover rich local heritage, striking architecture and peaceful northern shores.',
                        'link_label' => 'Explore the 4D3N Aceh tour',
                        'link_url' => '/indonesia/aceh',
                    ],
                    1 => [
                        'image' => 'https://images.unsplash.com/photo-1519608487953-e999c86e7455?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'City scenery in Medan',
                        'eyebrow' => 'NORTHERN SUMATRA',
                        'title' => 'Medan',
                        'body' => 'Make Medan your gateway to regional food, culture and North Sumatra adventures.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                    2 => [
                        'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Scenic landscape around Padang',
                        'eyebrow' => 'COAST & HIGHLANDS',
                        'title' => 'Padang',
                        'body' => 'Explore West Sumatra’s coastal scenery, distinctive cuisine and green landscapes.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                    3 => [
                        'image' => 'https://images.unsplash.com/photo-1519608487953-e999c86e7455?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Urban evening scenery in Surabaya',
                        'eyebrow' => 'CITY & CULTURE',
                        'title' => 'Surabaya',
                        'body' => 'Experience a lively East Java city with historic districts and a vibrant food scene.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                    4 => [
                        'image' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Historic stone architecture in Yogyakarta',
                        'eyebrow' => 'HISTORY & HERITAGE',
                        'title' => 'Yogyakarta',
                        'body' => 'Explore historic landmarks and the arts and traditions of Java.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                    5 => [
                        'image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Temple and tropical scenery in Bali',
                        'eyebrow' => 'ISLAND ESCAPE',
                        'title' => 'Bali',
                        'body' => 'Explore temples, volcanic scenery, beaches and local culture on a private island getaway.',
                        'link_label' => 'Explore Bali tour packages',
                        'link_url' => '/bali',
                    ],
                ],
                'contact_eyebrow' => 'LET’S PLAN YOUR INDONESIA GETAWAY',
                'contact_headline' => 'Tell us what you would love to discover.',
                'contact_body' => 'Share your preferred places, travel dates and the kind of trip you have in mind. Our team will help you explore the possibilities.',
                'contact_cta_label' => 'Enquire on WhatsApp',
                'contact_href' => 'https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20plan%20a%20private%20trip%20to%20Indonesia.',
            ],
            [
                'slug' => 'thailand',
                'name' => 'Thailand',
                'meta_title' => 'Thailand Private Tours | Smart Muslim by Roystar Travel & Tours',
                'meta_description' => 'Discover Thailand\'s temples, northern landscapes and tropical shores with a private journey planned around you.',
                'hero_eyebrow' => 'PRIVATE JOURNEYS THROUGH THAILAND',
                'hero_headline' => 'A land of colour.',
                'hero_highlight' => 'Explore your way.',
                'hero_body' => 'From Bangkok’s lively streets to the calm of the northern hills and the Andaman coast, discover Thailand at your own pace with a private trip shaped around you.',
                'hero_cta_label' => 'Plan your Thailand trip',
                'hero_note' => 'CULTURE · COASTLINES · THOUGHTFUL TRAVEL',
                'hero_image' => 'https://images.unsplash.com/photo-1508009603885-50cf7c579365?auto=format&fit=crop&w=2000&q=90',
                'intro_eyebrow' => 'WELCOME TO THAILAND',
                'intro_headline' => 'Find your own rhythm in',
                'intro_highlight' => 'the land of smiles.',
                'intro_body' => 'Thailand brings together ornate temples, welcoming towns, distinctive cuisine and tropical scenery. Whether you picture time in the city, a slower escape by the sea or a journey through the north, our team can help you shape a private itinerary around the experiences you value.',
                'intro_link_label' => 'Explore places to discover',
                'intro_image' => 'https://images.unsplash.com/photo-1528181304800-259b08848526?auto=format&fit=crop&w=1000&q=85',
                'intro_image_alt' => 'Mountain scenery and greenery in Thailand',
                'intro_image_caption' => 'Made for your kind of escape.',
                'highlights_eyebrow' => 'A JOURNEY WITH MORE TO DISCOVER',
                'highlights_headline' => 'Culture, coastline and room to explore.',
                'highlights_body' => 'Build a trip around the places, flavours and moments you want to take home.',
                'highlights' => [
                    0 => [
                        'title' => 'Temples & traditions',
                        'body' => 'Explore historic landmarks, local crafts and the distinct character of each region.',
                    ],
                    1 => [
                        'title' => 'North to the coast',
                        'body' => 'Pair city discoveries and green northern landscapes with time beside the sea.',
                    ],
                    2 => [
                        'title' => 'Travel at your pace',
                        'body' => 'Choose an easy-going stay or combine several stops in one thoughtfully planned trip.',
                    ],
                ],
                'destinations_eyebrow' => 'A FEW PLACES TO BEGIN',
                'destinations_headline' => 'Where will Thailand take you?',
                'destinations_body' => 'Start with a place that speaks to you. We can help you explore the details.',
                'slides' => [
                    0 => [
                        'image' => 'https://images.unsplash.com/photo-1508009603885-50cf7c579365?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Colourful Bangkok street at night',
                        'eyebrow' => 'CITY & HERITAGE',
                        'title' => 'Bangkok',
                        'body' => 'Discover riverside landmarks, lively neighbourhoods and the capital’s celebrated food scene.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                    1 => [
                        'image' => 'https://images.unsplash.com/photo-1528181304800-259b08848526?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Green mountain scenery',
                        'eyebrow' => 'NORTHERN THAILAND',
                        'title' => 'Chiang Mai & Chiang Rai',
                        'body' => 'Take in a slower pace among historic temples, mountain scenery and local markets.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                    2 => [
                        'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Sunny tropical beach',
                        'eyebrow' => 'COASTAL GETAWAY',
                        'title' => 'Pattaya',
                        'body' => 'Balance relaxed seaside time with nearby attractions and an easy-going city stay.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                    3 => [
                        'image' => 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Tropical beach and turquoise sea',
                        'eyebrow' => 'ISLAND ESCAPE',
                        'title' => 'Phuket',
                        'body' => 'Enjoy a flexible island stay with beaches, viewpoints and time to unwind.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                    4 => [
                        'image' => 'https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Clear water along a tropical coast',
                        'eyebrow' => 'ANDAMAN COAST',
                        'title' => 'Krabi',
                        'body' => 'Discover dramatic limestone scenery, quiet coves and coastal boat journeys.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                    5 => [
                        'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Scenic green landscape in Thailand',
                        'eyebrow' => 'RIVER & NATURE',
                        'title' => 'Kanchanaburi',
                        'body' => 'Explore riverside scenery, historic sites and a more relaxed side of Thailand.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                ],
                'contact_eyebrow' => 'LET’S PLAN YOUR THAILAND GETAWAY',
                'contact_headline' => 'Tell us what you would love to discover.',
                'contact_body' => 'Share your preferred places, travel dates and the kind of trip you have in mind. Muslim-friendly meals and time for prayer can be included when requested.',
                'contact_cta_label' => 'Enquire on WhatsApp',
                'contact_href' => 'https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20plan%20a%20private%20trip%20to%20Thailand.',
            ],
            [
                'slug' => 'vietnam',
                'name' => 'Vietnam',
                'meta_title' => 'Vietnam Private Tours | Smart Muslim by Roystar Travel & Tours',
                'meta_description' => 'Discover Vietnam\'s historic cities, coastal scenery and local culture with a private journey planned around you.',
                'hero_eyebrow' => 'PRIVATE JOURNEYS THROUGH VIETNAM',
                'hero_headline' => 'A journey through colour.',
                'hero_highlight' => 'Vietnam, your way.',
                'hero_body' => 'From Hanoi’s old quarter to the lantern-lit streets of Hoi An and the Mekong Delta, discover Vietnam at your own pace on a private journey shaped around you.',
                'hero_cta_label' => 'Plan your Vietnam trip',
                'hero_note' => 'RICH HERITAGE · COASTAL BEAUTY · LOCAL FLAVOUR',
                'hero_image' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=2000&q=90',
                'intro_eyebrow' => 'WELCOME TO VIETNAM',
                'intro_headline' => 'Discover a country of',
                'intro_highlight' => 'many stories.',
                'intro_body' => 'Vietnam is a place of historic neighbourhoods, dramatic landscapes and memorable food. Explore a little or travel from north to south: our team can help you plan a private itinerary that fits your interests, pace and travel dates.',
                'intro_link_label' => 'Explore places to discover',
                'intro_image' => 'https://images.unsplash.com/photo-1504457047772-27faf1c00561?auto=format&fit=crop&w=1000&q=85',
                'intro_image_alt' => 'Traditional buildings and city scenery in Vietnam',
                'intro_image_caption' => 'Made for your kind of escape.',
                'highlights_eyebrow' => 'A JOURNEY WITH MORE TO DISCOVER',
                'highlights_headline' => 'One country. So many ways to explore.',
                'highlights_body' => 'Build a trip around the places, flavours and moments you want to take home.',
                'highlights' => [
                    0 => [
                        'title' => 'History & heritage',
                        'body' => 'Wander old quarters, visit historic landmarks and discover regional traditions.',
                    ],
                    1 => [
                        'title' => 'City to coastline',
                        'body' => 'Bring together lively cities, mountain scenery, bays and the coast.',
                    ],
                    2 => [
                        'title' => 'Travel at your pace',
                        'body' => 'Choose a relaxed city break or connect a few regions into one private journey.',
                    ],
                ],
                'destinations_eyebrow' => 'A FEW PLACES TO BEGIN',
                'destinations_headline' => 'Where will Vietnam take you?',
                'destinations_body' => 'Start with a place that speaks to you. We can help you explore the details.',
                'slides' => [
                    0 => [
                        'image' => 'https://images.unsplash.com/photo-1504457047772-27faf1c00561?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Historic city scenery in Hanoi',
                        'eyebrow' => 'NORTHERN VIETNAM',
                        'title' => 'Hanoi',
                        'body' => 'Discover a characterful old quarter, lakeside walks and a rich food culture.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                    1 => [
                        'image' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Limestone islands rising from the sea',
                        'eyebrow' => 'LANDSCAPES & BAY',
                        'title' => 'Ha Long Bay',
                        'body' => 'Take in striking limestone scenery and time on the water in the north.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                    2 => [
                        'image' => 'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Historic streets in central Vietnam',
                        'eyebrow' => 'CENTRAL VIETNAM',
                        'title' => 'Da Nang & Hoi An',
                        'body' => 'Pair an easy coastal stay with lantern-lit lanes and historic architecture.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                    3 => [
                        'image' => 'https://images.unsplash.com/photo-1583417319070-4a69db38a482?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'City street in Ho Chi Minh City',
                        'eyebrow' => 'SOUTHERN VIETNAM',
                        'title' => 'Ho Chi Minh City',
                        'body' => 'Explore a dynamic city with historic sights, lively markets and local cafés.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                    4 => [
                        'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Green river landscape in Vietnam',
                        'eyebrow' => 'RIVER & COUNTRYSIDE',
                        'title' => 'Mekong Delta',
                        'body' => 'Slow down among waterways, green landscapes and riverside communities.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                ],
                'contact_eyebrow' => 'LET’S PLAN YOUR VIETNAM GETAWAY',
                'contact_headline' => 'Tell us what you would love to discover.',
                'contact_body' => 'Share your preferred places, travel dates and the kind of trip you have in mind. Muslim-friendly meals and time for prayer can be included when requested.',
                'contact_cta_label' => 'Enquire on WhatsApp',
                'contact_href' => 'https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20plan%20a%20private%20trip%20to%20Vietnam.',
            ],
            [
                'slug' => 'philippines',
                'name' => 'Philippines',
                'meta_title' => 'Philippines Private Tours | Smart Muslim by Roystar Travel & Tours',
                'meta_description' => 'Discover the Philippines\' islands, heritage and tropical coastlines with a private journey planned around you.',
                'hero_eyebrow' => 'PRIVATE JOURNEYS THROUGH THE PHILIPPINES',
                'hero_headline' => 'A thousand island dreams.',
                'hero_highlight' => 'One trip, your way.',
                'hero_body' => 'From Manila’s historic streets to the white sands of Boracay and Palawan, discover the Philippines at your own pace with a private journey planned around you.',
                'hero_cta_label' => 'Plan your Philippines trip',
                'hero_note' => 'ISLANDS · HERITAGE · WARM HOSPITALITY',
                'hero_image' => 'https://images.unsplash.com/photo-1518509562904-e7ef99cdcc86?auto=format&fit=crop&w=2000&q=90',
                'intro_eyebrow' => 'WELCOME TO THE PHILIPPINES',
                'intro_headline' => 'Find your own rhythm across',
                'intro_highlight' => 'the islands.',
                'intro_body' => 'The Philippines brings together a rich local culture, welcoming communities and island scenery across more than 7,000 islands. Whether you imagine a city stay, a beach escape or both, our team can help shape a private itinerary to suit your travel style.',
                'intro_link_label' => 'Explore places to discover',
                'intro_image' => 'https://images.unsplash.com/photo-1518509562904-e7ef99cdcc86?auto=format&fit=crop&w=1000&q=85',
                'intro_image_alt' => 'Tropical island and clear blue water in the Philippines',
                'intro_image_caption' => 'Made for your kind of escape.',
                'highlights_eyebrow' => 'A JOURNEY WITH MORE TO DISCOVER',
                'highlights_headline' => 'Island time, with room to explore.',
                'highlights_body' => 'Build a trip around the scenery, culture and moments you want to take home.',
                'highlights' => [
                    0 => [
                        'title' => 'Island landscapes',
                        'body' => 'Discover beautiful beaches, clear waters and striking natural scenery.',
                    ],
                    1 => [
                        'title' => 'Culture & heritage',
                        'body' => 'Explore historic landmarks, local traditions and the character of each island.',
                    ],
                    2 => [
                        'title' => 'Travel at your pace',
                        'body' => 'Choose a relaxing island stay or combine a few stops in one private journey.',
                    ],
                ],
                'destinations_eyebrow' => 'A FEW PLACES TO BEGIN',
                'destinations_headline' => 'Where will the Philippines take you?',
                'destinations_body' => 'Start with a place that speaks to you. We can help you explore the details.',
                'slides' => [
                    0 => [
                        'image' => 'https://images.unsplash.com/photo-1518509562904-e7ef99cdcc86?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Island scenery and turquoise water',
                        'eyebrow' => 'ISLAND ESCAPE',
                        'title' => 'Boracay',
                        'body' => 'Spend time by the sea, with soft sands, clear water and a relaxed island pace.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                    1 => [
                        'image' => 'https://images.unsplash.com/photo-1518509562904-e7ef99cdcc86?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Limestone formations and blue water in Palawan',
                        'eyebrow' => 'NATURE & COAST',
                        'title' => 'Palawan',
                        'body' => 'Discover limestone scenery, quiet lagoons and beautiful coastal landscapes.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                    2 => [
                        'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Green countryside and mountain landscape',
                        'eyebrow' => 'CITY & HERITAGE',
                        'title' => 'Manila',
                        'body' => 'Explore a vibrant capital with historic districts, local markets and city life.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                    3 => [
                        'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Sunny tropical beach in Cebu',
                        'eyebrow' => 'ISLAND ADVENTURE',
                        'title' => 'Cebu',
                        'body' => 'Pair city discoveries with waterfalls, coastlines and easy island excursions.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                    4 => [
                        'image' => 'https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=900&q=85',
                        'alt' => 'Clear blue water along a tropical island',
                        'eyebrow' => 'ISLAND & NATURE',
                        'title' => 'Bohol',
                        'body' => 'Enjoy a slower island stay with countryside views, beaches and local culture.',
                        'link_label' => '',
                        'link_url' => '',
                    ],
                ],
                'contact_eyebrow' => 'LET’S PLAN YOUR PHILIPPINES GETAWAY',
                'contact_headline' => 'Tell us what you would love to discover.',
                'contact_body' => 'Share your preferred places, travel dates and the kind of trip you have in mind. Muslim-friendly meals and time for prayer can be included when requested.',
                'contact_cta_label' => 'Enquire on WhatsApp',
                'contact_href' => 'https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20plan%20a%20private%20trip%20to%20the%20Philippines.',
            ],
        ];
    }
};
