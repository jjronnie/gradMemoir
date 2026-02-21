<?php

namespace App\Http\Requests;

use App\Support\UsernameGenerator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProfileSettingsRequest extends FormRequest
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
            'nickname' => ['nullable', 'string', 'max:80'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()?->id)],
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
            'quote' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^\d+$/', 'max:20'],
            'facebook_username' => ['nullable', 'string', 'max:255'],
            'x_username' => ['nullable', 'string', 'max:255'],
            'tiktok_username' => ['nullable', 'string', 'max:255'],
            'instagram_username' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255', 'regex:/^(?!https?:\/\/)[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}([\/\w\-.~]*)?$/'],
            'email_public' => ['nullable', 'string', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Username may only contain lowercase letters, numbers, and underscores.',
            'phone.regex' => 'Phone number must contain digits only.',
            'website.regex' => 'Website must be a valid domain/path without http:// or https://.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $bio = trim((string) $this->input('bio', ''));

            if ($bio !== '' && str_word_count($bio) > 100) {
                $validator->errors()->add('bio', 'Bio may not exceed 100 words.');
            }

            $quote = trim((string) $this->input('quote', ''));

            if ($quote !== '' && str_word_count($quote) > 8) {
                $validator->errors()->add('quote', 'Quote may not exceed 8 words.');
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

        if ($this->has('website')) {
            $website = trim((string) $this->input('website'));
            $normalized = preg_replace('/^https?:\/\//i', '', $website);
            $normalized = rtrim((string) $normalized, '/');

            $this->merge([
                'website' => $normalized === '' ? null : $normalized,
            ]);
        }

        foreach (['nickname', 'quote', 'profession', 'location'] as $field) {
            if (! $this->has($field)) {
                continue;
            }

            $value = trim((string) $this->input($field));
            $this->merge([
                $field => $value === '' ? null : $value,
            ]);
        }
    }
}
