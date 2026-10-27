<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Colour theme an admin picks for a custom tour page; "ocean" is the original blue look.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_package_pages', function (Blueprint $table) {
            $table->string('theme', 20)->default('ocean')->after('kind');
        });
    }

    public function down(): void
    {
        Schema::table('tour_package_pages', function (Blueprint $table) {
            $table->dropColumn('theme');
        });
    }
};
