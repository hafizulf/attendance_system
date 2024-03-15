<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;


class GenerateReportController extends ReportController
{
    public function generateAttendancesExcelFile(Request $request)
    {
        $currentDate = date('Y-m-d');
        $currentYear = date('Y', strtotime($currentDate));
        $currentMonth = date('m', strtotime($currentDate));
        $startDate = $request->has('startDate') ? $request->input('startDate') : "$currentYear-$currentMonth-01";
        $lastDayOfMonth = date('t', strtotime($currentDate));
        $endDate = $request->has('endDate') ? $request->input('endDate') : "$currentYear-$currentMonth-$lastDayOfMonth";

        $employees = Employee::select('id', 'full_name', 'username')->get();
        $attendanceModel = new Attendance();
        $attendancesByEmployee = $attendanceModel->getAttendancesWithGroupByEmployee($startDate, $endDate);
        $reportData = $this->_transformReport($startDate, $endDate, $employees, $attendancesByEmployee);
        $datas = array_values($reportData);

        $spreadsheet = new Spreadsheet(); // Create a new PhpSpreadsheet instance
        $sheet = $spreadsheet->getActiveSheet();
        // $sheet->setTitle('Page 1');
        $subtitle = 'Attendance Report - ' . date('d/m/Y', strtotime($startDate)) . ' to ' . date('d/m/Y', strtotime($endDate));
        $sheet->setCellValue('C2', $subtitle);
        $sheet->mergeCells('C2:G2'); // Merge cells for the subtitle
        $sheet->getStyle('C2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);


        $headings = [
            'Nama',
            'Username',
            'Late Count',
            'Early Leave Count',
            'Work Percentage',
        ];
        $sheet->fromArray([$headings], NULL, 'C4');
        $sheet->getStyle('C4:G4')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Auto-size columns for headings
        foreach(range('C','G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $headingWidths = []; // Array to store heading widths
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        $currentRow = 5; // Starting row for data
        $nextHeadingRow = $currentRow + 30; // Row to start the next heading (initially 30 rows below the data)
        $skippedRows = 0; // Count of skipped rows
        $firstBlock = true; // Flag to track the first block

        // Loop through the data and add to the sheet
        foreach ($datas as $item) {
            if ($firstBlock && $skippedRows >= 28) { // Check if we need to start the next heading for the first block
                // Add the next heading with a 2-row space
                $nextHeadingRow = $currentRow + 2; // Set the next heading row with 2 rows space
                $sheet->fromArray([$headings], NULL, 'C' . $nextHeadingRow);

                // Auto-size columns for the new heading
                foreach(range('C','G') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $headingWidths = []; // Reset heading widths
                foreach (range('C', 'G') as $col) {
                    $headingWidths[$col] = $sheet->getColumnDimension($col)->getWidth();
                }
                $sheet->getStyle('C' . $nextHeadingRow . ':G' . $nextHeadingRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'borders' => [ // Add borders to the heading
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [ // Apply alignment to the heading
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                $currentRow = $nextHeadingRow + 1; // Start adding data below the heading
                $nextHeadingRow = $currentRow + 30; // Set the next heading row for the next page
                $skippedRows = 0; // Reset skipped rows count
                $firstBlock = false; // Update flag for subsequent blocks
            } elseif (!$firstBlock && $skippedRows >= 30) { // Check if we need to start the next heading for subsequent blocks
                // Add the next heading with a 2-row space
                $nextHeadingRow = $currentRow + 2; // Set the next heading row with 2 rows space
                $sheet->fromArray([$headings], NULL, 'C' . $nextHeadingRow);
                // Apply the stored heading widths to the new heading row
                foreach ($headingWidths as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }
                $sheet->getStyle('C' . $nextHeadingRow . ':G' . $nextHeadingRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'borders' => [ // Add borders to the heading
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [ // Apply alignment to the heading
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                $currentRow = $nextHeadingRow + 1; // Start adding data below the heading
                $nextHeadingRow = $currentRow + 30; // Set the next heading row for the next page
                $skippedRows = 0; // Reset skipped rows count
            }

            $rowData = [
                'Nama' => $item['nama'],
                'Username' => $item['username'],
                'Late Count' => (string)$item['late_count'],
                'Early Leave Count' => (string)$item['early_leave_count'],
                'Work Percentage' => $item['work_percentage'],
            ];

            // Add data to the sheet
            $sheet->fromArray([$rowData], NULL, 'C' . $currentRow);

            // Apply alignment to data cells based on heading widths
            foreach (range('C', 'G') as $col) {
                $sheet->getStyle($col . $currentRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            }

            // Apply border to data rows
            $sheet->getStyle('C' . $currentRow . ':G' . $currentRow)->applyFromArray($borderStyle);

            $currentRow++;
            $skippedRows++;
        }

        // Create a writer object and export the spreadsheet
        $writer = new Xlsx($spreadsheet);
        $filename = 'attendance.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($temp_file);

        // Download the file
        return response()->download($temp_file, $filename)->deleteFileAfterSend(true);
    }
}
