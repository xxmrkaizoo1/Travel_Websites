<?php

namespace Tests\Feature;

use App\Models\HomepageConsultant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SmartMuslimConsultantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_the_team_below_our_services_with_whatsapp_links(): void
    {
        HomepageConsultant::query()->where('name', 'Wani')->update(['whatsapp' => '016-123 4567']);

        $this->get(route('smart-muslim'))
            ->assertOk()
            ->assertSeeInOrder(['id="services-title"', 'We’re here to help', 'Badrul', 'Wani', 'Izzren', 'Azam'], false)
            ->assertSee('class="smx-consultant-avatar"', false)
            ->assertSee('https://wa.me/60161234567?text='.rawurlencode('Hi Wani, I have a question about Smart Muslim tours.'), false)
            ->assertSee('https://wa.me/'.HomepageConsultant::MAIN_WHATSAPP.'?text='.rawurlencode('Hi, I would like to speak to Badrul about Smart Muslim tours.'), false);

        HomepageConsultant::query()->delete();

        $this->get(route('smart-muslim'))->assertDontSee('We’re here to help');
    }

    public function test_admin_edits_reorders_and_removes_team_members(): void
    {
        Storage::fake('public');
        $first = HomepageConsultant::factory()->create(['name' => 'Aina', 'position' => 1, 'photo_path' => 'homepage-consultants/aina.jpg']);
        $second = HomepageConsultant::factory()->create(['name' => 'Hakim', 'position' => 2, 'photo_path' => 'images/team/azam.jpg']);
        $leaving = HomepageConsultant::factory()->create(['name' => 'Farah', 'position' => 3, 'photo_path' => 'homepage-consultants/farah.jpg']);
        Storage::disk('public')->put('homepage-consultants/aina.jpg', 'photo');
        Storage::disk('public')->put('homepage-consultants/farah.jpg', 'photo');

        $payload = ['consultants' => [
            $first->id => ['name' => 'Aina Sofea', 'role' => 'Senior Consultant', 'whatsapp' => '+6016 550 0111', 'position' => 2, 'photo' => UploadedFile::fake()->image('aina.png')],
            $second->id => ['name' => 'Hakim', 'role' => '', 'whatsapp' => '', 'position' => 1, 'remove_photo' => '1'],
            $leaving->id => ['name' => 'Farah', 'position' => 3, 'remove' => '1'],
        ]];

        $this->actingAs(User::factory()->create())
            ->put(route('admin.homepage.consultants.update'), $payload)
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.homepage.consultants.update'), $payload)
            ->assertRedirect(route('admin.homepage.consultants'));

        $first->refresh();
        $this->assertSame(['Aina Sofea', 'Senior Consultant', '+6016 550 0111', 2], [$first->name, $first->role, $first->whatsapp, $first->position]);
        $this->assertStringStartsWith('homepage-consultants/', $first->photo_path);
        Storage::disk('public')->assertExists($first->photo_path);
        Storage::disk('public')->assertMissing('homepage-consultants/aina.jpg');
        $this->assertNull($second->fresh()->photo_path);
        $this->assertModelMissing($leaving);
        Storage::disk('public')->assertMissing('homepage-consultants/farah.jpg');
    }

    public function test_admin_adds_a_team_member_and_bad_numbers_are_rejected(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('admin.homepage.consultants.store'), ['new_name' => 'Nadia', 'new_whatsapp' => 'call me'])
            ->assertSessionHasErrors('new_whatsapp');

        $this->post(route('admin.homepage.consultants.store'), [
            'new_name' => 'Nadia',
            'new_role' => 'Umrah Specialist',
            'new_whatsapp' => '012-345 6789',
            'new_photo' => UploadedFile::fake()->image('nadia.jpg'),
        ])->assertRedirect(route('admin.homepage.consultants'));

        $nadia = HomepageConsultant::query()->where('name', 'Nadia')->sole();
        $this->assertSame(HomepageConsultant::query()->max('position'), $nadia->position);
        Storage::disk('public')->assertExists($nadia->photo_path);
        $this->assertStringStartsWith('https://wa.me/60123456789?', $nadia->whatsappUrl());
    }

    public function test_team_size_is_limited(): void
    {
        HomepageConsultant::factory()->count(HomepageConsultant::MAX_CONSULTANTS - HomepageConsultant::query()->count())->create();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.homepage.consultants.store'), ['new_name' => 'One too many'])
            ->assertSessionHasErrors('new_name');

        $this->assertDatabaseMissing('homepage_consultants', ['name' => 'One too many']);
    }
}
