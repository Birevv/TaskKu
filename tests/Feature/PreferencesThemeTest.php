<?php

namespace Tests\Feature;

use App\Filament\Pages\Preferences;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PreferencesThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_a_theme_preference(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Preferences::class)
            ->fillForm([
                'theme' => 'dark',
                'density' => 'comfortable',
                'notify_task_assigned' => true,
                'notify_task_due' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertDispatched('taskku-theme-updated', theme: 'dark');

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->getKey(),
            'theme' => 'dark',
            'density' => 'comfortable',
        ]);
    }

    public function test_global_theme_script_uses_the_authenticated_users_preference(): void
    {
        $user = User::factory()->create();
        $user->settings()->create(['theme' => 'dark']);

        $this->actingAs($user);

        $html = view('filament.theme-preference')->render();

        $this->assertStringContainsString("const savedTheme = 'dark';", $html);
        $this->assertStringContainsString("new CustomEvent('theme-changed'", $html);
    }

    public function test_density_preference_is_persisted_and_applied_to_the_document(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Preferences::class)
            ->fillForm([
                'theme' => 'system',
                'density' => 'compact',
                'notify_task_assigned' => true,
                'notify_task_due' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertDispatched('taskku-density-updated', density: 'compact');

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->getKey(),
            'density' => 'compact',
        ]);

        $html = view('filament.theme-preference')->render();

        $this->assertStringContainsString("const savedDensity = 'compact';", $html);
        $this->assertStringContainsString('document.documentElement.dataset.density = savedDensity;', $html);
        $this->assertStringContainsString("window.addEventListener('taskku-density-updated'", $html);
    }
}
