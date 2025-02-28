<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttempt extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'quiz_id', 'score'];

    /**
     * Relasi dengan model Student
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relasi dengan model Quiz
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
