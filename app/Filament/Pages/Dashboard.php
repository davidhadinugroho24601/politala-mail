<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;
// use Filament\Widgets\FilamentInfoWidget;
use App\Filament\Widgets\MailApprovalTimeWidget; // Add your widget
use App\Filament\Widgets\MailStatusChart; // Another custom widget
use App\Filament\Widgets\DailyMailChart; // Another custom widget

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    // protected static ?bool $shouldRegisterNavigation = true;
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
    public function getWidgets(): array
    {
        return [
            AccountWidget::class, // Default Filament widget

            MailApprovalTimeWidget::class, // Add your custom widgets
            MailStatusChart::class,
            DailyMailChart::class,
            // FilamentInfoWidget::class, // Default Filament widget
        ];
    }
}
