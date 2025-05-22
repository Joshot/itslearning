<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseAssignment extends Model
{
    protected $fillable = ['course_code', 'user_id', 'role'];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_code', 'course_code');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
