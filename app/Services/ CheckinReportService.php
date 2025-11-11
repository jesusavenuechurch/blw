<?php

namespace App\Services;


use App\Models\Checkin;
use App\Models\Partner;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CheckinReportService
{
    public function generateExcel($startDate = null, $endDate = null)
    {
        // Default: Monday 11th to today
        $startDate = $startDate ?? Carbon::parse('2025-11-11');
        $endDate = $endDate ?? Carbon::now();

        // Get check-ins with partner data
        $checkins = Checkin::whereBetween('checked_in_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->with('partner')
            ->orderBy('checked_in_at', 'desc')
            ->get();

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Check-ins Report');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);

        // Header row
        $headers = ['Date', 'Partner Name', 'Tier', 'Check-in Time', 'With Spouse', 'Registered'];
        $sheet->fromArray([$headers], null, 'A1');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D4AF37']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        foreach (['A1', 'B1', 'C1', 'D1', 'E1', 'F1'] as $cell) {
            $sheet->getStyle($cell)->applyFromArray($headerStyle);
        }

        // Add data rows
        $row = 2;
        $lastDate = null;
        $dateRowStart = null;

        foreach ($checkins as $checkin) {
            $currentDate = $checkin->checked_in_at->format('Y-m-d');

            // Add a blank row between dates (optional visual separator)
            if ($lastDate && $lastDate !== $currentDate) {
                $row++;
                $dateRowStart = $row;
            }

            $sheet->setCellValue('A' . $row, $checkin->checked_in_at->format('M d, Y'));
            $sheet->setCellValue('B' . $row, $checkin->partner->full_name ?? 'N/A');
            $sheet->setCellValue('C' . $row, $checkin->partner->tier_display ?? 'N/A');
            $sheet->setCellValue('D' . $row, $checkin->checked_in_at->format('H:i A'));
            $sheet->setCellValue('E' . $row, $checkin->partner->coming_with_spouse ? 'Yes' : 'No');
            $sheet->setCellValue('F' . $row, $checkin->partner->created_at->format('M d, Y'));

            // Alternate row colors
            if ($row % 2 == 0) {
                $fillStyle = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F5']]];
                foreach (['A' . $row, 'B' . $row, 'C' . $row, 'D' . $row, 'E' . $row, 'F' . $row] as $cell) {
                    $sheet->getStyle($cell)->applyFromArray($fillStyle);
                }
            }

            // Center align specific columns
            foreach (['A' . $row, 'C' . $row, 'D' . $row, 'E' . $row, 'F' . $row] as $cell) {
                $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            $lastDate = $currentDate;
            $row++;
        }

        // Add summary at bottom
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Total Check-ins:');
        $sheet->setCellValue('B' . $row, $checkins->count());
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);

        // Breakdown by tier
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Breakdown by Tier:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);

        $tierCounts = $checkins->groupBy('partner.tier_display')->map->count();
        foreach ($tierCounts as $tier => $count) {
            $row++;
            $sheet->setCellValue('A' . $row, $tier ?? 'N/A');
            $sheet->setCellValue('B' . $row, $count);
            $sheet->getStyle('A' . $row)->getAlignment()->setIndent(1);
        }

        // With spouse count
        $row += 2;
        $withSpouse = $checkins->where('partner.coming_with_spouse', 1)->count();
        $sheet->setCellValue('A' . $row, 'With Spouse:');
        $sheet->setCellValue('B' . $row, $withSpouse);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);

        // Generate file
        $writer = new Xlsx($spreadsheet);
        $filename = 'checkins-report-' . Carbon::now()->format('Y-m-d-His') . '.xlsx';
        
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return [
            'content' => $content,
            'filename' => $filename,
        ];
    }
}