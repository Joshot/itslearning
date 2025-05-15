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

        // Isi tabel course_materials
        $courseMaterials = [
            // IF540-D: 14 minggu
            ...array_map(function ($week) {
                return [
                    'course_id' => Course::where('course_code', 'IF540-D')->first()->id,
                    'week' => $week,
                    'pdf_path' => "materi/week{$week}.pdf",
                    'is_optional' => false,
                ];
            }, range(1, 14)),
            // IF540-DL: 1 minggu
            [
                'course_id' => Course::where('course_code', 'IF540-DL')->first()->id,
                'week' => 1,
                'pdf_path' => null,
                'is_optional' => false,
            ],
            // IF545-DL: 1 minggu
            [
                'course_id' => Course::where('course_code', 'IF545-DL')->first()->id,
                'week' => 1,
                'pdf_path' => null,
                'is_optional' => false,
            ],
        ];

        foreach ($courseMaterials as $material) {
            CourseMaterial::create($material);
        }
    }
}
