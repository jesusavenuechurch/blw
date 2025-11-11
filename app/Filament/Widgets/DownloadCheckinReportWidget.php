<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Livewire\Attributes\State;
use Carbon\Carbon;
use App\Services\CheckinReportService;

class DownloadCheckinReportWidget extends Widget
{
    protected static string $view = 'filament.widgets.download-checkin-report';
    protected static ?string $heading = 'Download Check-in Report';

    #[State]
    public $startDate;

    #[State]
    public $endDate;

    public function mount(): void
    {
        $this->startDate = Carbon::parse('2025-11-11')->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
    }

    public function downloadReport()
    {
        $service = new CheckinReportService();
        $report = $service->generateExcel(
            Carbon::parse($this->startDate),
            Carbon::parse($this->endDate)
        );

        return response()->streamDownload(
            fn() => print($report['content']),
            $report['filename'],
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }
}