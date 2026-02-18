<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

type Photo =
    | string
    | {
          url?: string;
          full?: string;
          thumb?: string;
          description?: string | null;
          postedDiff?: string | null;
          postedAt?: string | null;
          downloadName?: string | null;
      };

type NormalizedPhoto = {
    url: string;
    description: string | null;
    postedDiff: string | null;
    postedAt: string | null;
    downloadName: string | null;
};

const props = withDefaults(
    defineProps<{
        modelValue: boolean;
        photos: Photo[];
        initialIndex?: number;
        title?: string;
        description?: string | null;
    }>(),
    {
        initialIndex: 0,
        title: 'Photo',
        description: null,
    },
);

const emit = defineEmits<{
    (event: 'update:modelValue', value: boolean): void;
}>();

const currentIndex = ref(props.initialIndex);
const touchStartX = ref<number | null>(null);

watch(
    () => props.initialIndex,
    (value) => {
        currentIndex.value = value;
    },
);

const normalizedPhotos = computed<NormalizedPhoto[]>(() =>
    props.photos.map((photo) => {
        if (typeof photo === 'string') {
            return {
                url: photo,
                description: props.description ?? null,
                postedDiff: null,
                postedAt: null,
                downloadName: null,
            };
        }

        return {
            url: photo.full ?? photo.url ?? photo.thumb ?? '',
            description: photo.description ?? props.description ?? null,
            postedDiff: photo.postedDiff ?? null,
            postedAt: photo.postedAt ?? null,
            downloadName: photo.downloadName ?? null,
        };
    }),
);

const currentPhoto = computed<NormalizedPhoto | null>(
    () => normalizedPhotos.value[currentIndex.value] ?? null,
);

const canNavigate = computed(() => normalizedPhotos.value.length > 1);

const previous = (): void => {
    if (!canNavigate.value) {
        return;
    }

    currentIndex.value =
        (currentIndex.value - 1 + normalizedPhotos.value.length) %
        normalizedPhotos.value.length;
};

const next = (): void => {
    if (!canNavigate.value) {
        return;
    }

    currentIndex.value =
        (currentIndex.value + 1) % normalizedPhotos.value.length;
};

const onKeyDown = (event: KeyboardEvent): void => {
    if (!props.modelValue || !canNavigate.value) {
        return;
    }

    if (event.key === 'ArrowLeft') {
        previous();
    }

    if (event.key === 'ArrowRight') {
        next();
    }
};

const onTouchStart = (event: TouchEvent): void => {
    if (!canNavigate.value) {
        return;
    }

    touchStartX.value = event.touches[0]?.clientX ?? null;
};

const onTouchEnd = (event: TouchEvent): void => {
    if (!canNavigate.value || touchStartX.value === null) {
        return;
    }

    const touchEndX = event.changedTouches[0]?.clientX;

    if (typeof touchEndX !== 'number') {
        touchStartX.value = null;

        return;
    }

    const deltaX = touchEndX - touchStartX.value;

    if (Math.abs(deltaX) >= 40) {
        if (deltaX > 0) {
            previous();
        } else {
            next();
        }
    }

    touchStartX.value = null;
};

const extractFileName = (url: string): string => {
    try {
        const parsed = new URL(url, window.location.origin);
        const pathname = parsed.pathname;
        const name = pathname.split('/').pop() ?? '';

        return name !== '' ? name : 'photo.webp';
    } catch {
        return 'photo.webp';
    }
};

const downloadCurrentPhoto = async (): Promise<void> => {
    if (!currentPhoto.value || currentPhoto.value.url === '') {
        return;
    }

    const url = currentPhoto.value.url;
    const fileName = currentPhoto.value.downloadName ?? extractFileName(url);

    try {
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error('Unable to download image');
        }

        const blob = await response.blob();
        const blobUrl = window.URL.createObjectURL(blob);
        const link = document.createElement('a');

        link.href = blobUrl;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        link.remove();

        window.URL.revokeObjectURL(blobUrl);
    } catch {
        window.open(url, '_blank', 'noopener,noreferrer');
    }
};

onMounted(() => {
    window.addEventListener('keydown', onKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeyDown);
});
</script>

<template>
    <Dialog :open="modelValue" @update:open="emit('update:modelValue', $event)">
        <DialogContent
            class="scrollbar-hide max-h-[94vh] w-[min(95vw,72rem)] overflow-y-auto"
        >
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
            </DialogHeader>

            <div class="space-y-4">
                <div
                    class="overflow-hidden rounded-lg"
                    @touchstart.passive="onTouchStart"
                    @touchend.passive="onTouchEnd"
                >
                    <img
                        v-if="currentPhoto?.url"
                        :src="currentPhoto.url"
                        alt="Photo"
                        class="max-h-[72vh] w-full select-none object-contain"
                        loading="eager"
                        draggable="false"
                    />
                </div>

                <div
                    v-if="currentPhoto?.description || currentPhoto?.postedDiff"
                    class="space-y-1 text-sm text-muted-foreground"
                >
                    <p v-if="currentPhoto?.description">
                        {{ currentPhoto.description }}
                    </p>
                    <p v-if="currentPhoto?.postedDiff">
                        Posted {{ currentPhoto.postedDiff }}
                    </p>
                </div>

                <div class="flex items-center justify-center">
                    <button
                        type="button"
                        class="rounded-md border px-4 py-1.5 text-sm hover:bg-accent"
                        @click="downloadCurrentPhoto"
                    >
                        Download
                    </button>
                </div>

                <div class="space-y-1 text-center">
                    <p class="text-xs text-muted-foreground">
                        {{ currentIndex + 1 }} / {{ normalizedPhotos.length }}
                    </p>
                    <p
                        v-if="canNavigate"
                        class="text-xs text-muted-foreground"
                    >
                        Swipe left or right to browse photos.
                    </p>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
