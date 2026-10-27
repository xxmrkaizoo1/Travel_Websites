<?php

namespace Tests\Feature;

use App\Mail\ClientEmailVerificationCode;
use App\Mail\ClientPasswordResetCode;
use App\Models\ClientPasswordReset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ClientPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_resets_a_forgotten_password_with_an_emailed_code(): void
    {
        Mail::fake();
        $client = User::factory()->create(['name' => 'Aisyah Rahman', 'email' => 'aisyah@example.com']);

        $this->get(route('client.login'))->assertSee('href="'.route('client.password.request').'"', false);

        $this->post(route('client.password.email'), ['email' => 'Aisyah@Example.com'])
            ->assertRedirect(route('client.password.reset'))
            ->assertSessionHas('status', 'If an account uses that email, we have sent it a 6-digit code.');

        $code = $this->sentCode('aisyah@example.com');
        $this->get(route('client.password.reset'))->assertOk()->assertSee('aisyah@example.com');

        $this->post(route('client.password.store'), ['code' => $code, 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
            ->assertRedirect(route('client.login'))
            ->assertSessionHas('status', 'Your password has been reset. Sign in with your new password.');

        $this->assertTrue(Hash::check('new-password-1', $client->fresh()->password));
        $this->assertDatabaseCount('client_password_resets', 0);
        $this->get(route('client.password.reset'))->assertRedirect(route('client.password.request'));

        $this->post(route('client.login.store'), ['email' => 'aisyah@example.com', 'password' => 'new-password-1'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($client);
    }

    public function test_unknown_and_admin_emails_get_the_same_answer_but_no_code(): void
    {
        Mail::fake();
        User::factory()->admin()->create(['email' => 'admin@example.com']);

        foreach (['nobody@example.com', 'admin@example.com'] as $email) {
            $this->post(route('client.password.email'), ['email' => $email])
                ->assertRedirect(route('client.password.reset'))
                ->assertSessionHas('status', 'If an account uses that email, we have sent it a 6-digit code.');
        }

        Mail::assertNothingSent();
        $this->assertDatabaseCount('client_password_resets', 0);

        $this->post(route('client.password.store'), ['code' => '123456', 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
            ->assertSessionHasErrors(['code' => 'That code is incorrect or has expired. Request a new code.']);
    }

    public function test_wrong_expired_and_repeated_codes_are_refused(): void
    {
        Mail::fake();
        $client = User::factory()->create(['email' => 'aisyah@example.com']);
        $newPassword = ['password' => 'new-password-1', 'password_confirmation' => 'new-password-1'];

        $this->post(route('client.password.email'), ['email' => 'aisyah@example.com']);
        $code = $this->sentCode('aisyah@example.com');
        $wrongCode = $code === '000000' ? '111111' : '000000';

        $this->post(route('client.password.store'), ['code' => $wrongCode, ...$newPassword])
            ->assertSessionHasErrors(['code' => 'That code is incorrect.']);

        ClientPasswordReset::query()->update(['attempts' => 5]);
        $this->post(route('client.password.store'), ['code' => $code, ...$newPassword])
            ->assertSessionHasErrors(['code' => 'Too many incorrect codes. Request a new code to continue.']);

        ClientPasswordReset::query()->update(['attempts' => 0, 'expires_at' => now()->subMinute()]);
        $this->post(route('client.password.store'), ['code' => $code, ...$newPassword])
            ->assertSessionHasErrors(['code' => 'Your code has expired. Request a new code to continue.']);

        $this->assertDatabaseCount('client_password_resets', 0);
        $this->assertTrue(Hash::check('password', $client->fresh()->password));
    }

    public function test_a_new_code_replaces_the_old_one(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'aisyah@example.com']);

        $this->post(route('client.password.email'), ['email' => 'aisyah@example.com']);
        $firstCode = $this->sentCode('aisyah@example.com');

        $this->post(route('client.password.resend'))->assertRedirect(route('client.password.reset'));
        Mail::assertSentCount(2);
        $latestCode = Mail::sent(ClientPasswordResetCode::class)->last()->code;

        if ($firstCode !== $latestCode) {
            $this->post(route('client.password.store'), ['code' => $firstCode, 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
                ->assertSessionHasErrors(['code' => 'That code is incorrect.']);
        }

        $this->post(route('client.password.store'), ['code' => $latestCode, 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
            ->assertRedirect(route('client.login'));
    }

    public function test_new_password_must_be_confirmed_and_long_enough(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'aisyah@example.com']);
        $this->post(route('client.password.email'), ['email' => 'aisyah@example.com']);

        $this->post(route('client.password.store'), ['code' => $this->sentCode('aisyah@example.com'), 'password' => 'short', 'password_confirmation' => 'different'])
            ->assertSessionHasErrors('password');
    }

    public function test_code_emails_carry_the_smart_holidays_branding(): void
    {
        foreach ([new ClientEmailVerificationCode('Aisyah', '493017'), new ClientPasswordResetCode('Aisyah', '493017')] as $mail) {
            $html = $mail->render();

            $this->assertStringContainsString('493017', $html);
            $this->assertStringContainsString('SMART HOLIDAYS', $html);
            $this->assertStringContainsString('Hello Aisyah,', $html);
            $this->assertStringNotContainsString('Laravel', $html);
        }
    }

    private function sentCode(string $email): string
    {
        $code = null;

        Mail::assertSent(ClientPasswordResetCode::class, function (ClientPasswordResetCode $mail) use ($email, &$code): bool {
            $code = $mail->code;

            return $mail->hasTo($email);
        });

        return $code;
    }
}
