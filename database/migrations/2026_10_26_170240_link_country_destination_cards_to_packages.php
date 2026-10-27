<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Gives every destination card on the Thailand, Vietnam and Philippines pages a link: to its
 * package page when one exists, otherwise to a WhatsApp enquiry about that destination.
 * Cards that already have a link are left as they are.
 */
return new class extends Migration
{
    /**
     * Package page slug for each card title; null means there is no package page yet.
     *
     * @var array<string, array<string, string|null>>
     */
    private const PACKAGES = [
        'thailand' => [
            'Bangkok' => 'bangkok',
            'Chiang Mai & Chiang Rai' => 'chiang-mai-chiang-rai',
            'Pattaya' => 'pattaya',
            'Phuket' => null,
            'Krabi' => 'krabi',
            'Kanchanaburi' => 'kanchanaburi',
        ],
        'vietnam' => [
            'Hanoi' => 'hanoi',
            'Ha Long Bay' => null,
            'Da Nang & Hoi An' => 'da-nang',
            'Ho Chi Minh City' => 'ho-chi-minh',
            'Mekong Delta' => null,
        ],
        'philippines' => [
            'Boracay' => 'boracay',
            'Palawan' => 'palawan',
            'Manila' => 'manila',
            'Cebu' => 'cebu',
            'Bohol' => null,
        ],
    ];

    public function up(): void
    {
        foreach (self::PACKAGES as $countrySlug => $packages) {
            $this->updateSlides($countrySlug, function (array $slide) use ($packages): array {
                $title = $slide['title'] ?? '';

                if (filled($slide['link_url'] ?? '') || ! array_key_exists($title, $packages)) {
                    return $slide;
                }

                $packageSlug = $packages[$title];

                if ($packageSlug !== null) {
                    return [...$slide, 'link_url' => "/tour/{$packageSlug}", 'link_label' => "Explore {$title} tour packages"];
                }

                return [
                    ...$slide,
                    'link_url' => 'https://wa.me/60165500590?text='.rawurlencode("Hi, I'd like to ask about {$title} tour packages."),
                    'link_label' => "Ask about {$title} packages",
                ];
            });
        }
    }

    public function down(): void
    {
        foreach (self::PACKAGES as $countrySlug => $packages) {
            $this->updateSlides($countrySlug, function (array $slide) use ($packages): array {
                $title = $slide['title'] ?? '';
                $added = array_key_exists($title, $packages) && in_array($slide['link_label'] ?? '', ["Explore {$title} tour packages", "Ask about {$title} packages"], true);

                return $added ? [...$slide, 'link_url' => '', 'link_label' => ''] : $slide;
            });
        }
    }

    private function updateSlides(string $countrySlug, callable $update): void
    {
        $page = DB::table('tour_package_pages')->where('slug', $countrySlug)->first(['id', 'slides']);

        if ($page === null) {
            return;
        }

        $slides = array_map($update, json_decode($page->slides ?? '[]', true) ?: []);

        DB::table('tour_package_pages')->where('id', $page->id)->update(['slides' => json_encode($slides), 'updated_at' => now()]);
    }
};
