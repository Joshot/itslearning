<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Lecturer extends Authenticatable
{
    use Notifiable;
    use HasFactory;

    protected $fillable = [
        'nidn', 'name', 'email', 'password', 'major', 'mata_kuliah', 'profile_photo'
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];
}
