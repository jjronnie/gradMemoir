<?php

namespace App\Models;

use App\Support\CourseShortcodeGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'university_id',
        'name',
        'short_name',
        'nickname',
        'year',
        'slug',
        'shortcode',
        'admin_id',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $course): void {
            if ($course->slug === null || $course->slug === '') {
                $course->slug = Str::slug("{$course->short_name}-class-of-{$course->year}");
            }

            if ($course->shortcode === null || $course->shortcode === '') {
                $course->shortcode = CourseShortcodeGenerator::generateUnique();
            }
        });

        static::updating(function (self $course): void {
            if ($course->exists) {
                $course->slug = $course->getOriginal('slug');
                $course->shortcode = $course->getOriginal('shortcode');
            }
        });
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function students(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
