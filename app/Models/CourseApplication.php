<?php

namespace App\Models;

use App\Enums\CourseApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseApplication extends Model
{
    /** @use HasFactory<\Database\Factories\CourseApplicationFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'applicant_name',
        'email',
        'phone',
        'university_name',
        'course_name',
        'year',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CourseApplicationStatus::class,
        ];
    }
}
