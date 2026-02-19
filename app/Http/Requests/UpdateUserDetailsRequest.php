<?php

namespace App\Http\Requests;

use App\Models\Course;
use App\Models\CourseYear;
use App\Support\UsernameGenerator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserDetailsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user')?->id)],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('users', 'username')->ignore($this->route('user')?->id),
            ],
            'website' => ['nullable', 'string', 'max:255', 'regex:/^(?!https?:\/\/)[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}([\/\w\-.~]*)?$/'],
            'is_verified' => ['required', 'boolean'],
            'email_verified_at' => ['nullable', 'date'],
            'university_id' => ['nullable', 'integer', 'exists:universities,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'course_year_id' => ['nullable', 'integer', 'exists:course_years,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Username may only contain lowercase letters, numbers, and underscores.',
            'website.regex' => 'Website must be a valid domain/path without http:// or https://.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('username')) {
            $this->merge([
                'username' => UsernameGenerator::normalize(ltrim((string) $this->input('username'), '@')),
            ]);
        }

        if ($this->has('website')) {
            $website = trim((string) $this->input('website'));
            $normalized = preg_replace('/^https?:\/\//i', '', $website);
            $normalized = rtrim((string) $normalized, '/');

            $this->merge([
                'website' => $normalized === '' ? null : $normalized,
            ]);
        }

        if ($this->has('university_id')) {
            $universityId = trim((string) $this->input('university_id'));
            $this->merge([
                'university_id' => $universityId === '' ? null : (int) $universityId,
            ]);
        }

        if ($this->has('course_id')) {
            $courseId = trim((string) $this->input('course_id'));
            $this->merge([
                'course_id' => $courseId === '' ? null : (int) $courseId,
            ]);
        }

        if ($this->has('course_year_id')) {
            $courseYearId = trim((string) $this->input('course_year_id'));
            $this->merge([
                'course_year_id' => $courseYearId === '' ? null : (int) $courseYearId,
            ]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $universityId = $this->input('university_id');
            $courseId = $this->input('course_id');
            $courseYearId = $this->input('course_year_id');

            if ($courseId !== null && $courseId !== '' && $universityId !== null && $universityId !== '') {
                $courseBelongsToUniversity = Course::query()
                    ->whereKey($courseId)
                    ->where('university_id', $universityId)
                    ->exists();

                if (! $courseBelongsToUniversity) {
                    $validator->errors()->add('course_id', 'Selected course does not belong to the selected university.');
                }
            }

            if ($courseYearId === null || $courseYearId === '') {
                if ($courseId !== null && $courseId !== '') {
                    $validator->errors()->add('course_year_id', 'Please select a cohort for the selected course.');
                }

                return;
            }

            $courseYear = CourseYear::query()
                ->with('course:id,university_id')
                ->find($courseYearId);

            if ($courseYear === null) {
                return;
            }

            if ($courseId !== null && $courseId !== '' && (int) $courseId !== (int) $courseYear->course_id) {
                $validator->errors()->add('course_year_id', 'Selected cohort does not belong to the selected course.');
            }

            if (
                $universityId !== null
                && $universityId !== ''
                && (int) $universityId !== (int) $courseYear->course?->university_id
            ) {
                $validator->errors()->add('course_year_id', 'Selected cohort does not belong to the selected university.');
            }
        });
    }
}
