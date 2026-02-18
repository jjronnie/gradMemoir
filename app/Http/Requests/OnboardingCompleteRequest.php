<?php

namespace App\Http\Requests;

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
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('users', 'username')->ignore($this->user()?->id),
            ],
            'bio' => ['nullable', 'string'],
            'profession' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
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
            $bio = trim((string) $this->input('bio', ''));

            if ($bio !== '' && str_word_count($bio) > 100) {
                $validator->errors()->add('bio', 'Bio may not exceed 100 words.');
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
