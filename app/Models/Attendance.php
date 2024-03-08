<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
    * fillable
    *
    * @var array
    */
    protected $fillable = [
        'employee_id',
        'date',
        'time_in',
        'time_out',
    ];

    public function employee(): BelongsTo {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
