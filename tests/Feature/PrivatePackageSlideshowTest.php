<?php

namespace Tests\Feature;

use App\Models\TourPackagePage;
use Database\Seeders\IndonesiaTourPackagePagesSeeder;
use Database\Seeders\IslandHoneymoonPackagePagesSeeder;
use Database\Seeders\LegacyTourPackagePagesSeeder;
use Database\Seeders\ThailandTourPackagePagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivatePackageSlideshowTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_private_package_shows_a_full_photo_slideshow(): void
    {
        $this->seed([LegacyTourPackagePagesSeeder::class, IndonesiaTourPackagePagesSeeder::class, ThailandTourPackagePagesSeeder::class, IslandHoneymoonPackagePagesSeeder::class]);
        $this->fillSlideshows();

        $packages = TourPackagePage::query()->where('is_custom', true)->get()->filter->hasItineraries();
        $this->assertNotEmpty($packages);

        foreach ($packages as $package) {
            $this->assertGreaterThanOrEqual(2, count($package->package_data['gallery']), "{$package->slug} needs at least two slideshow photos.");

            $this->get(route('tour-package.show', $package->slug))
                ->assertOk()
                ->assertSee('pk-showcase-stage', false)
                ->assertSee('bali-carousel-next', false)
                ->assertSee('pk-showcase-thumbs', false);
        }
    }

    public function test_curated_galleries_are_not_replaced(): void
    {
        $this->seed(LegacyTourPackagePagesSeeder::class);
        $aceh = TourPackagePage::query()->where('slug', 'aceh')->firstOrFail();
        $gallery = $aceh->package_data['gallery'];

        $this->fillSlideshows();

        $this->assertSame($gallery, $aceh->fresh()->package_data['gallery']);
    }

    public function test_package_without_gallery_photos_falls_back_to_its_hero_and_intro_photos(): void
    {
        $page = new TourPackagePage(TourPackagePage::starterContent('package', 'Langkawi Break'));
        $page->slug = 'langkawi-break';
        $page->is_custom = true;
        $page->hero_image = 'images/series/seoul-1.jpg';
        $page->intro_image = 'images/series/seoul-2.jpg';
        $page->package_data = [...$page->package_data, 'gallery' => []];
        $page->save();

        $this->get(route('tour-package.show', 'langkawi-break'))
            ->assertOk()
            ->assertSee('pk-showcase-stage', false)
            ->assertSee('bali-carousel-next', false)
            ->assertSeeInOrder([asset('images/series/seoul-1.jpg'), asset('images/series/seoul-2.jpg')], false);
    }

    private function fillSlideshows(): void
    {
        (require database_path('migrations/2026_10_27_175640_fill_private_package_photo_slideshows.php'))->up();
    }
}
