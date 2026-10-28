<?php

namespace Database\Seeders;

use App\Models\TourPackagePage;
use Illuminate\Database\Seeder;

class LegacyTourPackagePagesSeeder extends Seeder
{
    private const PAGES = [
        'bali' => ['name' => 'Bali', 'parent' => 'island-honeymoon'],
        'aceh' => ['name' => 'Aceh', 'parent' => 'indonesia'],
    ];

    /**
     * @param  list<string>  $slugs  Only these pages are added; empty adds them all.
     */
    public function run(array $slugs = []): void
    {
        $content = json_decode(file_get_contents(__DIR__.'/data/legacy-tour-pages.json'), true, 512, JSON_THROW_ON_ERROR);
        $pages = $slugs === [] ? self::PAGES : array_intersect_key(self::PAGES, array_flip($slugs));

        foreach ($pages as $slug => $definition) {
            if (TourPackagePage::query()->where('slug', $slug)->exists()) {
                continue;
            }

            $page = new TourPackagePage($content[$slug] + [
                'name' => $definition['name'],
                'kind' => TourPackagePage::KIND_PACKAGE,
                'destinations_eyebrow' => '',
                'destinations_headline' => '',
                'destinations_body' => '',
                'slides' => [],
            ]);
            $page->slug = $slug;
            $page->parent_slug = $definition['parent'];
            $page->is_custom = true;
            $page->save();
        }
    }
}
