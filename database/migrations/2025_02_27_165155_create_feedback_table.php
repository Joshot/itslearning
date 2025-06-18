<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('course_id');
            $table->float('average_score')->nullable();
            $table->text('feedback_text')->nullable();
            $table->json('failed_tasks')->nullable();
            $table->json('question_distribution')->nullable();
            $table->json('question_weights')->nullable();
            $table->json('task_distribution')->nullable();
            $table->unsignedBigInteger('additional_quiz_id')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('additional_quiz_id')->references('id')->on('quizzes')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedback');
    }
};
