<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeaturedImageStoreRequest extends FormRequest
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
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif,avif', 'max:20480'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.required' => 'Please upload at least one image.',
            'images.array' => 'Uploaded images are invalid.',
            'images.min' => 'Please upload at least one image.',
            'images.max' => 'You can upload up to 5 images at once.',
            'images.*.image' => 'Each file must be a valid image.',
            'images.*.max' => 'Each image must be 20MB or smaller.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->hasFile('images')) {
            return;
        }

        if ($this->hasFile('image')) {
            $this->files->set('images', [$this->file('image')]);

            return;
        }

        if ($this->hasFile('photo')) {
            $this->files->set('images', [$this->file('photo')]);

            return;
        }

        if ($this->hasFile('photos')) {
            $this->files->set('images', $this->file('photos'));
        }
    }
}
