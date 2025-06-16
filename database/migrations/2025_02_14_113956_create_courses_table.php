<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_code')->unique();
            $table->string('course_name');
            $table->enum('course_milik', [
                'Informatika',
                'Pertanian',
                'Sistem Informasi',
                'Teknik Komputer',
                'Biologi',
                'Kedokteran',
                'Ilmu Komunikasi',
                'Manajemen',
                'Film',
                'DKV'
            ]);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
};
