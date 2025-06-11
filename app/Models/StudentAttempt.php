<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttempt extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'quiz_id', 'course_id', 'task_number', 'score', 'errors'];

    protected $casts = [
        'errors' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
