<?php

use App\Models\ChinaMuslimPackage;
use Illuminate\Database\Migrations\Migration;

/**
 * Fills in the day-by-day itinerary and package details for the Guangzhou – Guilin – Yangshuo
 * China Muslim card, which was added without them. A card that already has details is left alone.
 */
return new class extends Migration
{
    private const TITLE_KEYWORD = 'GUILIN YANGSHUO';

    public function up(): void
    {
        ChinaMuslimPackage::query()
            ->where('title', 'like', '%'.self::TITLE_KEYWORD.'%')
            ->where(fn ($query) => $query->whereNull('details')->orWhere('details', ''))
            ->update(['details' => $this->details()]);
    }

    public function down(): void
    {
        ChinaMuslimPackage::query()
            ->where('title', 'like', '%'.self::TITLE_KEYWORD.'%')
            ->where('details', $this->details())
            ->update(['details' => null]);
    }

    private function details(): string
    {
        return <<<'TEXT'
            DAY 01: KUALA LUMPUR ✈ GUANGZHOU – GUILIN (MOB,D)
            Gather at Kuala Lumpur International Airport for the flight to Guangzhou.
            Meet and greet with our local representative upon arrival.
            Take the high-speed train to Guilin.
            Pray at Chongxin Mosque.
            Mandatory tour: Two Rivers and Four Lakes night view (car tour).
            Explore the Sun & Moon Twin Pagodas, Guilin's landmark photo spot.
            Transfer to hotel for check-in.
            DAY 02: GUILIN (B,L)
            Breakfast at hotel.
            Mandatory tour: Fubo Mountain Scenic Area.
            Explore the Princess Iron Bell, Sword Testing Stone and Huanzhu Cave.
            Shopping stop: Silk Home Textile Museum.
            View Chuanshanyue Cave.
            See the Pagoda Mountain reflection on the river.
            Shopping stop: Tibetan Medicine Culture Museum.
            Transfer to hotel and rest.
            DAY 03: GUILIN (B,L,D)
            Breakfast at hotel.
            Shopping stop: jewellery and jade.
            Explore Qixing Park and Camel Mountain.
            Pray at Seven Star Park Mosque.
            Visit Elephant Trunk Hill Scenic Area.
            Tour the Traditional Chinese Medicine Culture Museum.
            Explore Rongshan Lake, the Nine-Curve Bridge, the Ancient South Gate and the Big Banyan Tree.
            Transfer to hotel and rest.
            DAY 04: GUILIN – YANGSHUO (B,L)
            Breakfast at hotel and check out.
            Visit Osmanthus Commune.
            Explore Silver Rock, a river crystal cave in Lipu.
            Mandatory tour: Li River bamboo rafting + osprey fishing + Three Sisters singing.
            Ten-Mile Gallery (car tour).
            View Moon Hill from a distance.
            Explore Yangshuo West Street.
            Transfer to hotel for check-in and rest.
            DAY 05: YANGSHUO – LIPU – GUANGZHOU (B,L,D)
            Breakfast at hotel and check out.
            Visit Maling Ancient Village.
            Pray at Guangzhou Xianxian Mosque.
            Explore Shamian Island.
            View the Canton Tower from a distance.
            Transfer to hotel for check-in and rest.
            DAY 06: GUANGZHOU ✈ KUALA LUMPUR (B,MOB)
            Breakfast at hotel and check out.
            Free time in Guangzhou.
            Transfer to the airport for your flight back to KUL.
            DAY 07: KUALA LUMPUR
            Arrive at KLIA. End of tour.
            Accommodation:
            4-star hotels or similar
            Guilin: Lan Ou Hotel or similar
            Yangshuo: Meihao Hotel or similar
            Guangzhou: Baiyun Licheng Ruixuan Hotel or similar
            Mandatory Tours:
            Two Rivers and Four Lakes night view (car tour)
            Sun and Moon Twin Pagodas
            Fubo Mountain Scenic Area
            Princess Iron Bell, Sword Testing Stone and Huanzhu Cave
            Li River bamboo rafting + osprey fishing + Three Sisters singing
            Shopping Stop:
            Silk
            Chinese Tibetan medicine
            Tong Ren Tang
            Jewellery
            Optional Tours:
            Impression Liu Sanjie
            Yangshuo Eternal Love show
            Included:
            International flight tickets
            23 kg baggage allowance
            Accommodation with breakfast
            Private transportation with driver
            Halal meals
            English-speaking guide throughout the tour
            Excluded:
            Personal expenses
            Travel insurance
            Tipping for local guide and driver
            Mandatory tour fees
            Optional tour fees
            Hotel surcharges during peak seasons or holidays
            Domestic flights for any additional sector
            Duration:
            7 days 5 nights
            Language:
            English
            Note: Itinerary, flights and hotels are subject to change due to weather, traffic or unforeseen circumstances.
            TEXT;
    }
};
