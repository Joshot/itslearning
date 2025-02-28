<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'quiz_number', 'total_questions'];

    public function questions()
    {
        return $this->hasMany(Question::class, 'quiz_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
