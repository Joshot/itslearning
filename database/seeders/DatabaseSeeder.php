<?php

namespace Database\Seeders;

use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('123'),
            'is_admin' => true,
        ]);

        // Sample Student
        Student::factory()->create([
            'name' => 'Joshua Hotama (00000056899)',
            'email' => 'joshua.hotama@student.umn.ac.id',
            'password' => bcrypt('123'),
        ]);

        // Sample Lecturer
        Lecturer::factory()->create([
            'name' => 'Hamzah Unto (00000001526)',
            'email' => 'hamzahunto@lecturer.umn.ac.id',
            'password' => bcrypt('123'),
        ]);
    }
}
