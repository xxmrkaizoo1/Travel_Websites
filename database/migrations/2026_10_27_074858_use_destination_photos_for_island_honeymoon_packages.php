<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Krabi, Lombok and Maldives shared one stock beach photo, so their cards on the Island & Honeymoon
 * page looked identical. Each gets a photo of its own destination (Wikimedia Commons:
 * "Longtails of Krabi" by Brandon Minieri, CC BY-SA 4.0; "Lombok, Island beach in the bay" by
 * Vyacheslav Argenberg, CC BY 4.0; "Diamonds Thudufushi Beach and Water Villas" by Martin Falbisoner,
 * CC BY-SA 4.0). Pages whose hero photo was already changed are left alone.
 */
return new class extends Migration
{
    private const STOCK_PHOTO = 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=2000&q=90';

    /**
     * @var array<string, string>
     */
    private array $photos = [
        'krabi' => 'images/island-honeymoon/krabi-longtail-boats.jpg',
        'lombok' => 'images/island-honeymoon/lombok-bay.jpg',
        'maldives' => 'images/island-honeymoon/maldives-water-villas.jpg',
    ];

    public function up(): void
    {
        foreach ($this->photos as $slug => $photo) {
            DB::table('tour_package_pages')
                ->where('slug', $slug)
                ->where('hero_image', self::STOCK_PHOTO)
                ->update(['hero_image' => $photo]);
        }
    }

    public function down(): void
    {
        foreach ($this->photos as $slug => $photo) {
            DB::table('tour_package_pages')
                ->where('slug', $slug)
                ->where('hero_image', $photo)
                ->update(['hero_image' => self::STOCK_PHOTO]);
        }
    }
};
