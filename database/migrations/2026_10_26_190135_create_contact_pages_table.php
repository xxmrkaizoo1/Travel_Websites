<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Content for the Contact Us page (company offices and map), editable from the admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_pages', function (Blueprint $table) {
            $table->id();
            $table->string('meta_title');
            $table->text('meta_description');
            $table->string('hero_eyebrow');
            $table->string('hero_title');
            $table->text('hero_body');
            $table->string('hero_image', 2048)->nullable();
            $table->string('companies_heading');
            $table->json('companies');
            $table->string('map_query');
            $table->timestamps();
        });

        DB::table('contact_pages')->insert([
            'meta_title' => 'Contact Us | Smart Holidays by Roystar Travel & Tours',
            'meta_description' => 'Contact Smart Holidays by Roystar Travel & Tours, Hong Thai Tours & Travel and B.T.T. Travel Services at Lorong Brunei 3, off Jalan Pudu, Kuala Lumpur.',
            'hero_eyebrow' => 'CONTACT US',
            'hero_title' => "Let's plan your next trip together.",
            'hero_body' => 'Call, WhatsApp, email or drop by our offices off Jalan Pudu, Kuala Lumpur. Our team is happy to help you plan.',
            'hero_image' => null,
            'companies_heading' => 'Contact Information',
            'companies' => json_encode([
                [
                    'name' => 'ROYSTAR TRAVEL & TOURS SDN BHD',
                    'logo' => 'images/smart-holidays-logo.png',
                    'address' => 'No.16A & 16B, Lorong Brunei 3, Off Jalan Pudu, 55100 Kuala Lumpur, Malaysia.',
                    'phone' => '03-2143 0188 / 2143 0288 / 2386 0966',
                    'whatsapp' => '016-550 0590 / 016-550 0529',
                    'email' => 'tour@roystartravel.com.my',
                    'website' => 'www.smartholidays.com.my',
                ],
                [
                    'name' => 'HONG THAI TOURS & TRAVEL (M) SDN BHD',
                    'logo' => 'images/contact/hong-thai.png',
                    'address' => 'Wisma Hong Thai, No. 16, Lorong Brunei 3, Off Jalan Pudu, 55100 Kuala Lumpur, Malaysia.',
                    'phone' => '603-2148 8448 / 2141 1276 / 2386 0966',
                    'whatsapp' => '016-550 0578 / 016-550 0518',
                    'email' => 'tour@hongthaitours.com.my',
                    'website' => '',
                ],
                [
                    'name' => 'B.T.T. TRAVEL SERVICES SDN BHD',
                    'logo' => 'images/contact/btt.png',
                    'address' => 'No 16C, Lorong Brunei 3, off Jalan Pudu, 55100 Kuala Lumpur',
                    'phone' => '603 - 2148 8820',
                    'whatsapp' => '016-227 7691',
                    'email' => 'inbound4@btttravel.com.my',
                    'website' => '',
                ],
            ]),
            'map_query' => 'Roystar Travel & Tours Sdn Bhd, 16A & 16B Lorong Brunei 3, Pudu, 55100 Kuala Lumpur',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_pages');
    }
};
