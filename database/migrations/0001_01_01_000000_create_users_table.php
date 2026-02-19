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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->string('google_id')->nullable()->unique();
            $table->string('username')->unique();
            $table->timestamp('username_updated_at')->nullable();
            $table->enum('role', ['student', 'admin', 'superadmin'])->default('student');
            $table->enum('status', ['active', 'banned', 'suspended'])->default('active');
            $table->text('bio')->nullable();
            $table->string('profession')->nullable();
            $table->string('location')->nullable();
            $table->string('phone')->nullable();
            $table->string('facebook_username')->nullable();
            $table->string('x_username')->nullable();
            $table->string('tiktok_username')->nullable();
            $table->string('instagram_username')->nullable();
            $table->string('website')->nullable();
            $table->string('email_public')->nullable();
            $table->boolean('onboarding_completed')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->foreignId('university_id')->nullable()->index();
            $table->foreignId('course_id')->nullable()->index();
            $table->foreignId('course_year_id')->nullable()->index();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
