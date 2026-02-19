<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class StorePostRequest extends FormRequest
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
            'body' => ['nullable', 'string', 'max:5000'],
            'photos' => ['nullable', 'array', 'max:4'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif,avif', 'max:20480'],
        ];
    }

    public function messages(): array
    {
        return [
            'photos.max' => 'You can upload up to 4 photos per post.',
            'photos.*.max' => 'Each photo must be 20MB or smaller.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $body = trim((string) $this->input('body', ''));
            $hasPhotos = $this->hasFile('photos');
            $requestedPhotoCount = is_array($this->file('photos')) ? count($this->file('photos')) : 0;
            $currentPhotoCount = Media::query()
                ->where('model_type', Post::class)
                ->whereIn('model_id', $this->user()->posts()->select('id'))
                ->count();
            $maxPhotos = 8;

            if ($body === '' && ! $hasPhotos) {
                $validator->errors()->add('body', 'Post body or at least one photo is required.');
            }

            if ($currentPhotoCount >= $maxPhotos) {
                $validator->errors()->add('photos', 'You have reached the maximum of 8 photos. Delete a photo before adding a new one.');
            }

            if ($currentPhotoCount + $requestedPhotoCount > $maxPhotos) {
                $remaining = max($maxPhotos - $currentPhotoCount, 0);
                $validator->errors()->add('photos', "You can add only {$remaining} more photo(s).");
            }
        });
    }
}
