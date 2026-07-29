<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\Notifications\NoticeOfEmailChangeRequest;
use Filament\Auth\Notifications\VerifyEmailChange;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_enables_profile_email_verification_and_recoverable_app_authentication(): void
    {
        $panel = Filament::getPanel('app');
        $providers = $panel->getMultiFactorAuthenticationProviders();

        $this->assertTrue($panel->hasProfile());
        $this->assertSame(EditProfile::class, $panel->getProfilePage());
        $this->assertTrue($panel->hasEmailVerification());
        $this->assertTrue($panel->hasEmailChangeVerification());
        $this->assertArrayHasKey('app', $providers);
        $this->assertInstanceOf(AppAuthentication::class, $providers['app']);
        $this->assertTrue($providers['app']->isRecoverable());
    }

    public function test_user_can_update_name_password_and_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->setFilamentUser($user);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => 'Updated User',
                'email' => $user->email,
                'avatar_path' => UploadedFile::fake()->image('avatar.jpg', 300, 300),
                'password' => 'A-stronger-password-2026',
                'passwordConfirmation' => 'A-stronger-password-2026',
                'currentPassword' => 'password',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();

        $this->assertSame('Updated User', $user->name);
        $this->assertTrue(Hash::check('A-stronger-password-2026', $user->password));
        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);
        $this->assertStringContainsString('/storage/avatars/', $user->getFilamentAvatarUrl());
    }

    public function test_user_can_verify_their_email_address(): void
    {
        $user = User::factory()->unverified()->create();
        $this->setFilamentUser($user);

        $verificationUrl = Filament::getPanel('app')->getVerifyEmailUrl($user);

        $this->get($verificationUrl)->assertRedirect();

        $this->assertTrue($user->refresh()->hasVerifiedEmail());
    }

    public function test_email_change_is_only_applied_after_the_new_address_is_verified(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $originalEmail = $user->email;
        $newEmail = 'updated@example.com';
        $this->setFilamentUser($user);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => $user->name,
                'email' => $newEmail,
                'avatar_path' => null,
                'password' => null,
                'passwordConfirmation' => null,
                'currentPassword' => 'password',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($originalEmail, $user->refresh()->email);
        Notification::assertSentTo($user, NoticeOfEmailChangeRequest::class);

        $verificationUrl = null;

        Notification::assertSentOnDemand(
            VerifyEmailChange::class,
            function (VerifyEmailChange $notification) use (&$verificationUrl): bool {
                $verificationUrl = $notification->url;

                return true;
            },
        );

        $this->assertNotNull($verificationUrl);
        $this->get($verificationUrl)->assertRedirect();
        $this->assertSame($newEmail, $user->refresh()->email);
    }

    public function test_app_authentication_secret_and_recovery_codes_are_secure_and_recovery_is_single_use(): void
    {
        $user = User::factory()->create();
        $this->setFilamentUser($user);

        /** @var AppAuthentication $provider */
        $provider = Filament::getPanel('app')->getMultiFactorAuthenticationProviders()['app'];
        $secret = $provider->generateSecret();
        $recoveryCodes = [
            'first-recovery-code',
            'second-recovery-code',
        ];

        $provider->saveSecret($user, $secret);
        $provider->saveRecoveryCodes($user, $recoveryCodes);

        $user->refresh();
        $storedCodes = $user->getAppAuthenticationRecoveryCodes();

        $this->assertSame($secret, $user->getAppAuthenticationSecret());
        $this->assertNotSame($secret, $user->getRawOriginal('app_authentication_secret'));
        $this->assertTrue($provider->isEnabled($user));
        $this->assertTrue($provider->verifyCode($provider->getCurrentCode($user), $secret));
        $this->assertCount(2, $storedCodes);
        $this->assertTrue(Hash::check($recoveryCodes[0], $storedCodes[0]));
        $this->assertTrue($provider->verifyRecoveryCode($recoveryCodes[0], $user));
        $this->assertFalse($provider->verifyRecoveryCode($recoveryCodes[0], $user));
        $this->assertCount(1, $user->refresh()->getAppAuthenticationRecoveryCodes());
    }

    private function setFilamentUser(User $user): void
    {
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant(null);
    }
}
