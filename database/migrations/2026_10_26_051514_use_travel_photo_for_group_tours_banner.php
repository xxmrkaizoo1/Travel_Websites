<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The Smart Holidays homepage banner has the brand logo in its centre, which clashes with the
 * Group Tours heading. Pages still using it switch to a plain travel photo; uploaded banners are kept.
 */
return new class extends Migration
{
    private const OLD_BANNER = 'images/smart-holidays-banner.jpg';

    private const NEW_BANNER = 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=2000&q=85';

    public function up(): void
    {
        DB::table('group_tours_pages')->where('hero_image', self::OLD_BANNER)->update(['hero_image' => self::NEW_BANNER]);
    }

    public function down(): void
    {
        DB::table('group_tours_pages')->where('hero_image', self::NEW_BANNER)->update(['hero_image' => self::OLD_BANNER]);
    }
};
