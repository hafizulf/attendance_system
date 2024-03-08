<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('employees')
    ->controller(EmployeeController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });

Route::prefix('attendances')
    ->controller(AttendanceController::class)
    ->group(function () {
        Route::post('/check-in/{employeeId}', 'checkIn');
        Route::patch('/check-out/{employeeId}', 'checkOut');
    });

Route::prefix('report')
    ->controller(ReportController::class)
    ->group(function () {
        Route::get('/date-range', 'dateRangeReport');
        Route::get('/monthly/{monthInEnglish}', 'monthlyReport');
        Route::get('/yearly/{year}', 'yearlyReport');
    });
