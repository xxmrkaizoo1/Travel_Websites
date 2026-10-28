<?php

use App\Models\ChinaMuslimPackage;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the Guangzhou – Guilin – Yangshuo, Kunming – Dali – Lijiang – Shangri-La and
 * Zhangjiajie – Changsha – Changde China Muslim cards. Their itineraries come from the
 * earlier itinerary migrations, which only fill in cards that already exist.
 * A card whose title is already taken is left alone.
 */
return new class extends Migration
{
    private const ITINERARY_MIGRATIONS = [
        '2026_10_27_030430_add_kunming_and_zhangjiajie_china_muslim_itineraries.php',
        '2026_10_27_033244_add_guilin_yangshuo_china_muslim_itinerary.php',
    ];

    public function up(): void
    {
        $position = ChinaMuslimPackage::query()->max('position') ?? 0;

        foreach ($this->packages() as $package) {
            ChinaMuslimPackage::query()->firstOrCreate(
                ['title' => $package['title']],
                [...$package, 'position' => ++$position],
            );
        }

        foreach (self::ITINERARY_MIGRATIONS as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    public function down(): void
    {
        ChinaMuslimPackage::query()
            ->whereIn('title', array_column($this->packages(), 'title'))
            ->delete();
    }

    /**
     * @return list<array{title: string, image_path: string, price: int, days: int, nights: int}>
     */
    private function packages(): array
    {
        return [
            [
                'title' => '7D5N GUANGZHOU GUILIN YANGSHUO MUSLIM TOURS',
                'image_path' => 'china-muslim/guilin-li-river.jpg',
                'price' => 2990,
                'days' => 7,
                'nights' => 5,
            ],
            [
                'title' => '9D7N KUNMING DALI LIJIANG SHANGRI-LA (2 WAY TRAIN) MUSLIM TOURS',
                'image_path' => 'china-muslim/lijiang-old-town.jpg',
                'price' => 0,
                'days' => 9,
                'nights' => 7,
            ],
            [
                'title' => '8D6N ZHANGJIAJIE CHANGSHA CHANGDE MUSLIM TOURS',
                'image_path' => 'china-muslim/zhangjiajie-forest-park.jpg',
                'price' => 0,
                'days' => 8,
                'nights' => 6,
            ],
        ];
    }
};
