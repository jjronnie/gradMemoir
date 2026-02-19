<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import { useProgress } from '@/composables/useProgress';
import AppLayout from '@/layouts/AppLayout.vue';
import type { AppPageProps } from '@/types';

const page = usePage<AppPageProps>();
const body = ref('');
const photos = ref<File[]>([]);
const processing = ref(false);
const isUploading = ref(false);
const uploadPercent = ref(0);
const errors = ref<Record<string, string>>({});
const successMessage = ref('');
const conversionPercent = ref(0);

const progress = useProgress();

const previews = computed(() =>
    photos.value.map((file) => ({
        file,
        url: URL.createObjectURL(file),
    })),
);
const photoLimit = computed(
    () => (page.props.auth.user?.photo_limit as number | undefined) ?? 8,
);
const photoCount = computed(
    () => (page.props.auth.user?.photo_count as number | undefined) ?? 0,
);
const photoSlotsRemaining = computed(
    () =>
        (page.props.auth.user?.photo_slots_remaining as number | undefined) ??
        0,
);
const hasReachedPhotoLimit = computed(() => photoSlotsRemaining.value <= 0);

const onSelectFiles = (event: Event): void => {
    if (hasReachedPhotoLimit.value) {
        return;
    }

    const input = event.target as HTMLInputElement;
    const selected = Array.from(input.files ?? []);
    const allowedForThisUpload = Math.min(photoSlotsRemaining.value, 4);
    const merged = [...photos.value, ...selected].slice(
        0,
        allowedForThisUpload,
    );
    photos.value = merged;
};

const removePhoto = (index: number): void => {
    photos.value = photos.value.filter((_, i) => i !== index);
};

const submit = async (): Promise<void> => {
    errors.value = {};

    if (photos.value.length === 0) {
        errors.value.photos = 'Please attach at least one photo.';
        return;
    }

    if (hasReachedPhotoLimit.value) {
        errors.value.photos =
            'You have reached the maximum of 8 photos. Delete one to add another.';
        return;
    }

    processing.value = true;
    isUploading.value = true;
    uploadPercent.value = 0;
    conversionPercent.value = 0;

    const formData = new FormData();
    formData.append('body', body.value);
    photos.value.forEach((photo) => {
        formData.append('photos[]', photo);
    });

    const uploadPost = (): Promise<{
        status: number;
        payload: {
            data?: { post_id?: number };
            message?: string;
            errors?: Record<string, string[]>;
        };
    }> =>
        new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();

            xhr.open('POST', '/posts');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader(
                'X-CSRF-TOKEN',
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') ?? '',
            );

            xhr.upload.onprogress = (
                event: ProgressEvent<EventTarget>,
            ): void => {
                if (!event.lengthComputable) {
                    return;
                }

                uploadPercent.value = Math.round(
                    (event.loaded / event.total) * 100,
                );
            };

            xhr.onload = (): void => {
                try {
                    const payload = JSON.parse(xhr.responseText) as {
                        data?: { post_id?: number };
                        message?: string;
                        errors?: Record<string, string[]>;
                    };

                    resolve({
                        status: xhr.status,
                        payload,
                    });
                } catch {
                    reject(new Error('Invalid upload response'));
                }
            };

            xhr.onerror = (): void => {
                reject(new Error('Upload failed'));
            };

            xhr.send(formData);
        });

    try {
        const response = await uploadPost();
        isUploading.value = false;
        uploadPercent.value = 100;
        const payload = response.payload;

        if (response.status < 200 || response.status >= 300) {
            errors.value = Object.fromEntries(
                Object.entries(payload.errors ?? {}).map(([key, value]) => [
                    key,
                    value[0] ?? 'Invalid value.',
                ]),
            );
            processing.value = false;
            isUploading.value = false;
            return;
        }

        const postId = payload.data?.post_id;

        if (postId === undefined) {
            processing.value = false;
            return;
        }

        successMessage.value =
            payload.message ??
            'Photo uploaded successfully. Your post will be visible in a minute or two.';
        progress.start();
        conversionPercent.value = 10;

        const interval = window.setInterval(async () => {
            const statusResponse = await fetch(`/posts/${postId}/status`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const statusPayload = (await statusResponse.json()) as {
                data?: { progress?: number; published?: boolean };
            };

            progress.setPercent(statusPayload.data?.progress ?? 0);
            conversionPercent.value = statusPayload.data?.progress ?? 0;

            if (statusPayload.data?.published) {
                window.clearInterval(interval);
                progress.finish();
                body.value = '';
                photos.value = [];
                processing.value = false;
                isUploading.value = false;
                uploadPercent.value = 0;
                conversionPercent.value = 0;
            }
        }, 2000);
    } catch {
        errors.value.body = 'Failed to publish post.';
        processing.value = false;
        isUploading.value = false;
        uploadPercent.value = 0;
        conversionPercent.value = 0;
    }
};
</script>

