<?php

use App\Models\TourPackagePage;
use Illuminate\Database\Migrations\Migration;

/**
 * Package pages show their photo gallery as a full-screen showcase, so headings such as
 * "Cebu highlights slideshow" become "Cebu highlights". Headings without the word are left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        TourPackagePage::query()->each(function (TourPackagePage $page): void {
            $data = $page->package_data ?? [];
            $headline = $data['gallery_headline'] ?? null;

            if (! is_string($headline) || ! preg_match('/\bslideshow\b/i', $headline)) {
                return;
            }

            $data['gallery_headline'] = trim(preg_replace('/\s*\bslideshow\b/i', '', $headline));
            $page->package_data = $data;
            $page->saveQuietly();
        });
    }

    public function down(): void
    {
        // The removed word is not worth restoring.
    }
};
