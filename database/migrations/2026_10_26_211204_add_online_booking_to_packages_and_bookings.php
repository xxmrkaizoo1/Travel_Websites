<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets clients book Smart Muslim packages online: each package gets an open/closed switch,
 * and bookings remember the package they came from and the client's special requests.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_package_pages', function (Blueprint $table) {
            $table->boolean('booking_open')->default(true);
        });

        Schema::table('china_muslim_packages', function (Blueprint $table) {
            $table->boolean('booking_open')->default(true);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('amount', 12, 2)->nullable()->change();
            $table->nullableMorphs('bookable');
            $table->text('special_requests')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropMorphs('bookable');
            $table->dropColumn('special_requests');
        });

        Schema::table('china_muslim_packages', function (Blueprint $table) {
            $table->dropColumn('booking_open');
        });

        Schema::table('tour_package_pages', function (Blueprint $table) {
            $table->dropColumn('booking_open');
        });
    }
};
