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
        Schema::table('china_muslim_packages', function (Blueprint $table) {
            $table->text('details')->nullable()->after('link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('china_muslim_packages', function (Blueprint $table) {
            $table->dropColumn('details');
        });
    }
};
