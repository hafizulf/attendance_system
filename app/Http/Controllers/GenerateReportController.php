<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\ColumnDimension;


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

        // Create a new PhpSpreadsheet instance
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Page 1');
        $headings = [
            'Nama',
            'Username',
            'Late Count',
            'Early Leave Count',
            'Work Percentage',
        ];
        $sheet->fromArray([$headings], NULL, 'B4');
        $sheet->getStyle('B4:F4')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);

        // Set data starting from cell B5
        $rowData = [];
        foreach ($datas as $item) {
            $rowData[] = [
                $item['nama'],
                $item['username'],
                (string)$item['late_count'],
                (string)$item['early_leave_count'],
                $item['work_percentage'],
            ];
        }
        $sheet->fromArray($rowData, NULL, 'B5');

        // Set alignment for all cells
        $alignment = [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ];
        $sheet->getStyle('B4:F' . (count($rowData) + 4))->applyFromArray([ // add 4 because we start from B4
            'alignment' => $alignment,
        ]);
        // Set borders for the data range
        $lastDataRow = count($rowData) + 4;
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle("B4:F{$lastDataRow}")->applyFromArray($borderStyle);
        // Auto-size columns based on heading length
        foreach(range('B','F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
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
