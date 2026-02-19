<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\CourseYear;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class UgandaUniversitiesAndCoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $creator = User::query()
            ->where('role', UserRole::Superadmin->value)
            ->orderBy('id')
            ->first() ?? User::query()->orderBy('id')->first();

        if ($creator === null) {
            throw new RuntimeException('UgandaUniversitiesAndCoursesSeeder requires at least one user for created_by.');
        }

        DB::transaction(function () use ($creator): void {
            $seedData = $this->seedData();
            $universitiesBySlug = collect();

            foreach ($seedData as $universityData) {
                $slug = Str::slug($universityData['name']);

                $university = University::query()->firstOrCreate(
                    ['name' => $universityData['name']],
                    [
                        'slug' => $slug,
                        'location' => $universityData['location'],
                        'created_by' => $creator->id,
                    ],
                );

                $updates = [];

                if ($university->slug !== $slug) {
                    $updates['slug'] = $slug;
                }

                if ($university->location !== $universityData['location']) {
                    $updates['location'] = $universityData['location'];
                }

                if ((int) $university->created_by !== (int) $creator->id) {
                    $updates['created_by'] = $creator->id;
                }

                if ($updates !== []) {
                    $university->forceFill($updates)->save();
                }

                $universitiesBySlug->put($slug, $university->fresh());
            }

            $usedShortcodes = Course::query()
                ->pluck('shortcode')
                ->mapWithKeys(fn ($shortcode): array => [Str::upper((string) $shortcode) => true])
                ->all();

            $reservedSlugs = CourseYear::query()
                ->get(['course_id', 'year', 'slug'])
                ->mapWithKeys(function (CourseYear $courseYear): array {
                    return [
                        (string) $courseYear->slug => $this->courseYearKey(
                            (int) $courseYear->course_id,
                            (string) $courseYear->year,
                        ),
                    ];
                })
                ->all();

            foreach ($seedData as $universityData) {
                $universitySlug = Str::slug($universityData['name']);
                /** @var University|null $university */
                $university = $universitiesBySlug->get($universitySlug);

                if ($university === null) {
                    continue;
                }

                foreach ($universityData['courses'] as $courseData) {
                    $shortName = Str::upper($courseData['short_name']);
                    $course = Course::query()
                        ->where('university_id', $university->id)
                        ->where('short_name', $shortName)
                        ->first();

                    if ($course === null) {
                        $shortcode = $this->generateDeterministicShortcode(
                            (int) $university->id,
                            $shortName,
                            $usedShortcodes,
                        );

                        $course = Course::query()->create([
                            'university_id' => $university->id,
                            'name' => $courseData['name'],
                            'short_name' => $shortName,
                            'nickname' => null,
                            'shortcode' => $shortcode,
                            'created_by' => $creator->id,
                        ]);
                    } else {
                        $course->forceFill([
                            'name' => $courseData['name'],
                            'nickname' => null,
                            'created_by' => $creator->id,
                        ])->save();
                    }

                    foreach (range(2024, 2026) as $year) {
                        $yearString = (string) $year;
                        $courseYear = CourseYear::query()
                            ->where('course_id', $course->id)
                            ->where('year', $yearString)
                            ->first();

                        if ($courseYear !== null) {
                            $reservedSlugs[(string) $courseYear->slug] = $this->courseYearKey($course->id, $yearString);

                            continue;
                        }

                        $courseYearKey = $this->courseYearKey($course->id, $yearString);
                        $slug = $this->generateUniqueCourseYearSlug(
                            $shortName,
                            $yearString,
                            $universitySlug,
                            $courseYearKey,
                            $reservedSlugs,
                        );

                        CourseYear::query()->create([
                            'course_id' => $course->id,
                            'year' => $yearString,
                            'slug' => $slug,
                            'admin_id' => null,
                        ]);

                        $reservedSlugs[$slug] = $courseYearKey;
                    }
                }
            }
        });
    }

    private function courseProgramKey(int $universityId, string $shortName): string
    {
        return $universityId.'|'.Str::upper($shortName);
    }

    private function courseYearKey(int $courseId, string $year): string
    {
        return $courseId.'|'.$year;
    }

    private function generateDeterministicShortcode(int $universityId, string $shortName, array &$usedShortcodes): string
    {
        $normalizedShortName = Str::upper((string) Str::of($shortName)->replaceMatches('/[^A-Za-z0-9]/', ''));
        $prefix = Str::substr(str_pad($normalizedShortName, 4, 'X'), 0, 4);
        $seed = $universityId.'|'.Str::upper($shortName);
        $attempt = 0;

        do {
            $hash = Str::upper(substr(hash('sha256', $seed.'|'.$attempt), 0, 5));
            $candidate = Str::substr($prefix.$hash, 0, 5);
            $attempt++;
        } while (isset($usedShortcodes[$candidate]));

        $usedShortcodes[$candidate] = true;

        return $candidate;
    }

    private function generateUniqueCourseYearSlug(
        string $shortName,
        string $year,
        string $universitySlug,
        string $courseYearKey,
        array &$reservedSlugs
    ): string {
        $baseSlug = $this->buildCourseYearSlug($shortName, $year);

        if (! isset($reservedSlugs[$baseSlug]) || $reservedSlugs[$baseSlug] === $courseYearKey) {
            return $baseSlug;
        }

        $universityAcronym = $this->universityAcronym($universitySlug);
        $attempt = 0;

        do {
            $suffix = $attempt === 0 ? $universityAcronym : $universityAcronym.$attempt;
            $candidate = $this->buildCourseYearSlug($shortName.'-'.$suffix, $year);
            $attempt++;
        } while (isset($reservedSlugs[$candidate]) && $reservedSlugs[$candidate] !== $courseYearKey);

        return $candidate;
    }

    private function buildCourseYearSlug(string $shortName, string $year): string
    {
        $middle = Str::slug(Str::lower($shortName).'-class-of-'.$year);

        return 'course/'.$middle;
    }

    private function universityAcronym(string $universitySlug): string
    {
        $ignoredWords = ['of', 'and', 'the'];
        $parts = explode('-', Str::lower($universitySlug));
        $acronym = '';

        foreach ($parts as $part) {
            if ($part === '' || in_array($part, $ignoredWords, true)) {
                continue;
            }

            $acronym .= Str::substr($part, 0, 1);
        }

        return $acronym !== '' ? $acronym : 'uni';
    }

    /**
     * @return array<int, array{name: string, location: string, courses: array<int, array{name: string, short_name: string}>}>
     */
    private function seedData(): array
    {
        return [
            [
                'name' => 'Makerere University',
                'location' => 'Kampala',
                'courses' => [
                    ['name' => 'Bachelor of Science in Computer Science', 'short_name' => 'CS'],
                    ['name' => 'Bachelor of Software Engineering', 'short_name' => 'SE'],
                    ['name' => 'Bachelor of Information Systems and Technology', 'short_name' => 'IST'],
                    ['name' => 'Bachelor of Library and Information Science', 'short_name' => 'LIS'],
                    ['name' => 'Bachelor of Records and Archives Management', 'short_name' => 'RAM'],
                    ['name' => 'Bachelor of Business Administration', 'short_name' => 'BBA'],
                ],
            ],
            [
                'name' => 'Makerere University Business School',
                'location' => 'Kampala',
                'courses' => [
                    ['name' => 'Bachelor of Business Administration', 'short_name' => 'BBA'],
                    ['name' => 'Bachelor of International Business', 'short_name' => 'BIB'],
                    ['name' => 'Bachelor of Procurement and Supply Chain Management', 'short_name' => 'BPSM'],
                    ['name' => 'Bachelor of Entrepreneurship and Small Business Management', 'short_name' => 'BESBM'],
                    ['name' => 'Bachelor of Science in Accounting and Finance', 'short_name' => 'BSAF'],
                    ['name' => 'Bachelor of Human Resource Management', 'short_name' => 'BHRM'],
                    ['name' => 'Bachelor of Business Computing', 'short_name' => 'BBC'],
                    ['name' => 'Bachelor of Office and Information Management', 'short_name' => 'BOIM'],

                ],
            ],
            [
                'name' => 'Kyambogo University',
                'location' => 'Kampala',
                'courses' => [
                    ['name' => 'Bachelor of Electrical Engineering', 'short_name' => 'BEE'],
                    ['name' => 'Bachelor of Science in Accounting and Finance', 'short_name' => 'BSAF'],
                    ['name' => 'Bachelor of Business Studies', 'short_name' => 'BBS'],
                    ['name' => 'Bachelor of Procurement and Logistics Management', 'short_name' => 'BPLM'],
                    ['name' => 'Bachelor of Social Work', 'short_name' => 'BSW'],
                    ['name' => 'Bachelor of Information Technology', 'short_name' => 'BIT'],
                ],
            ],
        ];
    }
}
