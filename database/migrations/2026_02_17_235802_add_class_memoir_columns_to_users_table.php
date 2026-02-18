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
        Schema::table('users', function (Blueprint $table) {
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
            $table->string('email_public')->nullable();
            $table->boolean('onboarding_completed')->default(false);
            $table->foreignId('university_id')->nullable()->index();
            $table->foreignId('course_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google_id',
                'username',
                'username_updated_at',
                'role',
                'status',
                'bio',
                'profession',
                'location',
                'phone',
                'facebook_username',
                'x_username',
                'tiktok_username',
                'instagram_username',
                'email_public',
                'onboarding_completed',
                'university_id',
                'course_id',
            ]);
        });
    }
};
