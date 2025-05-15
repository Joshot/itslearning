<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('course_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade'); // FK ke tabel courses
            $table->integer('week');
            $table->string('pdf_path')->nullable();
            $table->string('video_url')->nullable();
            $table->boolean('is_optional')->default(false);
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('course_materials');
    }
};
