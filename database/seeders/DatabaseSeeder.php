<?php

namespace Database\Seeders;

use App\Models\Lecturer;
use App\Models\Student;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Student::factory()->create([
            'name' => 'Joshua Hotama (00000056899)',
            'email' => 'joshua.hotama@student.umn.ac.id',
            'password' => bcrypt('123'),
        ]);

        Lecturer::factory()->create([
            'name' => 'Hamzah Unto (00000001526)',
            'email' => 'hamzahunto@lecturer.umn.ac.id',
            'password' => bcrypt('123'),
        ]);
    }
}
