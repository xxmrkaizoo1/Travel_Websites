<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Content for the Ticketing page (airline partners, fare promotions and the ticket enquiry form), editable from the admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticketing_pages', function (Blueprint $table) {
            $table->id();
            $table->string('meta_title');
            $table->text('meta_description');
            $table->string('hero_eyebrow');
            $table->string('hero_title');
            $table->string('hero_highlight');
            $table->text('hero_body');
            $table->string('hero_image', 2048)->nullable();
            $table->string('airlines_label');
            $table->string('airlines_heading');
            $table->json('airlines');
            $table->string('promotions_label');
            $table->string('promotions_heading');
            $table->text('promotions_body')->nullable();
            $table->json('promotions');
            $table->string('enquiry_label');
            $table->string('enquiry_heading');
            $table->text('enquiry_body');
            $table->string('whatsapp_number', 20);
            $table->string('email');
            $table->string('phone');
            $table->timestamps();
        });

        $airline = static fn (string $name, string $file): array => ['name' => $name, 'logo' => "images/ticketing/airlines/{$file}.png"];
        $promotion = static fn (string $title, string $destinations, string $fare, string $file, string $alt): array => [
            'title' => $title,
            'airline' => 'Batik Air',
            'origin' => 'Kuala Lumpur',
            'destinations' => $destinations,
            'fare' => $fare,
            'fare_note' => 'All-in one way from',
            'booking_period' => 'Now – 18 Aug 2024',
            'travel_period' => 'Now – 31 Mar 2025',
            'image' => "images/ticketing/{$file}.jpg",
            'alt' => $alt,
        ];

        DB::table('ticketing_pages')->insert([
            'meta_title' => 'Flight Tickets | Smart Holidays by Roystar Travel & Tours',
            'meta_description' => 'Book flight tickets with Smart Holidays by Roystar Travel & Tours: airline partners, current fare promotions and a quick ticket enquiry.',
            'hero_eyebrow' => 'TICKETING',
            'hero_title' => 'Flights, booked',
            'hero_highlight' => 'for you.',
            'hero_body' => 'Tell us where and when you want to fly. Our ticketing team compares airlines and fares and books the seats for you.',
            'hero_image' => 'images/ticketing/banner.png',
            'airlines_label' => 'OUR AIRLINE PARTNERS',
            'airlines_heading' => 'We book with the airlines you trust.',
            'airlines' => json_encode([
                $airline('Batik Air', 'batik-air'),
                $airline('Singapore Airlines', 'singapore-airlines'),
                $airline('China Airlines', 'china-airlines'),
                $airline('China Southern Airlines', 'china-southern'),
                $airline('Philippine Airlines', 'philippine-airlines'),
                $airline('Etihad Airways', 'etihad'),
                $airline('Qatar Airways', 'qatar-airways'),
                $airline('Cathay Pacific', 'cathay-pacific'),
                $airline('Emirates', 'emirates'),
                $airline('EVA Air', 'eva-air'),
            ]),
            'promotions_label' => 'FARE PROMOTIONS',
            'promotions_heading' => 'Fly further for less.',
            'promotions_body' => 'Current airline deals from Kuala Lumpur. Fares are limited, so ask us early to check seats for your dates.',
            'promotions' => json_encode([
                $promotion('Chase the Horizon', 'Da Nang · Hanoi · Melbourne · Tokyo · Brisbane · Sydney and more', 'RM189', 'batik-air-chase-the-horizon', 'Batik Air promotion: a family playing on the beach, fly from Kuala Lumpur from RM189 one way'),
                $promotion('Explore Indonesia', 'Padang · Pekanbaru · Medan · Surabaya · Lombok · Jakarta and more', 'RM109', 'batik-air-explore-indonesia', 'Batik Air promotion: Mount Bromo at sunrise, fly to Indonesia from RM109 one way'),
                $promotion('Explore Thailand', 'Krabi · Hat Yai · Phuket · Bangkok', 'RM139', 'batik-air-explore-thailand', 'Batik Air promotion: islands and beaches of Krabi, fly to Thailand from RM139 one way'),
            ]),
            'enquiry_label' => 'TICKETS ENQUIRY',
            'enquiry_heading' => 'Tickets Enquiry',
            'enquiry_body' => 'Fill in your trip and send it to us on WhatsApp. Our ticketing team will reply with available flights and fares.',
            'whatsapp_number' => '60165500590',
            'email' => 'tour@roystartravel.com.my',
            'phone' => '+603 2143 0188',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ticketing_pages');
    }
};
