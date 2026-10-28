<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional background video and image behind the "Popular in Smart Holidays" carousel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smart_holidays_pages', function (Blueprint $table): void {
            $table->string('holidays_popular_video', 2048)->nullable();
            $table->string('holidays_popular_image', 2048)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('smart_holidays_pages', function (Blueprint $table): void {
            $table->dropColumn(['holidays_popular_video', 'holidays_popular_image']);
        });
    }
};
