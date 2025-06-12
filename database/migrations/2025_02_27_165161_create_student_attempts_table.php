<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('student_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->unsignedTinyInteger('task_number');
            $table->integer('score');
            $table->integer('errors_easy')->default(0); // Kolom baru untuk errors easy
            $table->integer('errors_medium')->default(0); // Kolom baru untuk errors medium
            $table->integer('errors_hard')->default(0); // Kolom baru untuk errors hard
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('student_attempts');
    }
};
