<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CONDITIONS = [
        ['title' => 'Booking & deposit', 'body' => "A booking is confirmed once the booking form is completed and the deposit is received.\nDeposits are non-refundable and non-transferable.\nSeats are limited and allocated on a first-come, first-served basis."],
        ['title' => 'Full payment', 'body' => "The balance must be paid in full before the date stated on your invoice.\nBookings without full payment by that date may be cancelled and the deposit forfeited."],
        ['title' => 'Cancellation by travellers', 'body' => "All cancellations must be made in writing.\nCancellation charges depend on how close to departure the cancellation is made; our team will confirm the charge for your tour.\nNo refund is given for unused services, missed tours or early departure."],
        ['title' => 'Minimum group size', 'body' => "Each departure needs a minimum number of travellers.\nIf the minimum is not reached, we may offer another date or a full refund of the amount paid."],
        ['title' => 'Passports & visas', 'body' => "Your passport must be valid for at least 6 months from the date of travel.\nTravellers are responsible for holding the correct visa and travel documents.\nNo refund is given if entry is refused by immigration."],
        ['title' => 'Travel insurance', 'body' => 'We strongly recommend comprehensive travel insurance covering medical costs, cancellation and loss of belongings.'],
        ['title' => 'Changes to the itinerary', 'body' => "Itineraries, flights and hotels may change because of weather, airline schedules or local conditions.\nWe will arrange a replacement of a similar standard wherever possible."],
    ];

    public function up(): void
    {
        Schema::table('group_tours_pages', function (Blueprint $table): void {
            $table->string('booking_heading')->default('Booking Conditions')->after('cta_label');
            $table->string('booking_intro', 500)->nullable()->after('booking_heading');
            $table->json('booking_conditions')->nullable()->after('booking_intro');
        });

        DB::table('group_tours_pages')->update([
            'booking_intro' => 'Please read these conditions before booking a group tour. By paying the deposit you agree to the terms below.',
            'booking_conditions' => json_encode(self::CONDITIONS),
        ]);
    }

    public function down(): void
    {
        Schema::table('group_tours_pages', function (Blueprint $table): void {
            $table->dropColumn(['booking_heading', 'booking_intro', 'booking_conditions']);
        });
    }
};
