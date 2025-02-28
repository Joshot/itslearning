<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('questions', function (Blueprint $table) {
            $table->id(); // Secara default ini sudah unsignedBigInteger
            $table->string('question_text'); // Kolom untuk teks soal
            $table->string('option_a'); // Kolom untuk pilihan A
            $table->string('option_b'); // Kolom untuk pilihan B
            $table->string('option_c'); // Kolom untuk pilihan C
            $table->string('option_d'); // Kolom untuk pilihan D
            $table->string('correct_option'); // Kolom untuk jawaban yang benar (A, B, C, D)
            $table->string('quiz_id');
            $table->enum('difficulty', ['easy', 'medium', 'hard']); // Kolom untuk tingkat kesulitan
            $table->timestamps(); // Kolom untuk created_at dan updated_at
        });
    }

    public function down() {
        Schema::dropIfExists('questions');
    }
};
