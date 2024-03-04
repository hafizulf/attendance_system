<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'is_pin',
        'is_face_recognition',
        'is_finger_print',
        'pin',
        'face_recognition',
        'finger_print',
    ];
}
