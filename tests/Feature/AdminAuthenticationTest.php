<?php

namespace Tests\Feature;

use App\Models\ChinaMuslimPackage;
use App\Models\HomepageCampaign;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_is_available(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Welcome back')
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false);
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_each_new_admin_sign_in_offers_the_guided_tour_once(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Aisyah Rahman']);

        // Already signed in without a fresh sign-in: the "?" button is there, but nobody is asked.
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertSee('data-admin-tour-start', false)
            ->assertSee('data-tour-offer="false"', false);
        $this->post(route('logout'));

        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-tour-name="Aisyah" data-tour-offer="true"', false)
            ->assertSee('aria-label="Show me around the admin"', false)
            ->assertSee('data-admin-tour="stats"', false)
            ->assertSee('data-admin-tour="nav-online-booking"', false);

        $this->get(route('admin.dashboard'))->assertSee('data-tour-offer="false"', false);
    }

    public function test_admin_can_log_in_access_dashboard_and_log_out(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $admin->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Travel overview')
            ->assertSee('SMART MUSLIM TOUR')
            ->assertSee('Tour Package (Private)')
            ->assertSee('Tour Package (Series)')
            ->assertSee('China Muslim')
            ->assertSee('Company Trip (MICE)')
            ->assertSee('Contact Us');

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_dashboard_sidebar_lists_manageable_content_with_context_actions(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $package = ChinaMuslimPackage::factory()->create(['title' => 'Sidebar Beijing Tour']);
        $campaign = HomepageCampaign::query()->orderBy('position')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Sidebar Beijing Tour')
            ->assertSee('data-edit-url="'.route('admin.china-muslim.edit', $package).'"', false)
            ->assertSee('data-delete-url="'.route('admin.china-muslim.destroy', $package).'"', false)
            ->assertSee('data-edit-url="'.route('admin.homepage.edit', $campaign).'"', false)
            ->assertSee('data-delete-url="'.route('admin.homepage.destroy', $campaign).'"', false)
            ->assertSee('data-context-create-url="'.route('admin.china-muslim.create').'"', false);
    }

    public function test_non_admin_user_cannot_log_in_or_access_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_invalid_admin_password_is_rejected(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $admin->email,
                'password' => 'incorrect-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_seeder_creates_admin_using_configured_credentials(): void
    {
        config([
            'admin.name' => 'Configured Admin',
            'admin.email' => 'configured-admin@example.com',
            'admin.password' => 'a-secure-test-password',
        ]);

        $this->seed(AdminUserSeeder::class);

        $admin = User::query()->where('email', 'configured-admin@example.com')->firstOrFail();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'is_admin' => true,
        ]);
        $this->assertTrue(Hash::check('a-secure-test-password', $admin->password));
    }
}
