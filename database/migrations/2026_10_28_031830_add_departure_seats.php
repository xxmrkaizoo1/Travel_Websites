<?php

use App\Models\Booking;
use App\Models\TourPackagePage;
use App\Support\BookablePackage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Series packages get seats on each departure instead of one count for the whole package,
 * so every booking remembers which departure it is for.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_departure_seats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_package_page_id')->constrained()->cascadeOnDelete();
            $table->string('departure_key');
            $table->unsignedInteger('seats');
            $table->timestamps();

            $table->unique(['tour_package_page_id', 'departure_key'], 'package_departure_seats_unique');
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->string('bookable_option')->nullable()->after('bookable_id');
        });

        // Link existing series bookings to their departure by destination and travel date.
        Booking::query()
            ->where('bookable_type', (new TourPackagePage)->getMorphClass())
            ->with('bookable')
            ->each(function (Booking $booking): void {
                if (! $booking->bookable instanceof TourPackagePage || ! $booking->bookable->isSeries()) {
                    return;
                }

                $matches = collect((new BookablePackage($booking->bookable))->departures())
                    ->where('destination', $booking->destination)
                    ->where('travel_date', $booking->travel_date->toDateString());

                if ($matches->count() === 1) {
                    $booking->forceFill(['bookable_option' => $matches->first()['key']])->saveQuietly();
                }
            });

        // Package-wide seats on series packages are replaced by seats on each departure.
        TourPackagePage::query()->whereKey(TourPackagePage::query()->get()->filter->isSeries()->modelKeys())->update(['seats' => null]);
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn('bookable_option');
        });

        Schema::dropIfExists('package_departure_seats');
    }
};
