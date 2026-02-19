<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class University extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UniversityFactory> */
    use HasFactory, InteractsWithMedia;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'location',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $university): void {
            if ($university->slug === null || $university->slug === '') {
                $university->slug = Str::slug($university->name);
            }
        });
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function courseYears(): HasManyThrough
    {
        return $this->hasManyThrough(
            CourseYear::class,
            Course::class,
            'university_id',
            'course_id',
            'id',
            'id',
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif',
                'image/avif',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->format('webp')
            ->quality(80)
            ->nonQueued();

        $this->addMediaConversion('full')
            ->width(1200)
            ->format('webp')
            ->quality(80)
            ->nonQueued();
    }
}
