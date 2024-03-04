<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('username')->unique();
            $table->boolean('is_pin')->default(false);
            $table->boolean('is_face_recognition')->default(false);
            $table->boolean('is_finger_print')->default(false);
            $table->unsignedInteger('pin')->nullable();
            $table->string('face_recognition')->nullable();
            $table->string('finger_print')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
