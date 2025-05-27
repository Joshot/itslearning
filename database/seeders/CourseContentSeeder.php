<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\CourseMaterial;

class CourseContentSeeder extends Seeder
{
    public function run()
    {
        // Isi tabel courses
        $courses = [
            [
                'course_code' => 'IF540-D',
                'course_name' => 'Machine Learning (LEC)',
            ],
            [
                'course_code' => 'IF540-DL',
                'course_name' => 'Machine Learning (LAB)',
            ],
            [
                'course_code' => 'IF545-DL',
                'course_name' => 'Pengenalan Internet (LEC)',
            ],
        ];

        foreach ($courses as $course) {
            Course::updateOrCreate(
                ['course_code' => $course['course_code']],
                ['course_name' => $course['course_name']]
            );
        }

    }
}
