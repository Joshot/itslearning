<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('feedback', function (Blueprint $table) {
            if (!Schema::hasColumn('feedback', 'course_id')) {
                $table->unsignedBigInteger('course_id')->after('student_id');
                $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            }
            if (!Schema::hasColumn('feedback', 'failed_tasks')) {
                $table->json('failed_tasks')->nullable();
            }
            if (!Schema::hasColumn('feedback', 'question_distribution')) {
                $table->json('question_distribution')->nullable();
            }
            if (!Schema::hasColumn('feedback', 'question_weights')) {
                $table->json('question_weights')->nullable();
            }
            if (!Schema::hasColumn('feedback', 'additional_quiz_id')) {
                $table->unsignedBigInteger('additional_quiz_id')->nullable();
                $table->foreign('additional_quiz_id')->references('id')->on('quizzes')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropForeign(['additional_quiz_id']);
            $table->dropColumn(['course_id', 'failed_tasks', 'question_distribution', 'question_weights', 'additional_quiz_id']);
        });
    }
};
