<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function checkIn(Request $request, string $id) : JsonResponse {
        if (!intval($id)) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid ID format. ID must be a numeric.',
            ], 400);
        }
        try {
            $employee = Employee::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'message' => 'Employee not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'pin' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if($request->input('pin') !== $employee['pin']) {
            return response()->json([
                'status' => 400,
                'errors' => [
                    'pin' => [
                        'Invalid PIN. Please try again.',
                    ]
                ],
            ], 400);
        }

        $currentDate = date('Y-m-d');
        $employeeId = $id;
        $existingAttendance = Attendance::where('employee_id', $employeeId)
            ->whereDate('date', $currentDate)
            ->first();

        if (!$existingAttendance) {
            $currentDateTime = date('Y-m-d H:i:s');
            $attendance = new Attendance();
            $attendance->employee_id = $employeeId;
            $attendance->date = $currentDate;
            $attendance->time_in = date('H:i:s', strtotime($currentDateTime));
            $attendance->save();

            return response()->json([
                'status' => 200,
                'message' => 'Employee check-in recorded',
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Employee has already checked in today',
            ]);
        }
    }
}
