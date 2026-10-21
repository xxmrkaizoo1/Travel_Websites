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
            $table->string('kind', 20)->default('introduction')->after('name');
            $table->boolean('is_custom')->default(false)->after('kind');
            $table->json('package_data')->nullable()->after('slides');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_package_pages', function (Blueprint $table) {
            $table->dropColumn(['kind', 'is_custom', 'package_data']);
        });
    }
};
