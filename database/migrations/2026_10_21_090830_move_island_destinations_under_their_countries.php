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
            ->whereIn('slug', ['bali', 'lombok'])
            ->where('parent_slug', 'island-honeymoon')
            ->update(['parent_slug' => 'indonesia']);

        DB::table('tour_package_pages')
            ->where('slug', 'krabi')
            ->where('parent_slug', 'island-honeymoon')
            ->update(['parent_slug' => 'thailand']);

        DB::table('tour_package_pages')
            ->where('slug', 'maldives')
            ->where('parent_slug', 'island-honeymoon')
            ->update(['parent_slug' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('tour_package_pages')
            ->whereIn('slug', ['bali', 'lombok'])
            ->where('parent_slug', 'indonesia')
            ->update(['parent_slug' => 'island-honeymoon']);

        DB::table('tour_package_pages')
            ->where('slug', 'krabi')
            ->where('parent_slug', 'thailand')
            ->update(['parent_slug' => 'island-honeymoon']);

        DB::table('tour_package_pages')
            ->where('slug', 'maldives')
            ->whereNull('parent_slug')
            ->update(['parent_slug' => 'island-honeymoon']);
    }
};
