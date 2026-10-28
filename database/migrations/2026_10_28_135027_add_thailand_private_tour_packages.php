<?php

use App\Models\TourPackagePage;
use Database\Seeders\ThailandTourPackagePagesSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the Bangkok, Bangkok – Pattaya, Bangkok – Kanchanaburi and Chiang Mai & Chiang Rai private
 * tour packages under Thailand. The Thailand destination cards already link to these pages. Their
 * content lives in the Thailand tour package seeder; existing pages are left alone.
 */
return new class extends Migration
{
    private const SLUGS = ['bangkok', 'pattaya', 'kanchanaburi', 'chiang-mai-chiang-rai'];

    public function up(): void
    {
        (new ThailandTourPackagePagesSeeder)->run(self::SLUGS);
    }

    public function down(): void
    {
        TourPackagePage::query()->whereIn('slug', self::SLUGS)->where('is_custom', true)->delete();
    }
};
