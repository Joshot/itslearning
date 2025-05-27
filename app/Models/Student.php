<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Student extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'nim', 'name', 'email', 'password', 'major', 'angkatan', 'profile_photo', 'motto'
    ];

    protected $hidden = ['password'];
}
