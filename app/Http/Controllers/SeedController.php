<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SeedController extends Controller
{
    public function seedHolidayData(Request $request)
    {
        try {
            Artisan::call('db:seed', [
                '--class' => 'HolidaySeeder',
            ]);
            return response()->json(['message' => 'Holiday seeder executed successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
