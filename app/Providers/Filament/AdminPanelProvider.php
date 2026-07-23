<?php

namespace App\Providers\Filament;

use App\Auth\AdminEmailAuthentication;
use App\Filament\Auth\AdminLogin;
use App\Filament\Pages\AdminDashboard;
use App\Filament\Pages\ChangePassword;
use App\Filament\Pages\EditProfile;
use App\Notifications\AdminLoginOtpNotification;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->maxContentWidth(Width::Full)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login(AdminLogin::class)
            ->multiFactorAuthentication([
                AdminEmailAuthentication::make()
                    ->codeExpiryMinutes(10)
                    ->codeNotification(AdminLoginOtpNotification::class),
            ])
            ->profile(EditProfile::class, isSimple: false)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->brandLogo(fn() => view('global.logo'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                AdminDashboard::class,
                ChangePassword::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->navigationGroups([
                NavigationGroup::make('Sales'),
                NavigationGroup::make('Market'),
                NavigationGroup::make('Website'),
                NavigationGroup::make('Settings'),
            ])


            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label('Edit Profile')
                    ->icon('heroicon-o-user-circle')
                    ->url(fn (): string => EditProfile::getUrl()),
                MenuItem::make()
                    ->label('Change Password')
                    ->icon('heroicon-o-lock-closed')
                    ->url(fn(): string => ChangePassword::getUrl())
                    ->openUrlInNewTab(false)
                    ->sort(10),
            ])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => view('filament.hooks.topbar-actions')->render(),
            );
    }
}
