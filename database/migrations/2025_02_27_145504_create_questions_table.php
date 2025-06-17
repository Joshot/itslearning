<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('question_text');
            $table->string('option_a');
            $table->string('option_b');
            $table->string('option_c');
            $table->string('option_d');
            $table->string('correct_option');
            $table->unsignedTinyInteger('task_number')->nullable(); // 1, 2, 3, or 4
            $table->unsignedBigInteger('course_id'); // Foreign key to courses
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->string('image')->nullable(); // Add nullable image column
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('questions');
    }
};
