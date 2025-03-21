<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Http\Middleware\CheckGroupIDSession;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\RedirectFilamentToBreezeLogin;
use App\Http\Middleware\HideAdminNavigation;
use Filament\Navigation\MenuItem;
use Filament\Pages\Auth\EditProfile;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;



class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->brandName('POLITALA Mail')
            ->path('admin')
            
            ->login(false)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware($this->getAdminMiddleware())
            ->authMiddleware([
                Authenticate::class,
            ])
            ->profile(EditProfile::class, isSimple: false)
            ->navigationItems([
                NavigationItem::make()->label('Daftar Role')
                ,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                     ->label('Kirim Surat')
                    //  ->collapsible(false)
                     ,
                NavigationGroup::make()
                    ->label('Surat Masuk')
                    // ->collapsible(false)
                    ,
            ]);

    }

    private function getAdminMiddleware(): array
    {
        $middleware = [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
            RedirectFilamentToBreezeLogin::class,
            HideAdminNavigation::class,
        ];

        // Apply CheckGroupIDSession to ALL `/admin/*` EXCEPT `/admin/your-roles*`
        if ((request()->is('admin/*' ) || request()->is('admin' ) )&& !request()->is('admin/your-roles*') && !request()->is('admin/login')) {
            $middleware[] = CheckGroupIDSession::class;
        }

        return $middleware;
    }

    
}

