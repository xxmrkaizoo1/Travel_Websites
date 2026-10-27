<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminClientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_clients_cannot_manage_client_accounts(): void
    {
        $client = User::factory()->create();

        $this->get(route('admin.clients.index'))->assertRedirect(route('login'));
        $this->actingAs($client)->get(route('admin.clients.show', $client))->assertForbidden();
    }

    public function test_client_list_shows_clients_only_with_their_bookings(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->admin()->create(['name' => 'Office Admin']);
        $client = User::factory()->create(['name' => 'Aisyah Rahman']);
        Booking::factory()->for($client, 'client')->create(['status' => 'confirmed', 'amount' => 5000]);
        Booking::factory()->for($client, 'client')->create(['status' => 'cancelled', 'amount' => 900]);
        User::factory()->suspended()->create(['name' => 'Suspended Sam']);

        $this->actingAs($admin)
            ->get(route('admin.clients.index'))
            ->assertOk()
            ->assertSeeInOrder(['Aisyah Rahman', 'RM 5,000.00', 'Active'])
            ->assertSee('Suspended Sam')
            ->assertDontSee('Office Admin');

        $this->get(route('admin.clients.index', ['filter' => 'suspended']))
            ->assertSee('Suspended Sam')
            ->assertDontSee('Aisyah Rahman');

        $this->get(route('admin.clients.index', ['search' => 'aisyah']))
            ->assertSee('Aisyah Rahman')
            ->assertDontSee('Suspended Sam');
    }

    public function test_admin_accounts_cannot_be_opened_as_clients(): void
    {
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.clients.show', $otherAdmin))
            ->assertNotFound();
        $this->post(route('admin.clients.suspension.store', $otherAdmin))->assertNotFound();
        $this->assertNull($otherAdmin->fresh()->suspended_at);
    }

    public function test_admin_adds_a_client_who_can_sign_in_straight_away(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.clients.store'), [
                'name' => 'Walk In Client',
                'email' => 'walkin@example.com',
                'phone' => '012-345 6789',
                'password' => 'temporary123',
                'password_confirmation' => 'temporary123',
            ]);

        $client = User::query()->where('email', 'walkin@example.com')->sole();
        $this->assertFalse($client->is_admin);
        $this->assertNotNull($client->email_verified_at);

        $this->post(route('logout'));
        $this->post(route('client.login.store'), ['email' => 'walkin@example.com', 'password' => 'temporary123'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($client);
    }

    public function test_new_client_needs_a_unique_email_and_a_confirmed_password(): void
    {
        $existing = User::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.clients.store'), [
                'name' => '',
                'email' => $existing->email,
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertDatabaseCount('users', 2);
    }

    public function test_admin_updates_details_and_resets_the_password(): void
    {
        $client = User::factory()->create(['name' => 'Old Name']);

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.clients.update', $client), ['name' => 'New Name', 'email' => $client->email, 'phone' => '', 'password' => '', 'password_confirmation' => ''])
            ->assertSessionHas('status', 'Client details updated.');
        $this->assertSame('New Name', $client->fresh()->name);
        $this->assertTrue(Hash::check('password', $client->fresh()->password));

        $this->put(route('admin.clients.update', $client), ['name' => 'New Name', 'email' => $client->email, 'password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass'])
            ->assertSessionHas('status', 'Client details and password updated.');
        $this->assertTrue(Hash::check('brand-new-pass', $client->fresh()->password));
    }

    public function test_suspended_client_is_signed_out_and_cannot_sign_in_until_reactivated(): void
    {
        $client = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.clients.suspension.store', $client))->assertSessionHas('status');
        $this->assertTrue($client->fresh()->isSuspended());

        $this->actingAs($client->fresh())->get(route('home'))
            ->assertRedirect(route('client.login'))
            ->assertSessionHasErrors(['email' => 'This account has been suspended. Please contact us for help.']);
        $this->assertGuest();

        $this->post(route('client.login.store'), ['email' => $client->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'This account has been suspended. Please contact us for help.']);
        $this->assertGuest();

        $this->actingAs($admin)->delete(route('admin.clients.suspension.destroy', $client));
        $this->post(route('logout'));
        $this->post(route('client.login.store'), ['email' => $client->email, 'password' => 'password'])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($client);
    }

    public function test_client_with_bookings_cannot_be_deleted_but_one_without_can(): void
    {
        $admin = User::factory()->admin()->create();
        $booked = Booking::factory()->create()->client;
        $unbooked = User::factory()->create();
        $enquiry = Enquiry::factory()->for($unbooked, 'client')->create();

        $this->actingAs($admin)
            ->delete(route('admin.clients.destroy', $booked))
            ->assertSessionHasErrors(['client' => "{$booked->name} has bookings, so the account cannot be deleted. Suspend it instead."]);
        $this->assertModelExists($booked);

        $this->delete(route('admin.clients.destroy', $unbooked))->assertRedirect(route('admin.clients.index'));
        $this->assertModelMissing($unbooked);
        $this->assertNull($enquiry->fresh()->user_id);
    }

    public function test_client_profile_lists_bookings_and_enquiries(): void
    {
        $client = User::factory()->create();
        $booking = Booking::factory()->for($client, 'client')->create(['destination' => 'Melbourne']);
        Enquiry::factory()->for($client, 'client')->create(['destination' => 'Seoul in spring']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.clients.show', $client))
            ->assertOk()
            ->assertSee($booking->fresh()->reference)
            ->assertSee('Seoul in spring')
            ->assertSee('href="'.route('admin.bookings.create', ['client' => $client->id]).'"', false);
    }
}
