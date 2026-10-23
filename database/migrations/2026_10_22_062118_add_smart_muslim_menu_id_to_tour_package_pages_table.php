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
            $table->foreignId('smart_muslim_menu_id')
                ->nullable()
                ->after('parent_slug')
                ->constrained('smart_muslim_menus')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_package_pages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('smart_muslim_menu_id');
        });
    }
};
