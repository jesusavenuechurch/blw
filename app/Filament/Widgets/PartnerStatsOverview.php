<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Checkin;
use App\Models\Partner;
use Carbon\Carbon;

class PartnerStatsOverview extends ChartWidget
{
    protected static ?string $heading = 'Partner Stats (Last 7 Days)';

    protected function getData(): array
    {
        // Labels for last 7 days
        $labels = collect(range(0, 6))
            ->map(fn($i) => Carbon::now()->subDays($i)->format('D')) // e.g. Mon, Tue, Wed
            ->reverse()
            ->values();

        // Check-ins per day
        // $checkins = collect(range(0, 6))
        //     ->map(fn($i) => Checkin::whereDate('checked_in_at', Carbon::now()->subDays($i))->count())
        //     ->reverse()
        //     ->values();

        // Registrations per day
        $registrations = collect(range(0, 6))
            ->map(fn($i) => Partner::whereDate('created_at', Carbon::now()->subDays($i))->count())
            ->reverse()
            ->values();

        return [
            'datasets' => [
                // [
                //     'label' => 'Check-ins',
                //     'data' => $checkins,
                //     'backgroundColor' => '#fbbf24', // gold color
                // ],
                [
                    'label' => 'Registrations',
                    'data' => $registrations,
                    'backgroundColor' => '#60a5fa', // blue tone
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