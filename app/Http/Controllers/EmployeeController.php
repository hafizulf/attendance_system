<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index() : JsonResponse {
        $data = [
            'message' => 'pong'
        ];

        return response()->json($data);
    }
}
