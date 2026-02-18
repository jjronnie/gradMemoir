<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        $array['conversions'] = [
            'thumb' => $this->hasGeneratedConversion('thumb') ? $this->getUrl('thumb') : null,
            'full' => $this->hasGeneratedConversion('full') ? $this->getUrl('full') : null,
        ];

        if (! $this->hasOriginalFile()) {
            $array['original_url'] = $array['conversions']['full'] ?? $array['conversions']['thumb'] ?? null;
        }

        return $array;
    }

    protected function hasOriginalFile(): bool
    {
        return Storage::disk($this->disk)->exists($this->getPathRelativeToRoot());
    }
}
