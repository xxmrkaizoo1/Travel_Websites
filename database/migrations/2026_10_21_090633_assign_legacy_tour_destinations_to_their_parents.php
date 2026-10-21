<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DESTINATIONS_BY_PARENT = [
        'island-honeymoon' => ['bali', 'krabi', 'lombok', 'maldives'],
        'indonesia' => ['aceh', 'medan', 'padang', 'surabaya', 'yogyakarta'],
        'thailand' => ['bangkok', 'chiang-mai-chiang-rai', 'kanchanaburi', 'pattaya'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::DESTINATIONS_BY_PARENT as $parentSlug => $destinationSlugs) {
            DB::table('tour_package_pages')
                ->whereIn('slug', $destinationSlugs)
                ->where('is_custom', true)
                ->whereNull('parent_slug')
                ->update(['parent_slug' => $parentSlug]);
        }
    }

    /**
     * Destination relationships are retained when rolling back this data correction.
     */
    public function down(): void {}
};
