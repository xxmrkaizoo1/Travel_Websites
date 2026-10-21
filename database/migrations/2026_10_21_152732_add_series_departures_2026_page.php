<?php

use App\Models\TourPackagePage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const SLUG = 'series-departures-2026';

    public function up(): void
    {
        if (TourPackagePage::query()->where('slug', self::SLUG)->exists()) {
            return;
        }

        $page = new TourPackagePage(array_replace(
            TourPackagePage::starterContent(TourPackagePage::KIND_INTRODUCTION, 'Series Departures 2026'),
            $this->content(),
        ));
        $page->slug = self::SLUG;
        $page->parent_slug = null;
        $page->is_custom = true;
        $page->save();
    }

    public function down(): void
    {
        TourPackagePage::query()->where('slug', self::SLUG)->delete();
    }

    /**
     * @return array<string, mixed>
     */
    private function content(): array
    {
        $whatsapp = 'https://wa.me/60165500590?text='.rawurlencode("Hi, I'd like to know more about the Series Departures 2026.");

        return [
            'meta_title' => 'Series Departures 2026 | Smart Muslim by Roystar Travel & Tours',
            'meta_description' => 'Join our 2026 group series departures to Seoul, Kunming and Medan. Shared costs, a fully arranged itinerary and an experienced tour guide.',
            'hero_eyebrow' => 'GROUP TOURS · 2026',
            'hero_headline' => 'Travel together.',
            'hero_highlight' => 'Series Departures 2026.',
            'hero_body' => 'Fixed-date group departures with the itinerary, guide and transport all arranged for you.',
            'hero_cta_label' => 'Ask about departures',
            'hero_note' => 'SEOUL · KUNMING · MEDAN',
            'hero_image' => $this->commons('Exterior of Sultan Ahmed I Mosque in Istanbul, Turkey 002.jpg', 2000),
            'intro_eyebrow' => 'WHY TRAVEL IN A GROUP',
            'intro_headline' => 'Great journeys are better',
            'intro_highlight' => 'shared.',
            'intro_body' => 'Group tour packages are usually cheaper because the overall cost is shared among the participants. They are ideal for those who want to travel on a lower budget. Additionally, the entire itinerary is arranged by the travel agency, so participants don\'t have to worry about planning it themselves. These tours are also perfect for those who enjoy socializing, as there\'s an opportunity to meet other travelers. You will also be accompanied by an experienced tour guide who will provide information about the interesting places visited. In terms of safety, traveling in a group is more secure, and the risk of getting lost is low.',
            'intro_link_label' => 'See the 2026 destinations',
            'intro_image' => $this->commons('Seoul city skyline at night from Namsan Mountain (49175035266).jpg', 1400),
            'intro_image_alt' => 'Seoul city skyline at night',
            'intro_image_caption' => 'Your next group adventure.',
            'highlights_eyebrow' => 'THE GROUP TOUR ADVANTAGE',
            'highlights_headline' => 'Why join a series departure?',
            'highlights_body' => 'Four good reasons to travel with a group.',
            'highlights' => [
                ['title' => 'Lower cost', 'body' => 'The overall cost is shared among the participants, so group packages are usually cheaper.'],
                ['title' => 'Everything arranged', 'body' => 'The whole itinerary is planned by the travel agency, so you do not have to organise a thing.'],
                ['title' => 'Meet fellow travellers', 'body' => 'Enjoy the chance to socialise and meet new people, with an experienced guide sharing the stories of each place.'],
                ['title' => 'Safer together', 'body' => 'Travelling in a group is more secure and the risk of getting lost is low.'],
            ],
            'destinations_eyebrow' => '2026 DESTINATIONS',
            'destinations_headline' => 'Where will you go?',
            'destinations_body' => 'Three destinations, each with its own culture, food and scenery.',
            'slides' => [],
            'package_data' => ['series' => $this->destinations()],
            'contact_eyebrow' => 'JOIN A DEPARTURE',
            'contact_headline' => 'Ready to travel with us?',
            'contact_body' => 'Tell us which destination interests you and we will share the available 2026 dates and prices.',
            'contact_cta_label' => 'Enquire on WhatsApp',
            'contact_href' => $whatsapp,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function destinations(): array
    {
        return [
            [
                'name' => 'Seoul',
                'country' => 'South Korea',
                'flag' => 'images/series/flag-kr.png',
                'tagline' => 'Palaces, street food and four beautiful seasons.',
                'intro' => 'Traveling in South Korea is really fun! The country is full of interesting places, delicious food, and unique culture.',
                'advantages' => [
                    ['icon' => '🏯', 'title' => 'Fascinating Culture & History', 'body' => 'Visit old palaces like Gyeongbokgung and wear a hanbok (traditional clothing). It is like stepping into a Korean drama!'],
                    ['icon' => '🌸', 'title' => 'Beautiful Four Seasons Scenery', 'body' => 'Go in spring to see cherry blossoms, or in winter to play in the snow and go skiing.'],
                    ['icon' => '🍱', 'title' => 'Delicious Korean Food', 'body' => 'Try kimchi, tteokbokki, bibimbap and more. Do not forget the street food in Myeongdong!'],
                    ['icon' => '🛍️', 'title' => 'Shopping Paradise', 'body' => 'Skincare, trendy fashion and more. Popular places: Myeongdong, Hongdae and Dongdaemun.'],
                ],
                'gallery' => [
                    ['image' => $this->commons('Front view of the Imperial Throne Hall Geunjeongjeon at Gyeongbokgung Palace with blue sky in Seoul.jpg'), 'alt' => 'Gyeongbokgung Palace', 'caption' => 'Gyeongbokgung Palace'],
                    ['image' => $this->commons('N Seoul Tower view 2.jpg'), 'alt' => 'N Seoul Tower', 'caption' => 'N Seoul Tower'],
                    ['image' => $this->commons('Myeongdong night market seoul 4.jpg'), 'alt' => 'Myeongdong night market', 'caption' => 'Myeongdong'],
                    ['image' => $this->commons('Han River, Banpo Bridge (46).jpg'), 'alt' => 'Han River and Banpo Bridge', 'caption' => 'Han River'],
                    ['image' => $this->commons('Starfield library 4.jpg'), 'alt' => 'Starfield Library', 'caption' => 'Starfield Library'],
                ],
            ],
            [
                'name' => 'Kunming',
                'country' => 'China',
                'flag' => 'images/series/flag-cn.png',
                'tagline' => 'The City of Eternal Spring.',
                'intro' => 'Vacationing in Kunming is really fun! This city is famous for its pleasant weather, beautiful natural scenery, and delicious food.',
                'advantages' => [
                    ['icon' => '🏞️', 'title' => 'Comfortable Weather All Year Round', 'body' => 'Kunming is known as the "City of Eternal Spring". No matter the season, you can explore the city comfortably.'],
                    ['icon' => '🌸', 'title' => 'Beautiful Natural Scenery', 'body' => 'Visit Green Lake Park, the unique and stunning Stone Forest, and more. Perfect for those who love taking photos.'],
                    ['icon' => '🍜', 'title' => 'Delicious Local Food', 'body' => 'Enjoy Yunnan dishes like crossing-the-bridge noodles and fresh vegetables. Do not miss the street food at the night markets.'],
                    ['icon' => '🛍️', 'title' => 'Shopping Spots & Night Markets', 'body' => 'Kunming\'s flower markets and night markets are full of handicrafts, spices and unique souvenirs.'],
                ],
                'gallery' => [
                    ['image' => $this->commons('55524-Kunming-Green-Lake-Park.jpg'), 'alt' => 'Green Lake Park, Kunming', 'caption' => 'Green Lake Park'],
                    ['image' => $this->commons('Shilin Yunnan China Shilin-Stone-Forest-01.jpg'), 'alt' => 'Stone Forest', 'caption' => 'Stone Forest'],
                    ['image' => $this->commons('Lijiang Yunnan China Jade-Dragon-Snow-Mountain-01.jpg'), 'alt' => 'Jade Dragon Snow Mountain', 'caption' => 'Jade Dragon Snow Mountain'],
                    ['image' => $this->commons('Lijiang Yunnan China-Evening-atmosphere-in-old-town-Lijiang-01.jpg'), 'alt' => 'Lijiang old town', 'caption' => 'Lijiang Old Town'],
                    ['image' => $this->commons('20260222 Kunming Dianchi Lake, looking at the West Mountain from the East Bank.jpg'), 'alt' => 'Dian Lake', 'caption' => 'Dian Lake'],
                ],
            ],
            [
                'name' => 'Medan',
                'country' => 'Indonesia',
                'flag' => 'images/series/flag-id.png',
                'tagline' => 'Lake Toba, Batak culture and bold flavours.',
                'intro' => 'Taking a trip to Medan is so much fun! The capital of North Sumatra is rich in natural beauty, unique culture, delicious food, and friendly locals.',
                'advantages' => [
                    ['icon' => '🏞️', 'title' => 'Stunning Natural Scenery', 'body' => 'Medan is the gateway to Lake Toba, the largest volcanic lake in the world. Visit Samosir Island, Sipiso-piso Waterfall and the cool mountains of Berastagi.'],
                    ['icon' => '🌤️', 'title' => 'Cool & Comfortable Weather', 'body' => 'In highland areas like Berastagi and Samosir the weather is pleasant and refreshing, perfect for relaxing away from the big cities.'],
                    ['icon' => '🥘', 'title' => 'Traditional Batak & Malay Cuisine', 'body' => 'Try nasi padang, soto medan, ayam penyet, grilled Toba fish and Batak dishes like arsik and saksang. Spicy, bold and delicious!'],
                    ['icon' => '🛍️', 'title' => 'Shopping & Souvenirs', 'body' => 'Stop by Pasar Ikan Lama, Pajak Central or Merdeka Walk for ulos cloth, Sumatran coffee, layered cakes, bika ambon and more.'],
                ],
                'gallery' => [
                    ['image' => $this->commons('Sipiso-piso Waterfalls.jpg'), 'alt' => 'Sipiso-piso Waterfall', 'caption' => 'Sipiso-piso Waterfall'],
                    ['image' => $this->commons('Sunset on lake toba.jpg'), 'alt' => 'Sunset at Lake Toba', 'caption' => 'Lake Toba'],
                    ['image' => $this->commons('Maimun Palace Medan Indonesia.jpg'), 'alt' => 'Maimun Palace', 'caption' => 'Maimun Palace'],
                    ['image' => $this->commons('Great mosque in Medan cropped.jpg'), 'alt' => 'Great Mosque of Medan', 'caption' => 'Great Mosque of Medan'],
                    ['image' => $this->commons('Batak Toba House in Samosir Island 01.JPG'), 'alt' => 'Batak house on Samosir Island', 'caption' => 'Samosir Island'],
                ],
            ],
        ];
    }

    private function commons(string $file): string
    {
        $images = [
            'Exterior of Sultan Ahmed I Mosque in Istanbul, Turkey 002.jpg' => 'hero',
            'Seoul city skyline at night from Namsan Mountain (49175035266).jpg' => 'intro',
            'Front view of the Imperial Throne Hall Geunjeongjeon at Gyeongbokgung Palace with blue sky in Seoul.jpg' => 'seoul-1',
            'N Seoul Tower view 2.jpg' => 'seoul-2',
            'Myeongdong night market seoul 4.jpg' => 'seoul-3',
            'Han River, Banpo Bridge (46).jpg' => 'seoul-4',
            'Starfield library 4.jpg' => 'seoul-5',
            '55524-Kunming-Green-Lake-Park.jpg' => 'kunming-1',
            'Shilin Yunnan China Shilin-Stone-Forest-01.jpg' => 'kunming-2',
            'Lijiang Yunnan China Jade-Dragon-Snow-Mountain-01.jpg' => 'kunming-3',
            'Lijiang Yunnan China-Evening-atmosphere-in-old-town-Lijiang-01.jpg' => 'kunming-4',
            '20260222 Kunming Dianchi Lake, looking at the West Mountain from the East Bank.jpg' => 'kunming-5',
            'Sipiso-piso Waterfalls.jpg' => 'medan-1',
            'Sunset on lake toba.jpg' => 'medan-2',
            'Maimun Palace Medan Indonesia.jpg' => 'medan-3',
            'Great mosque in Medan cropped.jpg' => 'medan-4',
            'Batak Toba House in Samosir Island 01.JPG' => 'medan-5',
        ];

        return 'images/series/'.$images[$file].'.jpg';
    }
};
