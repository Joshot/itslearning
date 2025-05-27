<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up() {
        Schema::create('lecturers', function (Blueprint $table) {
            $table->id();
            $table->string('nidn')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('major');
            $table->string('mata_kuliah');
            $table->string('profile_photo')->default('/images/profile.jpg');
            $table->string('motto')->default('Veni, Vidi, Vici');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('lecturers');
    }
};
