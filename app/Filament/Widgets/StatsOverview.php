<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected ?string $heading = 'Analytics';
    protected static ?int $sort = 1;

    protected ?string $description = 'An overview of some analytics.';
    protected function getStats(): array
    {
        return [
            Stat::make('Unique views', '192.2k')
                ->description('Unique views in the last 30 days')
                ->descriptionIcon('heroicon-o-eye')
                ->descriptionColor('success')
                ->chart([40, 50, 10, 30]),
            Stat::make('Total views', '1.2m')
                ->description('Total views in the last 30 days')
                ->descriptionIcon('heroicon-o-eye')
                ->descriptionColor('success')
                ->chart([40, 30, 40, 50]),
            Stat::make('Conversion rate', '2.2%')
                ->description('Conversion rate in the last 30 days')
                ->descriptionIcon('heroicon-o-eye')
                ->descriptionColor('success')
                ->chart([40, 1, 500, 100]),
        ];
    }
}
