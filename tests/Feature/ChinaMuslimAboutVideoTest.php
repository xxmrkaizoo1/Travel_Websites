<?php

namespace Tests\Feature;

use App\Models\ChinaMuslimPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ChinaMuslimAboutVideoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_uploads_replaces_and_removes_a_cards_about_video(): void
    {
        Storage::fake('public');
        $package = ChinaMuslimPackage::factory()->create(['about_video_path' => 'china-muslim-videos/old.mp4']);
        Storage::disk('public')->put('china-muslim-videos/old.mp4', 'video');
        $admin = User::factory()->admin()->create();

        $this->actingAs(User::factory()->create())
            ->put(route('admin.china-muslim.update', $package), $this->payload($package, ['about_video' => $this->video()]))
            ->assertForbidden();

        $this->actingAs($admin)
            ->put(route('admin.china-muslim.update', $package), $this->payload($package, ['about_video' => $this->video()]))
            ->assertRedirect(route('admin.china-muslim'));

        $newVideo = $package->fresh()->about_video_path;
        $this->assertStringStartsWith('china-muslim-videos/', $newVideo);
        Storage::disk('public')->assertExists($newVideo);
        Storage::disk('public')->assertMissing('china-muslim-videos/old.mp4');

        $this->get(route('china-muslim'))
            ->assertSee('data-about-video-type="file"', false)
            ->assertSee('data-about-video-src="/storage/'.$newVideo.'"', false);

        $this->put(route('admin.china-muslim.update', $package), $this->payload($package, ['remove_about_video' => '1']));

        $this->assertNull($package->fresh()->about_video_path);
        Storage::disk('public')->assertMissing($newVideo);
    }

    public function test_saving_a_card_without_video_fields_keeps_its_uploaded_video(): void
    {
        Storage::fake('public');
        $package = ChinaMuslimPackage::factory()->create(['about_video_path' => 'china-muslim-videos/keep.mp4']);
        Storage::disk('public')->put('china-muslim-videos/keep.mp4', 'video');

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.china-muslim.update', $package), $this->payload($package));

        $this->assertSame('china-muslim-videos/keep.mp4', $package->fresh()->about_video_path);
        Storage::disk('public')->assertExists('china-muslim-videos/keep.mp4');
    }

    public function test_youtube_link_plays_in_the_about_popup_and_other_links_are_rejected(): void
    {
        $package = ChinaMuslimPackage::factory()->create();
        $this->actingAs(User::factory()->admin()->create());

        $this->put(route('admin.china-muslim.update', $package), $this->payload($package, ['about_video_url' => 'https://vimeo.com/123456']))
            ->assertSessionHasErrors('about_video_url');

        $this->put(route('admin.china-muslim.update', $package), $this->payload($package, ['about_video_url' => 'https://youtu.be/dQw4w9WgXcQ?si=abc']))
            ->assertSessionHasNoErrors();

        $this->get(route('china-muslim'))
            ->assertSee('data-about-video-type="youtube"', false)
            ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0', false);
    }

    public function test_video_upload_must_be_a_video_file(): void
    {
        Storage::fake('public');
        $package = ChinaMuslimPackage::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.china-muslim.update', $package), $this->payload($package, ['about_video' => UploadedFile::fake()->image('not-a-video.jpg')]))
            ->assertSessionHasErrors('about_video');

        $this->assertNull($package->fresh()->about_video_path);
    }

    public function test_deleting_a_card_deletes_its_video(): void
    {
        Storage::fake('public');
        $package = ChinaMuslimPackage::factory()->create(['about_video_path' => 'china-muslim-videos/gone.mp4']);
        Storage::disk('public')->put('china-muslim-videos/gone.mp4', 'video');

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.china-muslim.destroy', $package));

        Storage::disk('public')->assertMissing('china-muslim-videos/gone.mp4');
    }

    /**
     * @return array<string, array{string, ?string}>
     */
    public static function youtubeLinks(): array
    {
        return [
            'watch link' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=30s', 'dQw4w9WgXcQ'],
            'short link' => ['https://youtu.be/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'shorts link' => ['https://youtube.com/shorts/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'mobile link' => ['https://m.youtube.com/watch?feature=share&v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'look-alike domain' => ['https://youtube.com.evil.test/watch?v=dQw4w9WgXcQ', null],
            'channel page' => ['https://www.youtube.com/@smartholidays', null],
        ];
    }

    #[DataProvider('youtubeLinks')]
    public function test_youtube_id_is_read_only_from_youtube_video_links(string $url, ?string $expectedId): void
    {
        $this->assertSame($expectedId, ChinaMuslimPackage::youtubeId($url));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(ChinaMuslimPackage $package, array $overrides = []): array
    {
        return [
            'title' => $package->title,
            'price' => $package->price,
            'days' => 2,
            'nights' => 1,
            'slides' => [['title' => 'GUILIN', 'meals' => 'B', 'items' => 'Li River rafting']],
            ...$overrides,
        ];
    }

    private function video(): UploadedFile
    {
        return UploadedFile::fake()->create('li-river.mp4', 2048, 'video/mp4');
    }
}
