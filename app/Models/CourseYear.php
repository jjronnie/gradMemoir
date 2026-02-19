<?php

namespace App\Models;

use App\Support\CourseYearSlugGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CourseYear extends Model
{
    /** @use HasFactory<\Database\Factories\CourseYearFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'year',
        'slug',
        'admin_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $courseYear): void {
            if ($courseYear->slug === null || $courseYear->slug === '') {
                $shortName = (string) $courseYear->course()->value('short_name');
                $courseYear->slug = CourseYearSlugGenerator::fromShortNameAndYear(
                    $shortName,
                    (string) $courseYear->year,
                );
            }
        });

        static::updating(function (self $courseYear): void {
            if ($courseYear->exists) {
                $courseYear->slug = (string) $courseYear->getOriginal('slug');
            }
        });
    }

    public static function findOrCreateForCourse(Course $course, string $year): self
    {
        return DB::transaction(function () use ($course, $year): self {
            $existing = self::query()
                ->where('course_id', $course->id)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            try {
                return self::query()->create([
                    'course_id' => $course->id,
                    'year' => $year,
                ]);
            } catch (QueryException $exception) {
                $uniqueConstraintViolation = (string) $exception->getCode() === '23000';

                if (! $uniqueConstraintViolation) {
                    throw $exception;
                }

                return self::query()
                    ->where('course_id', $course->id)
                    ->where('year', $year)
                    ->firstOrFail();
            }
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'course_year_id');
    }
}
