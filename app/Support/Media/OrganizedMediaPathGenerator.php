<?php

namespace App\Support\Media;

use App\Models\Post;
use App\Models\University;
use App\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class OrganizedMediaPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->buildBasePath($media).'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->buildBasePath($media).'/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->buildBasePath($media).'/responsive-images/';
    }

    private function buildBasePath(Media $media): string
    {
        $mediaUuid = $this->sanitizeSegment($media->uuid ?: 'media-file');
        $model = $media->model;
        $ownerUsername = $this->usernameSegment($media->getCustomProperty('owner_username'));
        $universitySlug = $this->sanitizeSegment($media->getCustomProperty('university_slug'));

        if ($media->model_type === User::class) {
            $username = $ownerUsername !== 'unknown'
                ? $ownerUsername
                : $this->usernameSegment(
                    User::query()->whereKey($media->model_id)->value('username')
                    ?? ($model instanceof User ? $model->username : null)
                );

            return "students/{$username}/profile-photos/{$mediaUuid}";
        }

        if ($media->model_type === Post::class) {
            $postUserId = Post::query()->whereKey($media->model_id)->value('user_id');
            $username = $ownerUsername !== 'unknown'
                ? $ownerUsername
                : $this->usernameSegment(
                    $postUserId === null
                        ? null
                        : (User::query()->whereKey($postUserId)->value('username')
                            ?? ($model instanceof Post ? $model->user?->username : null))
                );

            return "students/{$username}/post-photos/{$mediaUuid}";
        }

        if ($media->model_type === University::class) {
            $slug = $universitySlug !== 'unknown'
                ? $universitySlug
                : $this->sanitizeSegment(
                    University::query()->whereKey($media->model_id)->value('slug')
                    ?? ($model instanceof University ? $model->slug : null)
                );

            return "universities/{$slug}/logos/{$mediaUuid}";
        }

        $modelName = $this->sanitizeSegment(class_basename((string) $media->model_type));
        $collection = $this->sanitizeSegment($media->collection_name);

        return "uploads/{$modelName}/{$collection}/{$mediaUuid}";
    }

    private function usernameSegment(?string $username): string
    {
        return $this->sanitizeSegment(ltrim((string) $username, '@'));
    }

    private function sanitizeSegment(?string $value): string
    {
        $normalized = strtolower(trim((string) $value));
        $normalized = preg_replace('/[^a-z0-9._-]+/', '-', $normalized) ?? '';
        $normalized = trim($normalized, '-_.');

        return $normalized !== '' ? $normalized : 'unknown';
    }
}
