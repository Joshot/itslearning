<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('course_code');
            $table->unsignedBigInteger('user_id'); // Bisa student atau lecturer
            $table->enum('role', ['student', 'lecturer']);
            $table->foreign('course_code')->references('course_code')->on('courses')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['course_code', 'user_id', 'role']); // Hindari duplikasi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_assignments');
    }
};
