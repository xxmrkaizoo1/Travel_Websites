<?php

namespace Tests\Feature;

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
