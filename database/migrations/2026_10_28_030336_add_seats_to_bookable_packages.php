<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Seats the admin offers on each bookable Smart Muslim package. Empty means no limit.
 * Each traveller on a confirmed or completed online booking takes one seat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_package_pages', function (Blueprint $table): void {
            $table->unsignedInteger('seats')->nullable();
        });

        Schema::table('china_muslim_packages', function (Blueprint $table): void {
            $table->unsignedInteger('seats')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('china_muslim_packages', function (Blueprint $table): void {
            $table->dropColumn('seats');
        });

        Schema::table('tour_package_pages', function (Blueprint $table): void {
            $table->dropColumn('seats');
        });
    }
};
