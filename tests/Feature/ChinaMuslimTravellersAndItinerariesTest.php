<?php

namespace Tests\Feature;

use App\Models\ChinaAboutPage;
use App\Models\ChinaMuslimPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChinaMuslimTravellersAndItinerariesTest extends TestCase
{
    use RefreshDatabase;

    public function test_brochure_itineraries_fill_only_the_empty_kunming_and_zhangjiajie_cards(): void
    {
        $kunming = ChinaMuslimPackage::factory()->create(['title' => '9D7N KUNMING DALI LIJIANG SHANGRI-LA (2 WAY TRAIN)- MUSLIM TOURS', 'details' => null]);
        $zhangjiajie = ChinaMuslimPackage::factory()->create(['title' => '8D6N ZHANGJIAJIE CHANGSHA CHANGDE – MUSLIM TOURS', 'details' => null]);
        $edited = ChinaMuslimPackage::factory()->create(['title' => '5D4N ZHANGJIAJIE EXPRESS', 'details' => 'DAY 01: Already planned']);

        (require database_path('migrations/2026_10_27_030430_add_kunming_and_zhangjiajie_china_muslim_itineraries.php'))->up();

        $kunmingDetails = $kunming->fresh()->parsedDetails();
        $this->assertCount(9, $kunmingDetails['days']);
        $this->assertSame('KUNMING – DALI', $kunmingDetails['days'][1]['title']);
        $this->assertSame('B,L,D', $kunmingDetails['days'][1]['meals']);
        $this->assertContains('Dali: M.Garden Hotel or similar', $kunmingDetails['sections']['Accommodation']);
        $this->assertContains('Extra oxygen tank at Jade Dragon Snow Mountain: RMB 50 per person', $kunmingDetails['sections']['Optional Tours']);

        $zhangjiajieDetails = $zhangjiajie->fresh()->parsedDetails();
        $this->assertCount(8, $zhangjiajieDetails['days']);
        $this->assertSame(['November 2026: 4, 16, 25', 'December 2026: 3, 7, 10, 17, 18, 20, 21, 23, 31 (school holidays)'], $zhangjiajieDetails['sections']['Departure Dates']);
        $this->assertSame(['Jade', 'Latex', 'Tea', 'Silk'], $zhangjiajieDetails['sections']['Shopping Stop']);

        $this->assertSame('DAY 01: Already planned', $edited->fresh()->details);

        $this->get(route('china-muslim'))
            ->assertSee('DEPARTURE DATES')
            ->assertSee('ACCOMMODATION')
            ->assertSee('OPTIONAL TOURS')
            ->assertSee('Changsha: Changsha Orange Hotel or similar');
    }

    public function test_guilin_brochure_fills_the_empty_guilin_yangshuo_card(): void
    {
        $guilin = ChinaMuslimPackage::factory()->create(['title' => '7D5N GUANGZHOU GUILIN YANGSHUO – MUSLIM TOURS', 'details' => null]);

        (require database_path('migrations/2026_10_27_033244_add_guilin_yangshuo_china_muslim_itinerary.php'))->up();

        $details = $guilin->fresh()->parsedDetails();
        $this->assertCount(7, $details['days']);
        $this->assertSame('GUILIN – YANGSHUO', $details['days'][3]['title']);
        $this->assertSame('B,L', $details['days'][3]['meals']);
        $this->assertSame(['Silk', 'Chinese Tibetan medicine', 'Tong Ren Tang', 'Jewellery'], $details['sections']['Shopping Stop']);
        $this->assertSame(['7 days 5 nights'], $details['sections']['Duration']);
        $this->assertStringStartsWith('Itinerary, flights and hotels', $details['note']);
    }

    public function test_brochure_headings_are_read_as_package_sections(): void
    {
        $package = new ChinaMuslimPackage(['details' => "DAY 01: Arrival\nPackage Includes:\nHalal meals\nMandatory Shopping Stops:\nJade\nPackage Excludes:\nTips"]);

        $this->assertSame(['Included' => ['Halal meals'], 'Shopping Stop' => ['Jade'], 'Excluded' => ['Tips']], $package->parsedDetails()['sections']);
    }

    public function test_china_page_shows_the_traveller_slideshow_with_thumbnails(): void
    {
        $this->get(route('china-muslim'))
            ->assertOk()
            ->assertSee('Moments from our China Muslim journeys')
            ->assertSee('images/china/travellers/lanzhou-silk-road.jpg', false)
            ->assertSee('Lanzhou · Silk Road')
            ->assertSee('Show photo 11: All smiles before take-off');

        ChinaAboutPage::query()->update(['traveller_photos' => json_encode([])]);

        $this->get(route('china-muslim'))->assertDontSee('Moments from our China Muslim journeys');
    }

    public function test_admin_edits_captions_removes_and_uploads_traveller_photos(): void
    {
        Storage::fake('public');
        ChinaAboutPage::query()->update(['traveller_photos' => json_encode([
            ['image' => 'images/china/travellers/lanzhou-silk-road.jpg', 'caption' => 'Lanzhou'],
            ['image' => 'china-travellers/old-upload.jpg', 'caption' => 'Old'],
        ])]);
        Storage::disk('public')->put('china-travellers/old-upload.jpg', 'photo');

        $payload = [
            'photos' => [
                ['image' => 'images/china/travellers/lanzhou-silk-road.jpg', 'caption' => 'Lanzhou · Silk Road 2026'],
                ['image' => 'china-travellers/old-upload.jpg', 'caption' => 'Old', 'remove' => '1'],
                ['image' => 'china-travellers/not-in-the-slideshow.jpg', 'caption' => 'Sneaky'],
            ],
            'new_photos' => [UploadedFile::fake()->image('xian.jpg', 1600, 1000)],
        ];

        $this->actingAs(User::factory()->create())
            ->put(route('admin.china-muslim.travellers'), $payload)
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.china-muslim'))
            ->assertSee('Traveller photos slideshow');

        $this->put(route('admin.china-muslim.travellers'), $payload)
            ->assertRedirect(route('admin.china-muslim'))
            ->assertSessionHas('status', 'Traveller photos updated.');

        $photos = ChinaAboutPage::query()->first()->traveller_photos;
        $this->assertCount(2, $photos);
        $this->assertSame(['image' => 'images/china/travellers/lanzhou-silk-road.jpg', 'caption' => 'Lanzhou · Silk Road 2026'], $photos[0]);
        $this->assertStringStartsWith('china-travellers/', $photos[1]['image']);
        Storage::disk('public')->assertExists($photos[1]['image']);
        Storage::disk('public')->assertMissing('china-travellers/old-upload.jpg');
    }

    public function test_slideshow_keeps_to_its_photo_limit(): void
    {
        Storage::fake('public');
        $full = collect(range(1, ChinaAboutPage::MAX_TRAVELLER_PHOTOS))
            ->map(fn (int $number): array => ['image' => "china-travellers/photo-{$number}.jpg", 'caption' => "Trip {$number}"]);
        ChinaAboutPage::query()->update(['traveller_photos' => $full->toJson()]);

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.china-muslim.travellers'), [
                'photos' => $full->all(),
                'new_photos' => [UploadedFile::fake()->image('extra.jpg')],
            ])
            ->assertSessionHasErrors('new_photos');

        $this->assertCount(ChinaAboutPage::MAX_TRAVELLER_PHOTOS, ChinaAboutPage::query()->first()->traveller_photos);
    }
}
