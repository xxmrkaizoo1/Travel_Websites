<?php

use App\Models\TourPackagePage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Clear slide link labels that have no link URL.
     *
     * The public page only renders a link when both values are set, while the
     * admin form requires them together, so dangling labels made the page impossible to save.
     */
    public function up(): void
    {
        TourPackagePage::query()->each(function (TourPackagePage $page): void {
            $slides = $page->slides ?? [];
            $hasChanges = false;

            foreach ($slides as $index => $slide) {
                if (filled($slide['link_label'] ?? '') && blank($slide['link_url'] ?? '')) {
                    $slides[$index]['link_label'] = '';
                    $hasChanges = true;
                }
            }

            if ($hasChanges) {
                $page->slides = $slides;
                $page->save();
            }
        });
    }

    /**
     * The cleared labels were never displayed, so there is nothing to restore.
     */
    public function down(): void
    {
        //
    }
};
