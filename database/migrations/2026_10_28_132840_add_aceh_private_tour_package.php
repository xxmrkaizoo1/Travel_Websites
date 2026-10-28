<?php

use App\Models\TourPackagePage;
use Database\Seeders\LegacyTourPackagePagesSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the 4D3N Aceh and Aceh + Sabang private tour package under Indonesia. Its content lives in
 * the legacy tour pages seeder data; an existing Aceh page is left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new LegacyTourPackagePagesSeeder)->run(['aceh']);
    }

    public function down(): void
    {
        TourPackagePage::query()->where('slug', 'aceh')->where('is_custom', true)->delete();
    }
};
