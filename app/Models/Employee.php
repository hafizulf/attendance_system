<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
    * fillable
    *
    * @var array
  */
    protected $fillable = [
        'full_name',
        'username',
        'image',
        'is_pin',
        'is_face_recognition',
        'is_finger_print',
        'pin',
        'face_recognition',
        'finger_print',
    ];

    protected function casts() : array {
        return [
            'created_at' => 'datetime:Y-m-d',
            'updated_at' => 'datetime:Y-m-d',
        ];
    }

    public function attendance() : HasMany {
        return $this->hasMany(Attendance::class);
    }
}
