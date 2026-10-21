<?php

use App\Models\TourPackagePage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const DESTINATIONS = [
        'vietnam' => ['Hanoi', 'Da Nang', 'Ho Chi Minh', 'Hoi An'],
        'philippines' => ['Manila', 'Boracay', 'Palawan', 'Cebu'],
    ];

    public function up(): void
    {
        foreach (self::DESTINATIONS as $country => $names) {
            foreach ($names as $name) {
                $slug = Str::slug($name);

                if (TourPackagePage::query()->where('slug', $slug)->exists()) {
                    continue;
                }

                $page = new TourPackagePage(TourPackagePage::starterContent(TourPackagePage::KIND_PACKAGE, $name));
                $page->slug = $slug;
                $page->parent_slug = $country;
                $page->is_custom = true;
                $page->save();
            }
        }
    }

    public function down(): void
    {
        foreach (self::DESTINATIONS as $country => $names) {
            TourPackagePage::query()
                ->where('parent_slug', $country)
                ->whereIn('slug', array_map(Str::slug(...), $names))
                ->delete();
        }
    }
};
