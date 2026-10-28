<?php

use App\Models\IslandDestination;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the Maafushi Island (Maldives) and Krabi Island (Thailand) spotlights to the Island &
 * Honeymoon page, after Bali. A destination whose slug is already taken is left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        $position = IslandDestination::query()->max('position') ?? 0;

        foreach ($this->destinations() as $destination) {
            IslandDestination::query()->firstOrCreate(
                ['slug' => $destination['slug']],
                [...$destination, 'position' => ++$position],
            );
        }
    }

    public function down(): void
    {
        IslandDestination::query()
            ->whereIn('slug', array_column($this->destinations(), 'slug'))
            ->delete();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function destinations(): array
    {
        $photo = fn (string $id, int $width): string => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w={$width}&q=85";

        return [
            [
                'slug' => 'maafushi-maldives',
                'name' => 'Maafushi Island',
                'country' => 'Maldives',
                'description' => 'Maafushi, a small island in the Maldives, is increasingly recognized as a budget-friendly holiday destination offering an extraordinary tropical experience. Surrounded by clear blue waters, soft white sand, and a tourist-friendly community, Maafushi is ideal for honeymooners, backpackers, or solo travelers seeking tranquility and natural beauty.',
                'known_as' => 'The Maldives on a budget',
                'perfect_for' => 'Honeymooners, backpackers & solo travelers',
                'benefits_heading' => 'Advantages of Vacationing in Maafushi',
                'benefits' => [
                    ['icon' => '🌅', 'title' => 'Stunning Blue Sea & Beaches', 'body' => 'The beaches in Maafushi are like a painting – crystal-clear turquoise waters perfect for swimming, sunbathing, or simply relaxing while watching the sunset. With no motor vehicles on the island, the atmosphere is peaceful and pollution-free.'],
                    ['icon' => '🐠', 'title' => 'Exciting Water Activities', 'body' => 'From snorkeling with rays and turtles, diving in coral reefs, to island-hopping tours and sandbank picnics – Maafushi is a paradise for sea lovers and active travelers. You can also try jet skiing or parasailing for an adrenaline rush!'],
                    ['icon' => '🕌', 'title' => 'Muslim-Friendly & Welcoming Community', 'body' => 'Maafushi is a local island, making it very suitable for Muslim travelers – there are mosques, halal restaurants, and a friendly local community that respects cultural norms. Experiencing a local stay gives a closer look at the Maldivian way of life.'],
                    ['icon' => '🍤', 'title' => 'Fresh Seafood & Local Cuisine', 'body' => 'Enjoy a variety of dishes made from fresh fish, coconut, and tropical spices. Many restaurants offer halal and vegetarian menus at reasonable prices. There are also modern seaside cafés for evening hangouts.'],
                    ['icon' => '🏨', 'title' => 'Accommodation from Budget to Comfortable', 'body' => 'Although the Maldives is famous for luxury resorts, Maafushi offers budget-friendly alternatives – from cozy guesthouses with excellent service to boutique hotels with sea views. Perfect for those who want to experience the Maldives without spending a fortune.'],
                ],
                'feature_image' => $photo('1590523277543-a94d2e4eb00b', 1200),
                'feature_image_alt' => 'Overwater villas above a turquoise lagoon in the Maldives',
                'feature_caption' => 'Turquoise water, slow days',
                'gallery' => [
                    ['image' => $photo('1514282401047-d79a71a590e8', 1600), 'alt' => 'Aerial view of overwater villas on a Maldives lagoon', 'eyebrow' => 'Discover the Maldives', 'title' => 'Island & lagoon views'],
                    ['image' => $photo('1540202404-a2f29016b523', 1600), 'alt' => 'White sandbank surrounded by clear shallow water', 'eyebrow' => 'Escape to a sandbank', 'title' => 'Sandbank picnics'],
                    ['image' => $photo('1512100356356-de1b84283e18', 1600), 'alt' => 'Seaplane beside a palm-lined white-sand beach', 'eyebrow' => 'Hop between islands', 'title' => 'Island-hopping tours'],
                    ['image' => $photo('1544550581-5f7ceaf7f992', 1600), 'alt' => 'Wooden jetty stretching over turquoise water', 'eyebrow' => 'Walk out over the sea', 'title' => 'Jetties & lagoons'],
                    ['image' => $photo('1586861635167-e5223aadc9fe', 1600), 'alt' => 'Floating breakfast trays in a sea-view pool', 'eyebrow' => 'Slow down and settle in', 'title' => 'Time to unwind'],
                ],
            ],
            [
                'slug' => 'krabi-thailand',
                'name' => 'Krabi Island',
                'country' => 'Thailand',
                'description' => 'Krabi, a tropical region located in southern Thailand, is becoming increasingly popular as a budget-friendly holiday destination offering stunning natural beauty. Surrounded by limestone cliffs, pristine white beaches, and turquoise-green seas, Krabi is perfect for honeymooners, backpackers, families, or solo travelers looking to escape the hustle and bustle of city life.',
                'known_as' => 'Land of limestone cliffs',
                'perfect_for' => 'Honeymooners, families & solo travelers',
                'benefits_heading' => 'Advantages of Vacationing in Krabi',
                'benefits' => [
                    ['icon' => '🌅', 'title' => 'Beautiful Beaches & Breathtaking Scenery', 'body' => 'Beaches in Krabi such as Ao Nang, Railay, and Tonsai are famous for clear waters, soft white sand, and unique limestone cliffs. The atmosphere is peaceful, ideal for swimming, relaxing, or enjoying beautiful sunsets. Krabi is truly an ideal choice for nature lovers and those seeking tranquility.'],
                    ['icon' => '🌊', 'title' => 'Water Activities & Nature Adventures', 'body' => 'Krabi offers a variety of exciting activities like island hopping to Phi Phi and Hong Islands, snorkeling, diving, kayaking in mangroves, and rock climbing in Railay. It’s a perfect destination for outdoor enthusiasts and adventure seekers.'],
                    ['icon' => '🕌', 'title' => 'Muslim-Friendly & Easy Access to Halal Food', 'body' => 'Krabi is Muslim-friendly, with a local Islamic community, accessible mosques, and many halal restaurants. Muslim travelers can enjoy their holiday comfortably without worrying about food or prayer.'],
                    ['icon' => '🍲', 'title' => 'Local Cuisine & Fresh Seafood', 'body' => 'Krabi offers a variety of delicious Thai dishes such as tom yum, steamed fish with lime, turmeric fried squid, and mango sticky rice. Meals can be enjoyed at beachside restaurants or night markets, with affordable prices and authentic flavors.'],
                    ['icon' => '🏨', 'title' => 'Accommodation for All Budgets', 'body' => 'Krabi provides a range of accommodations, from budget guesthouses in Ao Nang to exclusive villas and luxury beachfront resorts. Suitable for everyone – honeymooners, family vacations, or solo travelers – without breaking the bank.'],
                ],
                'feature_image' => $photo('1552465011-b4e21bf6e79a', 1200),
                'feature_image_alt' => 'Longtail boats on a Krabi beach beneath limestone cliffs',
                'feature_caption' => 'Cliffs, coves and calm seas',
                'gallery' => [
                    ['image' => $photo('1519451241324-20b4ea2c4220', 1600), 'alt' => 'Longtail boat between towering limestone cliffs', 'eyebrow' => 'Discover Krabi', 'title' => 'Limestone lagoons'],
                    ['image' => $photo('1589394815804-964ed0be2eb5', 1600), 'alt' => 'Turquoise bay enclosed by green cliffs in the Phi Phi Islands', 'eyebrow' => 'Hop between islands', 'title' => 'Phi Phi & Hong Islands'],
                    ['image' => $photo('1537956965359-7573183d1f57', 1600), 'alt' => 'Aerial view of a sheltered white-sand cove', 'eyebrow' => 'Find your cove', 'title' => 'Snorkeling & swimming'],
                    ['image' => $photo('1506665531195-3566af2b4dfa', 1600), 'alt' => 'Island viewpoint over a bay at sunset', 'eyebrow' => 'Take the scenic route', 'title' => 'Sunset viewpoints'],
                    ['image' => $photo('1544161515-4ab6ce6db874', 1600), 'alt' => 'Relaxing oil massage at a spa', 'eyebrow' => 'Slow down and settle in', 'title' => 'Thai spa & massage'],
                ],
            ],
        ];
    }
};
