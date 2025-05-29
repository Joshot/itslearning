<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = ['course_code', 'task_number', 'title', 'start_time', 'end_time'];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_code', 'course_code');
    }
}
