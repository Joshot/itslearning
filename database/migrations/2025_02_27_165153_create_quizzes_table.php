<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('course_code'); // Tambahkan kolom course_code
            $table->string('title'); // Nama kuis
            $table->dateTime('start_time')->nullable(); // Waktu mulai kuis
            $table->dateTime('end_time')->nullable(); // Waktu berakhir kuis
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
