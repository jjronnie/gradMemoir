<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseYearRequest extends FormRequest
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
        $courseId = (int) $this->route('course')?->id;

        return [
            'year' => [
                'required',
                'digits:4',
                'integer',
                'between:1900,2100',
                Rule::unique('course_years', 'year')
                    ->where(fn ($query) => $query->where('course_id', $courseId)),
            ],
            'admin_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->where('role', 'admin'),
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'year.digits' => 'Year must be exactly 4 digits.',
            'year.between' => 'Year must be between 1900 and 2100.',
            'year.unique' => 'This cohort year already exists for the selected program.',
        ];
    }
}
