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

class CheckinsByDayWidget extends BaseTableWidget
{
    protected static ?string $heading = 'Recent Check-ins';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Checkin::query()
                    ->with('partner')
                    ->whereDate('checked_in_at', '>=', Carbon::parse('2025-11-11'))
                    ->orderBy('checked_in_at', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('checked_in_at')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('partner.full_name')
                    ->label('Partner Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('partner.tier_display')
                    ->label('Tier')
                    ->sortable(),

                Tables\Columns\TextColumn::make('checked_in_at')
                    ->label('Check-in Time')
                    ->time('H:i A')
                    ->sortable(),

                Tables\Columns\BooleanColumn::make('partner.coming_with_spouse')
                    ->label('With Spouse')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\TextColumn::make('partner.created_at')
                    ->label('Registered')
                    ->date('M d, Y')
                    ->sortable(),
            ])
            ->paginated([10, 25, 50]);
    }
}