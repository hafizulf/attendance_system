<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Http;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $urlApiIndonesiaHoliday = "https://api-harilibur.vercel.app/api";
        $response = Http::get($urlApiIndonesiaHoliday);
        $data = $response->json();

        foreach ($data as $item) {
            // Find or create holiday by date
            Holiday::firstOrCreate(
                ['date' => $item['holiday_date']],
                ['name' => $item['holiday_name']]
            );
        }
    }
}
