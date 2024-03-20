<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WeekendSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = Carbon::now()->year;
        $startDate = Carbon::createFromDate($year, 1, 1); // Start from January 1st of the current year
        $endDate = Carbon::createFromDate($year, 12, 31);

        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            if ($currentDate->isWeekend()) {
                Holiday::updateOrCreate([
                    'date' => $currentDate->format('Y-m-d'),
                ], [
                    'name' => $currentDate->format('l'),
                    'date' => $currentDate->format('Y-m-d'),
                ]);
            }

            // Move to the next day
            $currentDate->addDay();
        }
    }
}
