<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Register;
use App\Models\User;
use App\Models\Workspace;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\Notifications\NoticeOfEmailChangeRequest;
use Filament\Auth\Notifications\VerifyEmailChange;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_disables_registration_email_verification_but_preserves_email_change_and_account_security(): void
    {
        $panel = Filament::getPanel('app');
        $providers = $panel->getMultiFactorAuthenticationProviders();

        $this->assertTrue($panel->hasProfile());
        $this->assertSame(EditProfile::class, $panel->getProfilePage());
        $this->assertFalse($panel->hasEmailVerification());
        $this->assertTrue($panel->hasEmailChangeVerification());
        $this->assertFalse(is_a(User::class, MustVerifyEmail::class, true));
        $this->assertFalse(Route::has('filament.app.auth.email-verification.verify'));
        $this->assertArrayHasKey('app', $providers);
        $this->assertInstanceOf(AppAuthentication::class, $providers['app']);
        $this->assertTrue($providers['app']->isRecoverable());
    }

    public function test_profile_page_is_available_outside_a_tenant_route(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::create([
            'owner_id' => $user->getKey(),
            'name' => 'Personal Workspace',
            'slug' => 'personal-workspace',
        ]);
        $workspace->members()->attach($user, [
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/app/profile')
            ->assertOk();
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

    public function test_registration_does_not_send_email_verification_and_authenticates_the_user(): void
    {
        Notification::fake();
        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant(null);

        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'New User',
                'email' => 'new-user@example.com',
                'password' => 'A-secure-password-2026',
                'passwordConfirmation' => 'A-secure-password-2026',
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $user = User::query()
            ->where('email', 'new-user@example.com')
            ->first();

        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check('A-secure-password-2026', $user->password));
        $this->assertNotSame('A-secure-password-2026', $user->password);
        $this->assertAuthenticatedAs($user);
        Notification::assertNothingSent();
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
