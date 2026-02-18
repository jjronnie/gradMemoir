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
        Schema::create('course_applications', function (Blueprint $table) {
            $table->id();
            $table->string('applicant_name');
            $table->string('email');
            $table->string('phone');
            $table->string('university_name');
            $table->string('course_name');
            $table->string('year', 4);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'reviewed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_applications');
    }
};
