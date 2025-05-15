<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseMaterial extends Model
{
    protected $fillable = ['course_id', 'week', 'pdf_path', 'video_url', 'is_optional'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
