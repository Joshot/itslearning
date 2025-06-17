<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class Lecturer extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'nidn', 'name', 'email', 'password', 'major', 'mata_kuliah', 'profile_photo', 'motto'
    ];

    protected $hidden = ['password'];

    protected $guard = 'lecturer';


    public function setPasswordAttribute($value)
    {
        if (!Hash::needsRehash($value)) {
            $this->attributes['password'] = $value;
        } else {
            $this->attributes['password'] = Hash::make($value);
        }
    }
}
