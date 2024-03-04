<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
