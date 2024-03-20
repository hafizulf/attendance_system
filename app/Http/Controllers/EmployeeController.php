<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    public function index(Request $request) : JsonResponse {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('q');
        $query = Employee::query();
        if ($searchQuery) {
            $query->where(function (Builder $builder) use ($searchQuery) {
                $builder
                    ->where('full_name', 'like', "%$searchQuery%")
                    ->orWhere('username', 'like', "%$searchQuery%");
            });
        }
        $employees =  $query->paginate($limit, ['*'], 'page', $page);
        $message = $employees->isEmpty() ? 'No matching records found' : 'Employees fetched';
        $data = [
            'status' => 200,
            'message' => $message,
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
            'username' => [
                'required',
                'string',
                Rule::unique('employees')->whereNull('deleted_at'),
            ],
            'image' => 'image|mimes:jpeg,png,jpg|max:2048',
            'pin' => 'required_without_all:face_recognition,finger_print|numeric|min:6',
            'face_recognition' => 'required_without_all:pin,finger_print|string',
            'finger_print' => 'required_without_all:pin,face_recognition|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $path = "";
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/images');
            $path = str_replace('public/', 'storage/', $path);
        }

        $data = [
            'full_name' => $request->input('full_name'),
            'username' => $request->input('username'),
            'image' => $path,
            'pin' => $request->input('pin'),
            'face_recognition' => $request->input('face_recognition'),
            'finger_print' => $request->input('finger_print'),
            'is_pin' => $request->filled('pin'),
            'is_face_recognition' => $request->filled('face_recognition'),
            'is_finger_print' => $request->filled('finger_print'),
        ];
        $employee = Employee::create($data);

        return response()->json([
            'status' => 201,
            'message' => 'Employee created',
            'data' => $employee,
        ]);
    }

    public function show(string $id) : JsonResponse {
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

        return response()->json([
            'status' => 200,
            'message' => 'Employee fetched',
            'data' => $employee,
        ]);
    }

    public function update(Request $request, string $id) : JsonResponse {
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
            'full_name' => 'string',
            'username' => 'string',
            'pin' => 'numeric|min:6',
            'face_recognition' => 'string',
            'finger_print' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $fillableFields = $request->only(['full_name', 'username', 'pin', 'face_recognition', 'finger_print']);
        $employee->fill($fillableFields);
        $employee->is_pin = $request->filled('pin') ? true : $employee->is_pin;
        $employee->is_face_recognition = $request->filled('face_recognition') ? true : $employee->is_face_recognition;
        $employee->is_finger_print = $request->filled('finger_print') ? true : $employee->is_finger_print;
        $employee->save();

        return response()->json([
            'status' => 200,
            'message' => 'Employee updated',
            'data' => $employee,
        ]);
    }

    public function destroy(string $id) : JsonResponse {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|numeric|exists:employees,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $employee = Employee::findOrFail($id);

            if ($employee->image) {
                $imagePath = public_path('images/' . $employee->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $employee->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Employee deleted successfully',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
                'errors' => $validator->errors(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while processing your request.',
            ], 500);
        }
    }
}
