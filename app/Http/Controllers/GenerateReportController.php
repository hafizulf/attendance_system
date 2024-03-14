<?php

namespace App\Http\Controllers;

use App\Exports\AttendancesExport;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;


class GenerateReportController extends ReportController
{
    public function generateAttendancesExcelFile(Request $request) {
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

        $data = [];
        foreach($datas as $item) {
            $data[] = [
                'Nama' => $item['nama'],
                'Username' => $item['username'],
                'Late Count' => (string)$item['late_count'],
                'Early Leave Count' => (string)$item['early_leave_count'],
                'Work Percentage' => $item['work_percentage'],
            ];
        }
        // Export data to Excel
        return Excel::download(new AttendancesExport($data), 'attendance.xlsx');
    }
}
