<?php

namespace App\Exports;

use App\Excel\ValueBinder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class AttendancesExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Username',
            'Late Count',
            'Early Leave Count',
            'Work Percentage',
        ];
    }
}
