<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\TableWidget as BaseTableWidget;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Checkin;
use App\Models\Partner;
use App\Services\CheckinReportService;
use Carbon\Carbon;

class CheckinStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::now()->startOfDay();
        $startDate = Carbon::parse('2025-11-11')->startOfDay();
        $now = Carbon::now();

        $totalCheckins = Checkin::whereBetween('checked_in_at', [$startDate, $now->endOfDay()])->count();
        $todayCheckins = Checkin::whereBetween('checked_in_at', [$today, $now->endOfDay()])->count();
        $withSpouse = Checkin::whereBetween('checked_in_at', [$startDate, $now->endOfDay()])
            ->whereHas('partner', fn($q) => $q->where('coming_with_spouse', 1))
            ->count();

        return [
            Stat::make('Total Check-ins', $totalCheckins)
                ->description('From Nov 11 to today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Today\'s Check-ins', $todayCheckins)
                ->description('Checked in today')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info')
                ->icon('heroicon-o-calendar'),

            Stat::make('With Spouse', $withSpouse)
                ->description('Total check-ins with spouse')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning')
                ->icon('heroicon-o-users'),
        ];
    }
}