<?php

namespace App\Providers\Filament;

use App\Filament\Dashboard;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Register;
use App\Filament\Pages\Tenancy\EditWorkspaceProfile;
use App\Filament\Pages\Tenancy\RegisterWorkspace;
use App\Models\Workspace;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->viteTheme('resources/css/filament/app/theme.css')
            ->brandName('Taskku')
            ->brandLogo(fn () => view('filament.components.logo'))
            ->brandLogoHeight('2.25rem')
            ->login(Login::class)
            ->registration(Register::class)
            ->passwordReset()
            ->profile(EditProfile::class, isSimple: false)
            ->emailVerification()
            ->emailChangeVerification()
            ->multiFactorAuthentication([
                AppAuthentication::make()
                    ->brandName('Taskku')
                    ->recoverable(),
            ])
            ->databaseNotifications()
            ->topbar(false)
            ->sidebarWidth('17rem')
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn () => view('filament.theme-preference'),
            )
            ->renderHook(
                PanelsRenderHook::SIMPLE_PAGE_START,
                fn () => view('filament.auth.back-to-home'),
                scopes: [Login::class, Register::class],
            )
            ->tenant(
                Workspace::class,
                slugAttribute: 'slug',
            )
            ->navigation(fn (): bool => Filament::getTenant() instanceof Workspace)
            ->tenantMenu(fn (): bool => Filament::getTenant() instanceof Workspace)
            ->tenantRoutePrefix('workspaces')
            ->tenantRegistration(RegisterWorkspace::class)
            ->tenantProfile(EditWorkspaceProfile::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
