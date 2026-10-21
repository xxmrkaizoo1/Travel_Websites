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
            $table->string('parent_slug', 120)->nullable()->index()->after('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_package_pages', function (Blueprint $table) {
            $table->dropIndex(['parent_slug']);
            $table->dropColumn('parent_slug');
        });
    }
};
