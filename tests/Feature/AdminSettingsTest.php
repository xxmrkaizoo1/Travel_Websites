<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_updates_their_profile_but_not_to_an_email_already_in_use(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Old Name', 'email' => 'old@example.test']);
        User::factory()->create(['email' => 'taken@example.test']);

        $this->actingAs($admin)
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Settings')
            ->assertSee('old@example.test');

        $this->from(route('admin.settings'))
            ->put(route('admin.settings.profile'), ['name' => 'Aisyah Rahman', 'email' => 'taken@example.test'])
            ->assertSessionHasErrorsIn('profile', 'email');

        $this->put(route('admin.settings.profile'), ['name' => 'Aisyah Rahman', 'email' => 'aisyah@example.test'])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHas('status', 'Your profile has been updated.');

        $this->assertSame(['Aisyah Rahman', 'aisyah@example.test'], [$admin->fresh()->name, $admin->fresh()->email]);
    }

    public function test_admin_changes_password_only_with_the_current_one(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'old-password-1']);
        $this->actingAs($admin);

        $this->put(route('admin.settings.password'), ['current_password' => 'wrong', 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
            ->assertSessionHasErrorsIn('password', 'current_password');

        $this->put(route('admin.settings.password'), ['current_password' => 'old-password-1', 'password' => 'short', 'password_confirmation' => 'short'])
            ->assertSessionHasErrorsIn('password', 'password');

        $this->put(route('admin.settings.password'), ['current_password' => 'old-password-1', 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-password-1', $admin->fresh()->password));
    }

    public function test_signing_out_other_devices_keeps_this_one_and_resets_remember_me(): void
    {
        config(['session.driver' => 'database']);
        $admin = User::factory()->admin()->create(['password' => 'secret-pass-1', 'remember_token' => 'old-remember-token']);
        $someoneElse = User::factory()->create();
        DB::table('sessions')->insert([
            ['id' => 'admin-phone', 'user_id' => $admin->id, 'ip_address' => '10.0.0.2', 'user_agent' => 'Mozilla/5.0 (iPhone) Safari/604.1', 'payload' => '', 'last_activity' => now()->subHour()->timestamp],
            ['id' => 'other-user', 'user_id' => $someoneElse->id, 'ip_address' => '10.0.0.3', 'user_agent' => 'Chrome/120', 'payload' => '', 'last_activity' => now()->timestamp],
        ]);
        $this->actingAs($admin);

        $this->get(route('admin.settings'))->assertSee('Safari on iOS');

        $this->delete(route('admin.settings.sessions'), ['current_password' => 'wrong'])
            ->assertSessionHasErrorsIn('sessions', 'current_password');
        $this->assertDatabaseHas('sessions', ['id' => 'admin-phone']);

        $this->delete(route('admin.settings.sessions'), ['current_password' => 'secret-pass-1'])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHas('status', 'You have been signed out of your other devices.');

        $this->assertDatabaseMissing('sessions', ['id' => 'admin-phone']);
        $this->assertDatabaseHas('sessions', ['id' => 'other-user']);
        $this->assertNotSame('old-remember-token', $admin->fresh()->remember_token);
    }

    public function test_only_admins_can_open_settings(): void
    {
        $this->get(route('admin.settings'))->assertRedirect();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.settings'))
            ->assertForbidden();

        $this->put(route('admin.settings.profile'), ['name' => 'Hacker', 'email' => 'hacker@example.test'])
            ->assertForbidden();
    }
}
