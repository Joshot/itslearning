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
                'course_milik' => 'Informatika',
            ],
            [
                'course_code' => 'IF540-DL',
                'course_name' => 'Machine Learning (LAB)',
                'course_milik' => 'Informatika',
            ],
            [
                'course_code' => 'IF545-DL',
                'course_name' => 'Pengenalan Internet (LEC)',
                'course_milik' => 'Informatika',
            ],
            [
                'course_code' => 'SI540-D',
                'course_name' => 'Machine SI (LEC)',
                'course_milik' => 'Sistem Informasi',
            ],
            [
                'course_code' => 'PT540-D',
                'course_name' => 'Dasar Genetik Tanaman',
                'course_milik' => 'Pertanian',
            ],
        ];

        foreach ($courses as $course) {
            Course::updateOrCreate(
                ['course_code' => $course['course_code']],
                [
                    'course_name' => $course['course_name'],
                    'course_milik' => $course['course_milik']
                ]
            );
        }
    }
}
