<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['course_code', 'course_name', 'course_milik'];

    public function materials()
    {
        return $this->hasMany(CourseMaterial::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'course_code', 'course_code');
    }

    public function assignments()
    {
        return $this->hasMany(CourseAssignment::class, 'course_code', 'course_code');
    }
}
