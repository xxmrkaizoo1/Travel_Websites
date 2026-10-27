<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds real client trip photos to the front of the Company Trip (MICE) gallery, newest trip first.
 * The photos already in the gallery move after them. Photos that are already there are skipped.
 */
return new class extends Migration
{
    /**
     * @var array<int, array{0: string, 1: string}>
     */
    private array $photos = [
        ['images/mice-gallery/uitm-guangzhou-2026.jpg', 'UiTM · Guangzhou, Aug 2026'],
        ['images/mice-gallery/usim-perth-2026.jpg', 'USIM · Perth, Aug 2026'],
        ['images/mice-gallery/egreen-guangzhou-2026.jpg', 'eGreen · Guangzhou, May 2026'],
        ['images/mice-gallery/iktisas-beijing-2025.jpg', 'Iktisas Planners · Beijing, Dec 2025'],
        ['images/mice-gallery/mbmb-kota-kinabalu-2025.jpg', 'MBMB · Kota Kinabalu, Dec 2025'],
        ['images/mice-gallery/meinhardt-beijing-2025.jpg', 'Meinhardt · Beijing, Sep 2025'],
        ['images/mice-gallery/cosmo-scm-medan-2025.jpg', 'Cosmo SCM · Medan, Jun 2025'],
        ['images/mice-gallery/perodua-xian-2025.jpg', 'Perodua Rangka Raya · Xi’an, Jun 2025'],
        ['images/mice-gallery/henry-goh-medan-toba-2025.jpg', 'Henry Goh & Co · Medan & Toba, Jun 2025'],
        ['images/mice-gallery/macm-shanghai-2024.jpg', 'MACM · Shanghai, Dec 2024'],
        ['images/mice-gallery/perodua-kunming-2024.jpg', 'Perodua · Kunming, Nov 2024'],
        ['images/mice-gallery/salcon-istanbul-2024.jpg', 'Salcon · Istanbul, May 2024'],
        ['images/mice-gallery/duopharma-seoul-2024.jpg', 'Duopharma · Seoul, Jan 2024'],
        ['images/mice-gallery/ayuh-doha-qatar-2024.jpg', 'AFC Asian Cup · Doha, Jan 2024'],
        ['images/mice-gallery/perodua-bali-2023.jpg', 'Perodua · Bali, Nov 2023'],
        ['images/mice-gallery/perodua-taipei-2023.jpg', 'Perodua Rangka Raya · Taipei, Sep 2023'],
        ['images/mice-gallery/iium-sydney-melbourne-2023.jpg', 'IIUM · Sydney & Melbourne, Aug 2023'],
        ['images/mice-gallery/harta-yogyakarta-2023.jpg', 'HARTA · Yogyakarta, Jul 2023'],
    ];

    public function up(): void
    {
        $existing = DB::table('mice_gallery_images')->pluck('image')->all();
        $newPhotos = array_values(array_filter($this->photos, fn (array $photo): bool => ! in_array($photo[0], $existing, true)));

        if ($newPhotos === []) {
            return;
        }

        DB::table('mice_gallery_images')->increment('position', count($newPhotos));

        foreach ($newPhotos as $index => [$image, $caption]) {
            DB::table('mice_gallery_images')->insert([
                'image' => $image,
                'caption' => $caption,
                'position' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('mice_gallery_images')->whereIn('image', array_column($this->photos, 0))->delete();
    }
};
