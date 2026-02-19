<?php

namespace App\Models;

use App\Support\CourseShortcodeGenerator;
use App\Support\CourseYearSlugGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'shortcode',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $course): void {
            if ($course->shortcode === null || $course->shortcode === '') {
                $course->shortcode = CourseShortcodeGenerator::generateUnique();
            }
        });

        static::updating(function (self $course): void {
            if ($course->exists) {
                $course->shortcode = (string) $course->getOriginal('shortcode');
            }
        });
    }

    public static function findByShortNameSlug(string $shortNameSlug): ?self
    {
        $targetSlug = CourseYearSlugGenerator::sanitizeShortName($shortNameSlug);

        $courseId = static::query()
            ->get(['id', 'short_name'])
            ->first(function (self $course) use ($targetSlug): bool {
                return CourseYearSlugGenerator::sanitizeShortName((string) $course->short_name) === $targetSlug;
            })
            ?->id;

        if ($courseId === null) {
            return null;
        }

        return static::query()->find($courseId);
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function courseYears(): HasMany
    {
        return $this->hasMany(CourseYear::class);
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
