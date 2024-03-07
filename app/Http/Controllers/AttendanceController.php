<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function updateCheckIn(Request $request, string $id) : JsonResponse {
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

        $currentDateTime = Carbon::now();
        $timeInAdjusted = $currentDateTime->addHours(7);
        $timeInFormatted = $timeInAdjusted->format('H:i:s');

        $attendance = new Attendance();
        $attendance->employee_id = $id;
        $attendance->date = $currentDateTime->toDateString();
        $attendance->time_in = $timeInFormatted;
        $attendance->save();

        return response()->json([
            'status' => 200,
            'message' => 'Employee check-in recorded',
        ]);
    }
}
