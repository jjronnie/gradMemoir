<?php

namespace App\Http\Requests;

use App\Support\UsernameGenerator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
    }
}
