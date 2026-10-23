<?php

namespace Tests\Feature;

use App\Mail\ClientEmailVerificationCode;
use App\Models\ClientEmailVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ClientAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_login_and_registration_pages_are_available(): void
    {
        $this->get(route('client.login'))
            ->assertOk()
            ->assertSee('CLIENT ACCOUNT')
            ->assertSee('Sign in to continue with your travel plans.')
            ->assertSee(route('client.register'));

        $this->get(route('client.register'))
            ->assertOk()
            ->assertSee('Create your account')
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('<option value="MY" selected>Malaysia (+60)</option>', false)
            ->assertSee('Singapore (+65)')
            ->assertSee(route('client.login'));
    }

    public function test_phone_number_is_normalized_and_carried_to_the_account_after_verification(): void
    {
        Mail::fake();

        $this->post(route('client.register.store'), [
            'name' => 'Travel Client',
            'email' => 'client@example.com',
            'phone_country' => 'SG',
            'phone' => '0912 3456',
            'password' => 'client-pass-2026',
            'password_confirmation' => 'client-pass-2026',
        ])->assertRedirect(route('client.verify'));

        $this->assertDatabaseHas('client_email_verifications', [
            'email' => 'client@example.com',
            'phone' => '+659123456',
        ]);

        $code = null;
        Mail::assertSent(ClientEmailVerificationCode::class, function (ClientEmailVerificationCode $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });

        $this->post(route('client.verify.store'), ['code' => $code])->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', ['email' => 'client@example.com', 'phone' => '+659123456']);
    }

    /**
     * @param  array<string, string>  $override
     */
    #[DataProvider('invalidPhoneDetails')]
    public function test_registration_rejects_missing_or_invalid_phone_details(array $override): void
    {
        Mail::fake();

        $this->from(route('client.register'))
            ->post(route('client.register.store'), array_merge([
                'name' => 'Travel Client',
                'email' => 'client@example.com',
                'phone_country' => 'MY',
                'phone' => '123456789',
                'password' => 'client-pass-2026',
                'password_confirmation' => 'client-pass-2026',
            ], $override))
            ->assertRedirect(route('client.register'))
            ->assertSessionHasErrors(array_key_first($override));

        Mail::assertNothingSent();
        $this->assertDatabaseCount('client_email_verifications', 0);
    }

    /**
     * @return array<string, array{array<string, string>}>
     */
    public static function invalidPhoneDetails(): array
    {
        return [
            'missing number' => [['phone' => '']],
            'letters in number' => [['phone' => 'abc12345']],
            'too short' => [['phone' => '12']],
            'too long' => [['phone' => '1234567890123456']],
            'unknown country' => [['phone_country' => 'ZZ']],
            'missing country' => [['phone_country' => '']],
        ];
    }

    public function test_registration_sends_an_otp_without_creating_or_authenticating_the_client(): void
    {
        Mail::fake();

        $this->post(route('client.register.store'), [
            'name' => 'Travel Client',
            'email' => 'client@example.com',
            'phone_country' => 'MY',
            'phone' => '012-345 6789',
            'password' => 'client-pass-2026',
            'password_confirmation' => 'client-pass-2026',
        ])->assertRedirect(route('client.verify'));

        $code = null;
        Mail::assertSent(ClientEmailVerificationCode::class, function (ClientEmailVerificationCode $mail) use (&$code): bool {
            $code = $mail->code;

            return $mail->hasTo('client@example.com')
                && preg_match('/^\d{6}$/', $code) === 1;
        });

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'client@example.com']);
        $this->assertDatabaseHas('client_email_verifications', [
            'email' => 'client@example.com',
            'name' => 'Travel Client',
        ]);

        $verification = ClientEmailVerification::query()->where('email', 'client@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('client-pass-2026', $verification->password));
        $this->assertTrue(Hash::check((string) $code, $verification->otp_hash));
        $this->assertNotSame($code, $verification->otp_hash);
    }

    public function test_client_is_created_and_signed_in_after_verifying_the_email_otp(): void
    {
        Mail::fake();

        $this->post(route('client.register.store'), [
            'name' => 'Travel Client',
            'email' => 'client@example.com',
            'phone_country' => 'MY',
            'phone' => '012-345 6789',
            'password' => 'client-pass-2026',
            'password_confirmation' => 'client-pass-2026',
        ])->assertRedirect(route('client.verify'));

        $code = null;
        Mail::assertSent(ClientEmailVerificationCode::class, function (ClientEmailVerificationCode $mail) use (&$code): bool {
            $code = $mail->code;

            return $mail->hasTo('client@example.com');
        });

        $this->post(route('client.verify.store'), ['code' => $code])
            ->assertRedirect(route('home'));

        $user = User::query()->where('email', 'client@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Travel Client',
            'is_admin' => false,
        ]);
        $this->assertTrue(Hash::check('client-pass-2026', $user->password));
        $this->assertDatabaseMissing('client_email_verifications', ['email' => 'client@example.com']);
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_verification_email_includes_the_code_and_expiry_notice(): void
    {
        $mail = new ClientEmailVerificationCode('Travel Client', '654321');
        $content = $mail->render();

        $this->assertStringContainsString('654321', $content);
        $this->assertStringContainsString('10 minutes', $content);
        $this->assertStringContainsString('Travel Client', $content);
    }

    public function test_client_can_sign_in_and_sign_out(): void
    {
        $user = User::factory()->create();

        $this->post(route('client.login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('client.logout'))->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_client_login_rejects_admin_accounts(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->from(route('client.login'))
            ->post(route('client.login.store'), [
                'email' => $admin->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('client.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_client_login_rejects_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $this->from(route('client.login'))
            ->post(route('client.login.store'), [
                'email' => $user->email,
                'password' => 'incorrect-password',
            ])
            ->assertRedirect(route('client.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_client_registration_rejects_duplicate_emails_and_unconfirmed_passwords(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->from(route('client.register'))
            ->post(route('client.register.store'), [
                'name' => 'New Client',
                'email' => 'taken@example.com',
                'password' => 'client-pass-2026',
                'password_confirmation' => 'different-password',
            ])
            ->assertRedirect(route('client.register'))
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_client_registration_escapes_the_submitted_name_after_validation_fails(): void
    {
        $this->from(route('client.register'))
            ->post(route('client.register.store'), [
                'name' => '<script>alert("x")</script>',
                'email' => 'invalid-email',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertRedirect(route('client.register'));

        $this->get(route('client.register'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("x")</script>', false);
    }

    public function test_invalid_or_expired_otp_does_not_create_a_client_account(): void
    {
        Mail::fake();

        $this->post(route('client.register.store'), [
            'name' => 'Travel Client',
            'email' => 'client@example.com',
            'phone_country' => 'MY',
            'phone' => '012-345 6789',
            'password' => 'client-pass-2026',
            'password_confirmation' => 'client-pass-2026',
        ]);

        $code = null;
        Mail::assertSent(ClientEmailVerificationCode::class, function (ClientEmailVerificationCode $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });
        $incorrectCode = str_pad((string) (((int) $code + 1) % 1000000), 6, '0', STR_PAD_LEFT);

        $this->from(route('client.verify'))
            ->post(route('client.verify.store'), ['code' => $incorrectCode])
            ->assertRedirect(route('client.verify'))
            ->assertSessionHasErrors('code');

        $this->assertDatabaseMissing('users', ['email' => 'client@example.com']);
        $this->assertGuest();

        ClientEmailVerification::query()->where('email', 'client@example.com')->update([
            'expires_at' => now()->subMinute(),
        ]);

        $this->from(route('client.verify'))
            ->post(route('client.verify.store'), ['code' => $code])
            ->assertRedirect(route('client.register'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'client@example.com']);
    }

    public function test_client_must_request_a_new_code_after_five_incorrect_attempts(): void
    {
        Mail::fake();

        $this->post(route('client.register.store'), [
            'name' => 'Travel Client',
            'email' => 'client@example.com',
            'phone_country' => 'MY',
            'phone' => '012-345 6789',
            'password' => 'client-pass-2026',
            'password_confirmation' => 'client-pass-2026',
        ]);

        $code = null;
        Mail::assertSent(ClientEmailVerificationCode::class, function (ClientEmailVerificationCode $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });
        $incorrectCode = str_pad((string) (((int) $code + 1) % 1000000), 6, '0', STR_PAD_LEFT);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from(route('client.verify'))
                ->post(route('client.verify.store'), ['code' => $incorrectCode])
                ->assertRedirect(route('client.verify'))
                ->assertSessionHasErrors('code');
        }

        $this->from(route('client.verify'))
            ->post(route('client.verify.store'), ['code' => $code])
            ->assertRedirect(route('client.verify'))
            ->assertSessionHasErrors('code');

        $this->assertDatabaseMissing('users', ['email' => 'client@example.com']);
        $this->assertDatabaseHas('client_email_verifications', [
            'email' => 'client@example.com',
            'attempts' => 5,
        ]);
    }

    public function test_client_can_resend_an_otp_and_only_the_latest_code_is_valid(): void
    {
        Mail::fake();

        $this->post(route('client.register.store'), [
            'name' => 'Travel Client',
            'email' => 'client@example.com',
            'phone_country' => 'MY',
            'phone' => '012-345 6789',
            'password' => 'client-pass-2026',
            'password_confirmation' => 'client-pass-2026',
        ]);

        $firstCode = null;
        Mail::assertSent(ClientEmailVerificationCode::class, function (ClientEmailVerificationCode $mail) use (&$firstCode): bool {
            $firstCode = $mail->code;

            return true;
        });

        $this->post(route('client.verify.resend'))->assertRedirect(route('client.verify'));

        Mail::assertSentTimes(ClientEmailVerificationCode::class, 2);
        $latestCode = null;
        Mail::assertSent(ClientEmailVerificationCode::class, function (ClientEmailVerificationCode $mail) use ($firstCode, &$latestCode): bool {
            if ($mail->code === $firstCode) {
                return false;
            }

            $latestCode = $mail->code;

            return true;
        });
        $this->assertNotSame($firstCode, $latestCode);

        $this->from(route('client.verify'))
            ->post(route('client.verify.store'), ['code' => $firstCode])
            ->assertRedirect(route('client.verify'))
            ->assertSessionHasErrors('code');

        $this->post(route('client.verify.store'), ['code' => $latestCode])
            ->assertRedirect(route('home'));

        $this->assertAuthenticated();
    }

    public function test_client_login_button_is_rendered_above_the_admin_button(): void
    {
        $response = $this->get(route('home'))->assertOk();
        $content = $response->getContent();
        $clientButtonPosition = strpos($content, 'class="client-portal-fab"');
        $adminButtonPosition = strpos($content, 'class="admin-portal-fab"');

        $this->assertNotFalse($clientButtonPosition);
        $this->assertNotFalse($adminButtonPosition);
        $this->assertLessThan($adminButtonPosition, $clientButtonPosition);
        $response->assertSee('Client login / sign up');
        $response->assertDontSee('data-profile-open', false);
    }

    public function test_profile_button_and_popup_show_the_signed_in_clients_details_before_the_whatsapp_button(): void
    {
        $client = User::factory()->create([
            'name' => 'Travel <b>Client</b>',
            'email' => 'client@example.com',
            'phone' => '+60123456789',
        ]);

        $response = $this->actingAs($client)->get(route('home'))->assertOk();
        $content = $response->getContent();

        $this->assertLessThan(strpos($content, 'class="whatsapp-button"'), strpos($content, 'data-profile-open'));
        $response->assertSee('id="profile-dialog"', false)
            ->assertSee('client@example.com')
            ->assertSee('+60123456789')
            ->assertSee('Travel &lt;b&gt;Client&lt;/b&gt;', false)
            ->assertDontSee('<b>Client</b>', false);
    }

    public function test_profile_button_is_not_shown_to_admins(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('home'))
            ->assertOk()
            ->assertDontSee('data-profile-open', false);
    }
}
