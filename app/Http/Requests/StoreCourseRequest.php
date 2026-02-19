<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
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
            'university_id' => ['required', 'integer', 'exists:universities,id'],
            'name' => ['required', 'string', 'max:255'],
            'short_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('courses', 'short_name')
                    ->where(fn ($query) => $query->where('university_id', (int) $this->input('university_id'))),
            ],
            'nickname' => ['nullable', 'string', 'max:80'],
        ];
    }
}
