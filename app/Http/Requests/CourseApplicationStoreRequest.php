<?php

namespace App\Http\Requests;

use App\Support\Honeypot;
use Illuminate\Foundation\Http\FormRequest;

class CourseApplicationStoreRequest extends FormRequest
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
            'applicant_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\d+$/', 'min:7', 'max:20'],
            'university_name' => ['required', 'string', 'max:255'],
            'course_name' => ['required', 'string', 'max:255'],
            'year' => ['required', 'digits:4'],
            'notes' => ['nullable', 'string', 'max:2000'],
            ...Honeypot::rules(),
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'WhatsApp phone number must contain digits only.',
            'year.digits' => 'Year must be exactly 4 digits.',
            ...Honeypot::messages(),
        ];
    }
}
