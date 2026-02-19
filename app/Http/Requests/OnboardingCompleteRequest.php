<?php

namespace App\Http\Requests;

use App\Models\CourseYear;
use App\Support\UsernameGenerator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class OnboardingCompleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'university_id' => ['required', 'integer', 'exists:universities,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'course_year_id' => ['required', 'integer', 'exists:course_years,id'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('users', 'username')->ignore($this->user()?->id),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,avif', 'max:20480'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Username may only contain lowercase letters, numbers, and underscores.',
            'avatar.max' => 'Profile photo must be 20MB or smaller.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $courseId = $this->integer('course_id');
            $courseYearId = $this->integer('course_year_id');

            if ($courseId === 0 || $courseYearId === 0) {
                return;
            }

            $belongsToCourse = CourseYear::query()
                ->whereKey($courseYearId)
                ->where('course_id', $courseId)
                ->exists();

            if (! $belongsToCourse) {
                $validator->errors()->add('course_year_id', 'Selected cohort does not belong to the selected program.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('username')) {
            $this->merge([
                'username' => UsernameGenerator::normalize(ltrim((string) $this->input('username'), '@')),
            ]);
        }
    }
}
