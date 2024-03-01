<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class employee extends Model
{
    use HasFactory;

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
