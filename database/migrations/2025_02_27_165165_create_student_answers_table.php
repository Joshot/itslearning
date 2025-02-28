<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attempt_id');
            $table->unsignedBigInteger('question_id');
            $table->char('selected_option', 1);
            $table->boolean('is_correct');
            $table->timestamps();

            // Foreign keys
            $table->foreign('attempt_id')->references('id')->on('student_attempts')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });

    }
    public function down() {
        Schema::dropIfExists('student_answers');
    }
};
