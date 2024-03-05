<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function index(Request $request) : JsonResponse {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $employees = Employee::paginate($limit, ['*'], 'page', $page);

        $data = [
            'status' => 200,
            'message' => 'Employees fetched',
            'data' => $employees->items(),
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'first_page_url' => $employees->url(1),
                'from' => $employees->firstItem(),
                'last_page' => $employees->lastPage(),
                'last_page_url' => $employees->url($employees->lastPage()),
                'next_page_url' => $employees->nextPageUrl(),
                'path' => $employees->path(),
                'per_page' => $employees->perPage(),
                'prev_page_url' => $employees->previousPageUrl(),
                'to' => $employees->lastItem(),
                'total' => $employees->total(),
            ],
        ];

        return response()->json($data);
    }

    public function store(Request $request) : JsonResponse {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string',
            'username' => 'required|string|unique:employees,username',
            'pin' => 'required_without_all:face_recognition,finger_print|numeric',
            'face_recognition' => 'required_without_all:pin,finger_print|string',
            'finger_print' => 'required_without_all:pin,face_recognition|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'full_name' => $request->input('full_name'),
            'username' => $request->input('username'),
            'pin' => $request->input('pin'),
            'face_recognition' => $request->input('face_recognition'),
            'finger_print' => $request->input('finger_print'),
        ];
        $employee = Employee::create($data);
        $employee->is_pin = $request->filled('pin');
        $employee->is_face_recognition = $request->filled('face_recognition');
        $employee->is_finger_print = $request->filled('finger_print');
        $employee->save();

        return response()->json([
            'status' => 201,
            'message' => 'Employee created',
            'data' => $employee,
        ]);
    }

    public function show(string $id) {
        if (!intval($id)) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid ID format. ID must be a numeric.',
            ], 400);
        }

        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json([
                'status' => 404,
                'message' => 'Employee not found',
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Employee fetched',
            'data' => $employee,
        ]);
    }
}
