<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'average_score',
        'feedback_text',
        'failed_tasks',
        'question_distribution',
        'question_weights',
        'additional_quiz_id'
    ];

    protected $casts = [
        'failed_tasks' => 'array',
        'question_distribution' => 'array',
        'question_weights' => 'array'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function additionalQuiz()
    {
        return $this->belongsTo(Quiz::class, 'additional_quiz_id');
    }
}
