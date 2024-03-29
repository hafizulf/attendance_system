<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DateTime;

class ReportController extends Controller
{
    protected $attendanceModel;
    public function __construct(Attendance $attendanceModel) {
        $this->attendanceModel = $attendanceModel;
    }

    public function dateRangeReport(Request $request) : JsonResponse {
        $currentDate = date('Y-m-d');
        $currentYear = date('Y', strtotime($currentDate));
        $currentMonth = date('m', strtotime($currentDate));
        $startDate = $request->has('startDate') ? $request->input('startDate') : "$currentYear-$currentMonth-01";
        $lastDayOfMonth = date('t', strtotime($currentDate));
        $endDate = $request->has('endDate') ? $request->input('endDate') : "$currentYear-$currentMonth-$lastDayOfMonth";

        $employees = Employee::select('id', 'full_name', 'username')->get();
        $attendancesByEmployee = $this->attendanceModel->getAttendancesWithGroupByEmployee($startDate, $endDate);
        $tempAttendances = $this->_transformReport($startDate, $endDate, $employees, $attendancesByEmployee);

        return response()->json([
            'status' => 200,
            'data' => array_values($tempAttendances),
        ]);
    }

    public function monthlyReport(Request $request, string $month) : JsonResponse {
        $monthNumber = date('m', strtotime("1 $month"));
        $year = date('Y');
        $startDate = "$year-$monthNumber-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $employees = Employee::select('id', 'full_name', 'username')->get();
        $attendancesByEmployee = $this->attendanceModel->getAttendancesWithGroupByEmployee($startDate, $endDate);
        $tempAttendances = $this->_transformReport($startDate, $endDate, $employees, $attendancesByEmployee);

        return response()->json([
            'status' => 200,
            'data' => array_values($tempAttendances),
        ]);
    }

    /**
     * Transform report data.
     *
     * @param string $startDate The start date.
     * @param string $endDate   The end date.
     * @param mixed ...$args    Employee, attendances
     * @return array            Transformed data array.
     */
    protected function _transformReport(string $startDate, string $endDate, ...$args) : array {
        $employees = $args[0];
        $attendances = $args[1];
        $tempAttendances = [];
        foreach ($employees as $employee) {
            $tempAttendances[$employee->id] = [
                'id' => $employee->id,
                'nama' => $employee->full_name,
                'username' => $employee->username,
                'late_count' => 0,
                'early_leave_count' => 0,
                'work_percentage' => 0,
                'attendances' => [],
            ];

            foreach ($attendances[$employee->id] ?? [] as $attendance) {
                $timeInHoursMinutes = date('H:i', strtotime($attendance->time_in));
                $timeOutHoursMinutes = date('H:i', strtotime($attendance->time_out));
                $lateThreshold = env('LATE_THRESHOLD', '08:00');
                $earlyLeaveThreshold = env('EARLY_LEAVE_THRESHOLD', '17:00');
                $late = ($timeInHoursMinutes > $lateThreshold) ? true : false;
                $earlyLeave = ($timeOutHoursMinutes < $earlyLeaveThreshold) ? true : false;

                 // Check if the attendance date is a holiday
                $attendanceDate = $attendance->date;
                $isHoliday = $this->_isHoliday($attendanceDate);

                $tempAttendances[$employee->id]['attendances'][] = [
                    'date' => $attendanceDate,
                    'time_in' => $timeInHoursMinutes,
                    'time_out' => $timeOutHoursMinutes,
                    'late' => $late,
                    'early_leave' => $earlyLeave,
                    'is_holiday' => $isHoliday,
                ];

                if ($late) {
                    $tempAttendances[$employee->id]['late_count']++;
                }
                if ($earlyLeave) {
                    $tempAttendances[$employee->id]['early_leave_count']++;
                }
            }

            // Calculate absence percentage
            $startDateTime = new DateTime($startDate);
            $endDateTime = new DateTime($endDate);
            $totalDays = $startDateTime->diff($endDateTime)->days + 1;
            $absenceDays = $totalDays - sizeof($tempAttendances[$employee->id]['attendances']);
            $absencePercentage = $totalDays > 0 ? (($totalDays - $absenceDays) / $totalDays) * 100 : 0;
            $tempAttendances[$employee->id]['work_percentage'] = number_format($absencePercentage, 2) . '%';
        }

        return $tempAttendances;
    }


    private function _isHoliday(string $date) : bool {
        $holidayExist = Holiday::where('date', $date)->get()->first();
        if($holidayExist) {
            return true;
        }
        return false;
    }

    public function yearlyReport(string $year) : JsonResponse {
        $startDate = "$year-01-01";
        $endDate = "$year-12-31";
        $employees = Employee::select('id', 'full_name', 'username')->get();
        $attendancesByEmployee = $this->attendanceModel->getAttendancesWithGroupByEmployee($startDate, $endDate);
        $tempAttendances = $this->_transformReport($startDate, $endDate, $employees, $attendancesByEmployee);

        return response()->json([
            'status' => 200,
            'data' => array_values($tempAttendances),
        ]);
    }

    public function employeeReport(Request $request, string $employeeId) : JsonResponse {
        $currentDate = date('Y-m-d');
        $currentYear = date('Y', strtotime($currentDate));
        $currentMonth = date('m', strtotime($currentDate));
        $startDate = $request->has('startDate') ? $request->input('startDate') : "$currentYear-$currentMonth-01";
        $lastDayOfMonth = date('t', strtotime($currentDate));
        $endDate = $request->has('endDate') ? $request->input('endDate') : "$currentYear-$currentMonth-$lastDayOfMonth";

        $employee = Employee::find($employeeId);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $tempAttendances = [
            'id' => $employee->id,
            'nama' => $employee->full_name,
            'username' => $employee->username,
            'late_count' => 0,
            'early_leave_count' => 0,
            'work_percentage' => 0,
            'attendances' => [],
        ];

        foreach ($attendances as $attendance) {
            $timeInHoursMinutes = date('H:i', strtotime($attendance->time_in));
            $timeOutHoursMinutes = date('H:i', strtotime($attendance->time_out));
            $lateThreshold = env('LATE_THRESHOLD', '08:00');
            $earlyLeaveThreshold = env('EARLY_LEAVE_THRESHOLD', '17:00');
            $late = ($timeInHoursMinutes > $lateThreshold);
            $earlyLeave = ($timeOutHoursMinutes < $earlyLeaveThreshold);
            $attendanceDate = $attendance->date;
            $isHoliday = $this->_isHoliday($attendanceDate);

            $tempAttendances['attendances'][] = [
                'date' => $attendanceDate,
                'time_in' => $timeInHoursMinutes,
                'time_out' => $timeOutHoursMinutes,
                'late' => $late,
                'early_leave' => $earlyLeave,
                'is_holiday' => $isHoliday,
            ];

            if ($late) {
                $tempAttendances['late_count']++;
            }
            if ($earlyLeave) {
                $tempAttendances['early_leave_count']++;
            }
        }

        // Calculate absence percentage
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
        $totalDays = $startDateTime->diff($endDateTime)->days + 1;
        $absenceDays = $totalDays - sizeof($tempAttendances['attendances']);
        $absencePercentage = $totalDays > 0 ? (($totalDays - $absenceDays) / $totalDays) * 100 : 0;
        $tempAttendances['work_percentage'] = number_format($absencePercentage, 2) . '%';

        return response()->json([
            'status' => 200,
            'data' => $tempAttendances,
        ]);
    }
}
