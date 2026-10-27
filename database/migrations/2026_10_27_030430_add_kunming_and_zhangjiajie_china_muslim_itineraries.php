<?php

use App\Models\ChinaMuslimPackage;
use Illuminate\Database\Migrations\Migration;

/**
 * Fills in the day-by-day itinerary and package details for the Kunming – Dali – Lijiang – Shangri-La
 * and Zhangjiajie – Changsha – Changde China Muslim cards, which were added without them.
 * Cards that already have details are left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->itineraries() as $titleKeyword => $details) {
            ChinaMuslimPackage::query()
                ->where('title', 'like', "%{$titleKeyword}%")
                ->where(fn ($query) => $query->whereNull('details')->orWhere('details', ''))
                ->update(['details' => $details]);
        }
    }

    public function down(): void
    {
        foreach ($this->itineraries() as $titleKeyword => $details) {
            ChinaMuslimPackage::query()
                ->where('title', 'like', "%{$titleKeyword}%")
                ->where('details', $details)
                ->update(['details' => null]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function itineraries(): array
    {
        return [
            'SHANGRI-LA' => <<<'TEXT'
                DAY 01: KUALA LUMPUR ✈ KUNMING (MOB)
                Gather at KLIA 3 hours before departure.
                Flight from Kuala Lumpur to Kunming.
                DAY 02: KUNMING – DALI (B,L,D)
                Arrival at Kunming Changshui International Airport.
                Meet & greet by our local representative.
                Transfer to hotel, check in & rest. Breakfast at hotel.
                Take the high-speed train to Dali (the coach takes your luggage to the Dali hotel).
                Visit the Santorini of China (return shuttle buggy + tea break).
                Pray at South Gate Mosque.
                Explore Dali Ancient Town (shuttle buggy excluded) and Foreigners Street.
                Transfer to hotel for check-in and rest.
                DAY 03: DALI (B,L,D)
                Breakfast at hotel.
                Shopping stop: The Bai Family Compound (silver shop).
                Tour Xizhou Ancient Town (Corner Tower).
                Explore the Erhai Ecological Corridor (open-air music caravan, local dance + photo shoot).
                Transfer to hotel and rest.
                DAY 04: DALI – SHANGRI-LA (B,L,D)
                Breakfast at hotel and check out.
                Visit a Muslim village (home visit + making and tasting Rushan, Yunnan milk fan).
                Explore Napa Lake Wetland (viewing platform + casual coffee).
                Explore Dukezong Ancient Town (Turtle Mountain Park + the world's biggest prayer wheel).
                Transfer to hotel and rest.
                DAY 05: SHANGRI-LA – LIJIANG (B,L,D)
                Breakfast at hotel and check out.
                Shopping stop: Shengyu Tianxiang shop.
                Explore Tiger Leaping Gorge (escalator included).
                Visit Lijiang Ancient Town and explore Sifang Street.
                Visit Black Dragon Pool.
                Transfer to hotel for check-in and rest.
                DAY 06: LIJIANG (B,L,D)
                Breakfast at hotel.
                Visit Jade Dragon Snow Mountain Glacier Park (eco bus + cable car).
                Visit Gan Haizi Grassland.
                View White Water River + Blue Moon Valley (shuttle buggy).
                Watch Zhang Yimou's Impression Lijiang show.
                Transfer to hotel and rest.
                DAY 07: LIJIANG – KUNMING (B,L,D)
                Breakfast at hotel and check out.
                Take the high-speed train back to Kunming (the coach takes your luggage back).
                Shopping stop: jade shop.
                Visit the Zheng He Cultural Exchange Center (tea break + outlets).
                Explore Dounan Flower Market, Asia's largest market for cut flowers.
                Transfer to hotel for check-in and rest.
                DAY 08: KUNMING ✈ KUALA LUMPUR (B,L,D)
                After breakfast, enjoy a city tour of Kunming.
                Visit Naigu Stone Forest.
                Shopping stop: Astaxanthin.
                Visit & pray at Shuncheng Mosque.
                Explore Jinbi Archway, Nanping Pedestrian Street and Qianwang Old Street.
                After dinner, transfer to the airport for your flight back to Kuala Lumpur.
                DAY 09: KUALA LUMPUR (MOB)
                Early morning flight back to KUL.
                Arrive in Kuala Lumpur. Back to home sweet home!
                Accommodation:
                4-star hotels or similar
                Dali: M.Garden Hotel or similar
                Lijiang: Manbo Haoting Hotel or similar
                Shangri-La: Elong Hotel or similar
                Kunming: Radisson Hotel or Xinshengda Hotel or similar
                Shopping Stop:
                Silver shop
                Shengyu Tianxiang shop
                Jade shop
                Astaxanthin
                Optional Tours:
                Extra oxygen tank at Jade Dragon Snow Mountain: RMB 50 per person
                Miao ethnic traditional costume experience
                Included:
                20 kg checked baggage allowance
                Two-way high-speed train tickets
                4★ hotel accommodation with international breakfast
                Entrance tickets for included attractions
                Private air-conditioned coach
                Full halal meals
                English-speaking guide
                Excluded:
                Personal expenses
                Own travel insurance
                Optional tours and activities
                Compulsory sightseeing tour
                Tipping for local guide & driver
                Hotel surcharges during peak season or public holidays
                Additional domestic flight tickets
                Duration:
                9 days 7 nights
                Language:
                English
                TEXT,
            'ZHANGJIAJIE' => <<<'TEXT'
                DAY 01: KUALA LUMPUR ✈ CHANGSHA (MOB,L,D)
                Assemble at Kuala Lumpur International Airport for your flight to Changsha.
                After arriving in Changsha, visit Changsha Hanhui Village in Laodaohe Town, the only ethnic minority village in Changsha, home to the Hui and Han people.
                Transfer to hotel for check-in and rest.
                DAY 02: CHANGSHA – CHANGDE – ZHANGJIAJIE (B,L,D)
                Breakfast at hotel.
                Head to Changde to visit the Liuye Lake Scenic Area (boat cruise included).
                Tour Daxiaohe Street. After lunch, proceed to Zhangjiajie.
                Compulsory tour: the 72 Strange Buildings, the world's highest stilted building.
                Stroll through the Muslim Quarter (time for personal prayers).
                Transfer to hotel for check-in and rest.
                DAY 03: ZHANGJIAJIE (B,L,D)
                Breakfast at hotel.
                Visit the Tujia Scenic Park to admire the Tujia stilt houses.
                Compulsory tour: Tianmen Mountain National Forest Park, with the world's longest cable car and the glass skywalk.
                Optional (own expense): Tianmen Fox Fairy Show or Charming Xiangxi show.
                Transfer to hotel and rest.
                DAY 04: ZHANGJIAJIE (B,L,D)
                Breakfast at hotel.
                Ride the Bailong Elevator, the world's tallest outdoor elevator, up to Yuanjiajie.
                See the First Bridge under Heaven and Hallelujah Mountain, the inspiration for Avatar.
                Visit Tianzi Mountain and descend by cableway.
                Transfer to hotel and rest.
                DAY 05: ZHANGJIAJIE (B,L,D)
                Breakfast at hotel and check out.
                Visit Golden Whip Stream and the Ten-Mile Gallery (mini-train ride included).
                Visit the Zhangjiajie Grand Canyon Glass Bridge, the world's longest and highest fully transparent glass bridge (boat, elevator and VR experience included).
                Transfer to hotel and rest.
                DAY 06: ZHANGJIAJIE (B,L,D)
                Breakfast at hotel and check out.
                Visit Yellow Dragon Cave, famous for its massive stalactites.
                Visit Baofeng Lake, the "Holy Lake in the Sky".
                Transfer to hotel and rest.
                DAY 07: ZHANGJIAJIE – CHANGSHA ✈ KUALA LUMPUR (B,MOB)
                Breakfast at hotel and check out.
                Depart Zhangjiajie and head back to Changsha.
                Transfer to the airport for your flight to Kuala Lumpur.
                DAY 08: KUALA LUMPUR
                Arrive at KLIA. End of tour.
                Departure Dates:
                November 2026: 4, 16, 25
                December 2026: 3, 7, 10, 17, 18, 20, 21, 23, 31 (school holidays)
                Accommodation:
                4-star hotels or similar
                Changsha: Changsha Orange Hotel or similar
                Zhangjiajie: BORRMAN Hotel or similar
                Mandatory Tours:
                72 Strange Buildings
                Tianmen Mountain + cable car + Tianmen escalator + glass skywalk
                Shopping Stop:
                Jade
                Latex
                Tea
                Silk
                Optional Tours:
                Tianmen Fox Fairy Show
                Romantic Show of Xiangxi (Charming Xiangxi)
                Tujia Folk Garden
                Included:
                International flight tickets
                23 kg baggage allowance
                Accommodation with breakfast
                Private transportation with driver
                Halal meals
                Mandatory tour fees
                Tipping for local guide and driver
                English-speaking guide throughout the tour
                Excluded:
                Personal expenses
                Travel insurance
                Optional tour fees
                Hotel surcharges during peak seasons or holidays
                Domestic flights for any additional sector
                Duration:
                8 days 6 nights
                Language:
                English
                TEXT,
        ];
    }
};
