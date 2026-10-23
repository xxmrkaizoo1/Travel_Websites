<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tour_package_pages', function (Blueprint $table) {
            $table->boolean('is_smart_muslim_menu')->default(false)->after('is_custom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_package_pages', function (Blueprint $table) {
            $table->dropColumn('is_smart_muslim_menu');
        });
    }
};