<template>
    <Head title="Add Photo" />

    <AppLayout>
        <div class="mx-auto w-full max-w-3xl space-y-5 p-4">
            <h1 class="text-2xl font-semibold">Add Photo</h1>
            <p
                v-if="successMessage !== ''"
                class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-700 dark:text-emerald-300"
            >
                {{ successMessage }}
            </p>
            <div v-if="isUploading" class="space-y-1">
                <p class="text-xs text-muted-foreground">
                    Uploading photos... {{ uploadPercent }}%
                </p>
                <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                    <div
                        class="h-full bg-primary transition-all duration-150"
                        :style="{ width: `${uploadPercent}%` }"
                    />
                </div>
            </div>
            <div v-else-if="processing" class="space-y-1">
                <p class="text-xs text-muted-foreground">
                    Processing photos... {{ conversionPercent }}%
                </p>
                <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                    <div
                        class="h-full bg-primary transition-all duration-200"
                        :style="{ width: `${conversionPercent}%` }"
                    />
                </div>
            </div>
            <p class="text-sm text-muted-foreground">
                Photos used: {{ photoCount }} / {{ photoLimit }}. Remaining:
                {{ photoSlotsRemaining }}.
            </p>

            <div class="grid gap-2">
                <label for="post-body" class="text-sm font-medium"
                    >Caption</label
                >
                <textarea
                    id="post-body"
                    v-model="body"
                    rows="5"
                    class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                    placeholder="Write something about this photo (optional)..."
                />
                <InputError :message="errors.body" />
            </div>

            <div class="grid gap-3">
                <label
                    class="flex min-h-28 cursor-pointer items-center justify-center rounded-lg border border-dashed border-border text-sm text-muted-foreground"
                    :class="{
                        'pointer-events-none opacity-60': hasReachedPhotoLimit,
                    }"
                >
                    Click to upload photos (max 4)
                    <input
                        type="file"
                        class="hidden"
                        accept=".jpg,.jpeg,.png,.webp,.gif,.avif"
                        multiple
                        :disabled="hasReachedPhotoLimit"
                        @change="onSelectFiles"
                    />
                </label>
                <InputError :message="errors.photos" />

                <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                    <div
                        v-for="(preview, index) in previews"
                        :key="index"
                        class="relative overflow-hidden border border-border"
                    >
                        <img
                            :src="preview.url"
                            alt="Selected photo"
                            class="aspect-square w-full object-cover"
                        />
                        <button
                            type="button"
                            class="absolute top-1 right-1 rounded-full bg-background/90 px-2 py-1 text-xs"
                            aria-label="Remove photo"
                            @click="removePhoto(index)"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </div>

            <LoadingButton
                :loading="processing"
                loading-text="Adding..."
                :disabled="hasReachedPhotoLimit"
                @click="submit"
            >
                Add Photo
            </LoadingButton>
        </div>
    </AppLayout>
</template>
