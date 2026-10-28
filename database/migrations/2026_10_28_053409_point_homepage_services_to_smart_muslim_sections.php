<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Turns the four "Our services" cards on the Smart Muslim homepage into the four Smart Muslim sections:
 * Tour Package (Private), Tour Package (Series), China Muslim and Company Trip (MICE), each linking to its page.
 */
return new class extends Migration
{
    private const PRIVATE_FALLBACK_IMAGE = 'https://images.unsplash.com/photo-1528181304800-259b08848526?auto=format&fit=crop&w=900&q=85';

    private const SERIES_FALLBACK_IMAGE = 'images/series/hero.jpg';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->replaceServices([
            'rest-of-the-world' => [
                'slug' => 'tour-package-private',
                'design_key' => 'service-private',
                'position' => 1,
                'title' => 'Tour Package (Private)',
                'overlay_label' => "TOUR PACKAGE\n(PRIVATE)",
                'button_label' => 'Click Here!',
                'href' => '/tour-package-private',
                'image_path' => $this->sectionPhoto(DB::table('private_tours_pages')->value('hero_image'), self::PRIVATE_FALLBACK_IMAGE),
                'image_alt' => 'An ornate temple under a bright blue sky',
            ],
            'ticketing' => [
                'slug' => 'tour-package-series',
                'design_key' => 'service-series',
                'position' => 2,
                'title' => 'Tour Package (Series)',
                'overlay_label' => "TOUR PACKAGE\n(SERIES)",
                'button_label' => 'Click Here!',
                'href' => '/tour/series-departures-2026',
                'image_path' => $this->sectionPhoto(DB::table('tour_package_pages')->where('slug', 'series-departures-2026')->value('hero_image'), self::SERIES_FALLBACK_IMAGE),
                'image_alt' => 'A mosque with tall minarets at sunset',
            ],
            'china-muslim' => [
                'slug' => 'china-muslim',
                'design_key' => 'service-china',
                'position' => 3,
                'title' => 'China Muslim',
                'overlay_label' => "CHINA\nMUSLIM",
                'button_label' => 'Click Here!',
                'href' => '/china-muslim',
            ],
            'company-trip' => [
                'slug' => 'company-trip-mice',
                'design_key' => 'service-mice',
                'position' => 4,
                'title' => 'Company Trip (MICE)',
                'overlay_label' => "COMPANY TRIP\n(MICE)",
                'button_label' => 'Click Here!',
                'href' => '/company-trip-mice',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->replaceServices([
            'tour-package-private' => [
                'slug' => 'rest-of-the-world',
                'design_key' => 'service-world',
                'position' => 1,
                'title' => 'Rest of the World',
                'overlay_label' => "REST OF\nTHE WORLD",
                'button_label' => 'Click Here!',
                'href' => 'https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20ask%20about%20your%20international%20tour%20packages.',
                'image_path' => 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=600&q=85',
                'image_alt' => 'A tranquil lake surrounded by mountains',
            ],
            'china-muslim' => [
                'slug' => 'china-muslim',
                'design_key' => 'service-china',
                'position' => 2,
                'title' => 'China Muslim',
                'overlay_label' => "CHINA\nMUSLIM",
                'button_label' => 'Click Here!',
                'href' => 'https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20ask%20about%20China%20Muslim%20tour%20packages.',
            ],
            'company-trip-mice' => [
                'slug' => 'company-trip',
                'design_key' => 'service-company',
                'position' => 3,
                'title' => 'Company Trip',
                'overlay_label' => "COMPANY\nTRIP",
                'button_label' => 'Click Here!',
                'href' => 'https://wa.me/60165500590?text=Hi%2C%20I%27d%20like%20to%20plan%20a%20company%20trip.',
            ],
            'tour-package-series' => [
                'slug' => 'ticketing',
                'design_key' => 'service-ticketing',
                'position' => 4,
                'title' => 'Ticketing',
                'overlay_label' => "FLIGHT\nTICKETS",
                'button_label' => 'Click Here!',
                'href' => 'mailto:tour@roystartravel.com.my?subject=Flight%20ticket%20enquiry',
                'image_path' => 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=600&q=85',
                'image_alt' => 'Passenger airplane flying above the clouds',
            ],
        ]);
    }

    /**
     * Applies new values to the services found by their current slug. Positions are unique,
     * so every matched row first moves out of the 1–4 range before taking its new place.
     *
     * @param  array<string, array<string, string|int>>  $servicesBySlug
     */
    private function replaceServices(array $servicesBySlug): void
    {
        DB::transaction(function () use ($servicesBySlug): void {
            $ids = DB::table('homepage_services')->whereIn('slug', array_keys($servicesBySlug))->pluck('id', 'slug');

            foreach ($ids as $id) {
                DB::table('homepage_services')->where('id', $id)->update(['position' => 100 + $id]);
            }

            foreach ($servicesBySlug as $currentSlug => $values) {
                if (isset($ids[$currentSlug])) {
                    DB::table('homepage_services')->where('id', $ids[$currentSlug])->update([...$values, 'updated_at' => now()]);
                }
            }
        });
    }

    /**
     * Reuses a section's banner photo when it is a web link or a bundled image. Uploaded files are not shared,
     * because replacing the service image deletes its old upload.
     */
    private function sectionPhoto(?string $path, string $fallback): string
    {
        if ($path !== null && (str_starts_with($path, 'images/') || in_array(parse_url($path, PHP_URL_SCHEME), ['http', 'https'], true))) {
            return $path;
        }

        return $fallback;
    }
};
