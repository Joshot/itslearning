<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('course_code');
            $table->integer('week');
            $table->string('title');
            $table->text('description');
            $table->dateTime('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('assignments');
    }
};
