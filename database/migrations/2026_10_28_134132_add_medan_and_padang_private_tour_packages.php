<?php

use App\Models\TourPackagePage;
use Database\Seeders\IndonesiaTourPackagePagesSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the 4D3N and 5D4N Medan – Lake Toba – Berastagi and Padang – Bukittinggi private tour
 * packages under Indonesia and links their destination cards to them. Their content lives in the
 * Indonesia tour package seeder; existing pages are left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new IndonesiaTourPackagePagesSeeder)->run(['medan', 'padang']);
    }

    public function down(): void
    {
        TourPackagePage::query()->whereIn('slug', ['medan', 'padang'])->where('is_custom', true)->delete();
    }
};
