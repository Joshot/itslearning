<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;

class QuizSeeder extends Seeder
{
    public function run()
    {
        Quiz::create([
            'course_code' => 'IF540-D',
            'title' => '1',
            'start_time' => now(),
            'end_time' => now()->addDays(7)->addHours(2),
        ]);

        Quiz::create([
            'course_code' => 'IF540-D',
            'title' => '2',
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(7)->addHours(2),
        ]);

        Quiz::create([
            'course_code' => 'IF540-D',
            'title' => '3',
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(7)->addHours(2),
        ]);

        Quiz::create([
            'course_code' => 'IF540-D',
            'title' => '4',
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(7)->addHours(2),
        ]);
    }
}
