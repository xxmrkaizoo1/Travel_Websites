<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('tour_package_pages')
            ->where('is_custom', true)
            ->whereIn('slug', ['bali', 'krabi', 'lombok', 'maldives'])
            ->update(['parent_slug' => 'island-honeymoon']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('tour_package_pages')
            ->where('is_custom', true)
            ->whereIn('slug', ['bali', 'lombok'])
            ->where('parent_slug', 'island-honeymoon')
            ->update(['parent_slug' => 'indonesia']);

        DB::table('tour_package_pages')
            ->where('is_custom', true)
            ->where('slug', 'krabi')
            ->where('parent_slug', 'island-honeymoon')
            ->update(['parent_slug' => 'thailand']);

        DB::table('tour_package_pages')
            ->where('is_custom', true)
            ->where('slug', 'maldives')
            ->where('parent_slug', 'island-honeymoon')
            ->update(['parent_slug' => null]);
    }
};
