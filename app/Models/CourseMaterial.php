<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseMaterial extends Model
{
    protected $fillable = ['course_id', 'week', 'files', 'video_url', 'is_optional'];

    protected $casts = [
        'files' => 'array', // Cast JSON to array
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
