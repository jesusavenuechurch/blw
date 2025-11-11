<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\TableWidget as BaseTableWidget;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\CheckIn;
use App\Models\Partner;
use App\Services\CheckinReportService;
use Carbon\Carbon;

class PartnerStatsOverview extends ChartWidget
{
    protected static ?string $heading = 'Check-ins by Day (Last 7 Days)';

    protected function getData(): array
    {
        $labels = collect(range(0, 6))
            ->map(fn($i) => Carbon::now()->subDays($i)->format('D, M d'))
            ->reverse()
            ->values();

        $checkins = collect(range(0, 6))
            ->map(fn($i) => CheckIn::whereDate('checked_in_at', Carbon::now()->subDays($i))->count())
            ->reverse()
            ->values();

        return [
            'datasets' => [
                [
                    'label' => 'Check-ins',
                    'data' => $checkins,
                    'backgroundColor' => '#D4AF37',
                    'borderColor' => '#B8860B',
                    'borderWidth' => 1,
                    'borderRadius' => 5,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
