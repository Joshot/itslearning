<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->float('average_score');
            $table->text('feedback_text');
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('feedback');
    }
};
