<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';
import ConfirmActionModal from '@/components/ConfirmActionModal.vue';
import InputError from '@/components/InputError.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';

type MediaPayload = {
    original_url?: string;
    conversions?: Record<string, string>;
};

type FeaturedProfile = {
    id: number;
    sort_order: number;
    user?: {
        id: number;
        name: string;
        username: string;
        email: string;
        media?: MediaPayload[];
    };
};

type FeaturedImage = {
    id: number;
    is_ready: boolean;
    created_at?: string | null;
    media?: MediaPayload[];
};

type SearchResultUser = {
    id: number;
    name: string;
    username: string;
    email: string;
    media?: MediaPayload[];
    featured_profile?: {
        id: number;
    } | null;
};

const props = defineProps<{
    featuredProfiles: {
        data: FeaturedProfile[];
    };
    featuredImages: FeaturedImage[];
    search: string;
    searchResults: SearchResultUser[];
}>();

const form = useForm({
    user_id: '',
});

const featuredImageForm = useForm({
    images: [] as File[],
});

const searchQuery = ref(props.search);
const addingUserId = ref<number | null>(null);
const deletingProfileId = ref<number | null>(null);
const deletingImageId = ref<number | null>(null);
const uploadingFeaturedImage = ref(false);
const featuredImagePreviewUrls = ref<string[]>([]);
const selectedPreviewIndex = ref(0);
const featuredImageInput = ref<HTMLInputElement | null>(null);
const imageViewerOpen = ref(false);
const imageViewerUrl = ref<string | null>(null);
const imageViewerLabel = ref('');
const confirmOpen = ref(false);
const confirmTitle = ref('');
const confirmDescription = ref('');
const pendingAction = ref<
    | { type: 'add'; user: SearchResultUser }
    | { type: 'remove'; id: number }
    | { type: 'remove-image'; id: number }
    | null
>(null);

const featuredImageCount = computed(() => props.featuredImages.length);
const selectedPreviewUrl = computed(
    () => featuredImagePreviewUrls.value[selectedPreviewIndex.value] ?? null,
);
const featuredImageErrorMessages = computed(() =>
    Object.entries(featuredImageForm.errors)
        .filter(
            ([key, value]) =>
                (key === 'images' || key.startsWith('images.')) &&
                typeof value === 'string' &&
                value.trim() !== '',
        )
        .map(([, value]) => value as string),
);
const confirmText = computed(() =>
    pendingAction.value?.type === 'add' ? 'Add' : 'Remove',
);
const confirmVariant = computed(() =>
    pendingAction.value?.type === 'add' ? 'default' : 'destructive',
);
const confirmProcessing = computed(
    () =>
        addingUserId.value !== null ||
        deletingProfileId.value !== null ||
        deletingImageId.value !== null,
);

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const applySearch = (): void => {
    if (searchTimeout !== null) {
        clearTimeout(searchTimeout);
    }

    searchTimeout = setTimeout(() => {
        router.get(
            '/admin/featured-profiles',
            { search: searchQuery.value },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }, 300);
};

const mediaPhotoUrl = (media?: MediaPayload[]): string | null => {
    return (
        media?.[0]?.conversions?.thumb ??
        media?.[0]?.conversions?.full ??
        media?.[0]?.original_url ??
        null
    );
};

const formatDate = (value?: string | null): string => {
    if (value === undefined || value === null || value === '') {
        return 'Unknown date';
    }

    return new Date(value).toLocaleString();
};

const clearFeaturedImagePreview = (): void => {
    featuredImagePreviewUrls.value.forEach((previewUrl) => {
        URL.revokeObjectURL(previewUrl);
    });
    featuredImagePreviewUrls.value = [];
};

const resetFeaturedImageSelection = (): void => {
    clearFeaturedImagePreview();
    featuredImageForm.reset('images');
    selectedPreviewIndex.value = 0;
    if (featuredImageInput.value !== null) {
        featuredImageInput.value.value = '';
    }
};

const onFeaturedImageSelected = (event: Event): void => {
    featuredImageForm.clearErrors();

    const input = event.target as HTMLInputElement;
    const files = Array.from(input.files ?? []);

    clearFeaturedImagePreview();
    featuredImageForm.images = files;
    featuredImagePreviewUrls.value = files.map((file) =>
        URL.createObjectURL(file),
    );
    selectedPreviewIndex.value = 0;
};

const setSelectedPreview = (index: number): void => {
    selectedPreviewIndex.value = index;
};

const openImageViewer = (featuredImage: FeaturedImage): void => {
    const photoUrl = mediaPhotoUrl(featuredImage.media);

    if (photoUrl === null) {
        return;
    }

    imageViewerUrl.value = photoUrl;
    imageViewerLabel.value = formatDate(featuredImage.created_at);
    imageViewerOpen.value = true;
};

const uploadFeaturedImage = (): void => {
    featuredImageForm.clearErrors();

    if (featuredImageForm.images.length === 0) {
        featuredImageForm.setError(
            'images',
            'Please select at least one image.',
        );

        return;
    }

    uploadingFeaturedImage.value = true;

    featuredImageForm.post('/admin/featured-profiles/images', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            resetFeaturedImageSelection();
        },
        onFinish: () => {
            uploadingFeaturedImage.value = false;
        },
    });
};

const requestAddProfile = (user: SearchResultUser): void => {
    pendingAction.value = { type: 'add', user };
    confirmTitle.value = 'Add Featured Profile';
    confirmDescription.value = `Add ${user.name} to featured profiles?`;
    confirmOpen.value = true;
};

const requestRemoveProfile = (featuredProfileId: number): void => {
    pendingAction.value = { type: 'remove', id: featuredProfileId };
    confirmTitle.value = 'Remove Featured Profile';
    confirmDescription.value = 'Remove this profile from the featured list?';
    confirmOpen.value = true;
};

const requestRemoveImage = (featuredImageId: number): void => {
    pendingAction.value = { type: 'remove-image', id: featuredImageId };
    confirmTitle.value = 'Remove Featured Image';
    confirmDescription.value = 'Delete this featured image?';
    confirmOpen.value = true;
};

const executeConfirmAction = (): void => {
    if (pendingAction.value === null) {
        return;
    }

    if (pendingAction.value.type === 'add') {
        const user = pendingAction.value.user;

        form.clearErrors();
        form.user_id = String(user.id);
        addingUserId.value = user.id;

        form.post('/admin/featured-profiles', {
            preserveScroll: true,
            onFinish: () => {
                addingUserId.value = null;
                confirmOpen.value = false;
                pendingAction.value = null;
            },
        });

        return;
    }

    if (pendingAction.value.type === 'remove') {
        deletingProfileId.value = pendingAction.value.id;

        router.delete(`/admin/featured-profiles/${pendingAction.value.id}`, {
            preserveScroll: true,
            onFinish: () => {
                deletingProfileId.value = null;
                confirmOpen.value = false;
                pendingAction.value = null;
            },
        });

        return;
    }

    deletingImageId.value = pendingAction.value.id;

    router.delete(`/admin/featured-profiles/images/${pendingAction.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deletingImageId.value = null;
            confirmOpen.value = false;
            pendingAction.value = null;
        },
    });
};

onBeforeUnmount(() => {
    clearFeaturedImagePreview();

    if (searchTimeout !== null) {
        clearTimeout(searchTimeout);
    }
});
</script>

<template>
    <Head title="Featured Profiles" />

    <AppLayout>
        <div class="space-y-4 p-4">
            <h1 class="text-2xl font-semibold">Featured Profiles</h1>

            <div class="rounded-xl border border-border bg-card p-5">
                <div
                    class="mb-4 flex flex-col gap-1 border-b border-border pb-4 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <h2 class="text-lg font-semibold">Featured Image Studio</h2>
                        <p class="text-sm text-muted-foreground">
                            Select images to upload. Validation errors will appear below.
                        </p>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ featuredImageCount }} uploaded total
                    </p>
                </div>

                <div class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr),minmax(0,1fr)]">
                    <section class="space-y-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium">
                                Upload featured images
                            </label>
                            <input
                                ref="featuredImageInput"
                                type="file"
                                multiple
                                accept="image/jpeg,image/png,image/webp,image/gif,image/avif"
                                class="block w-full cursor-pointer rounded-md border border-input bg-background px-3 py-2 text-sm"
                                @change="onFeaturedImageSelected"
                            />
                            <div class="space-y-1">
                                <InputError
                                    v-for="(errorMessage, index) in featuredImageErrorMessages"
                                    :key="`featured-image-error-${index}`"
                                    :message="errorMessage"
                                />
                            </div>
                            <LoadingButton
                                type="button"
                                :loading="uploadingFeaturedImage"
                                loading-text="Uploading..."
                                @click="uploadFeaturedImage"
                            >
                                Upload Selected Images
                            </LoadingButton>
                        </div>

                        <div
                            class="overflow-hidden rounded-xl border border-border bg-muted/20"
                        >
                            <img
                                v-if="selectedPreviewUrl"
                                :src="selectedPreviewUrl"
                                alt="Selected featured image preview"
                                class="h-[24rem] w-full object-contain"
                            />
                            <div
                                v-else
                                class="flex h-[24rem] items-center justify-center px-4 text-center text-sm text-muted-foreground"
                            >
                                Full-size preview appears here after selecting images.
                            </div>
                        </div>

                        <div
                            v-if="featuredImagePreviewUrls.length > 0"
                            class="grid grid-cols-5 gap-2"
                        >
                            <button
                                v-for="(previewUrl, index) in featuredImagePreviewUrls"
                                :key="previewUrl"
                                type="button"
                                class="overflow-hidden rounded-md border transition hover:opacity-90"
                                :class="
                                    selectedPreviewIndex === index
                                        ? 'border-primary ring-2 ring-primary/40'
                                        : 'border-border'
                                "
                                @click="setSelectedPreview(index)"
                            >
                                <img
                                    :src="previewUrl"
                                    alt="Selected preview thumbnail"
                                    class="h-16 w-full object-cover"
                                />
                            </button>
                        </div>
                    </section>

                    <section class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                                Uploaded
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                Click thumbnail to view full image
                            </p>
                        </div>

                        <div
                            v-if="featuredImages.length > 0"
                            class="grid max-h-[33rem] grid-cols-3 gap-2 overflow-y-auto pr-1 sm:grid-cols-4 lg:grid-cols-6"
                        >
                            <article
                                v-for="featuredImage in featuredImages"
                                :key="featuredImage.id"
                                class="rounded-md border border-border bg-background p-2"
                            >
                                <button
                                    type="button"
                                    class="block w-full"
                                    @click="openImageViewer(featuredImage)"
                                >
                                    <img
                                        v-if="mediaPhotoUrl(featuredImage.media)"
                                        :src="mediaPhotoUrl(featuredImage.media) ?? ''"
                                        alt="Uploaded featured image"
                                        class="h-16 w-full rounded object-cover"
                                        loading="lazy"
                                    />
                                    <div
                                        v-else
                                        class="flex h-16 w-full items-center justify-center rounded bg-muted text-[10px] text-muted-foreground"
                                    >
                                        N/A
                                    </div>
                                </button>
                                <div class="mt-1 flex items-center justify-between gap-1">
                                    <p
                                        class="text-[10px]"
                                        :class="
                                            featuredImage.is_ready
                                                ? 'text-emerald-600'
                                                : 'text-amber-600'
                                        "
                                    >
                                        {{ featuredImage.is_ready ? 'Ready' : 'Processing' }}
                                    </p>
                                    <button
                                        type="button"
                                        class="text-[10px] font-medium text-red-600 hover:underline disabled:opacity-50"
                                        :disabled="deletingImageId === featuredImage.id"
                                        @click="requestRemoveImage(featuredImage.id)"
                                    >
                                        {{
                                            deletingImageId === featuredImage.id
                                                ? '...'
                                                : 'Delete'
                                        }}
                                    </button>
                                </div>
                            </article>
                        </div>

                        <p
                            v-else
                            class="rounded-lg border border-dashed border-border p-6 text-center text-sm text-muted-foreground"
                        >
                            No featured images uploaded yet.
                        </p>
                    </section>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-card p-4">
                <label class="mb-2 block text-sm font-medium"
                    >Search users to add/remove</label
                >
                <Input
                    v-model="searchQuery"
                    placeholder="Search by name, username, or email..."
                    @input="applySearch"
                />
                <InputError :message="form.errors.user_id" class="mt-2" />

                <div
                    v-if="searchQuery.trim() !== ''"
                    class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3"
                >
                    <article
                        v-for="user in searchResults"
                        :key="user.id"
                        class="rounded-lg border border-border p-3"
                    >
                        <div class="flex items-start gap-3">
                            <img
                                v-if="mediaPhotoUrl(user.media)"
                                :src="mediaPhotoUrl(user.media) ?? ''"
                                :alt="user.name"
                                class="h-12 w-12 rounded object-cover"
                                loading="lazy"
                            />
                            <div
                                v-else
                                class="flex h-12 w-12 items-center justify-center rounded bg-muted text-xs text-muted-foreground"
                            >
                                N/A
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">
                                    {{ user.name }}
                                </p>
                                <p class="truncate text-xs text-muted-foreground">
                                    @{{ user.username }}
                                </p>
                                <p class="truncate text-xs text-muted-foreground">
                                    {{ user.email }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <a
                                :href="`/@${user.username}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex h-8 items-center rounded-md border border-border px-3 text-xs hover:bg-accent"
                            >
                                Show
                            </a>
                            <LoadingButton
                                v-if="!user.featured_profile"
                                type="button"
                                size="sm"
                                :loading="addingUserId === user.id"
                                loading-text="Adding..."
                                @click="requestAddProfile(user)"
                            >
                                Add
                            </LoadingButton>
                            <LoadingButton
                                v-else
                                type="button"
                                size="sm"
                                variant="destructive"
                                :loading="
                                    deletingProfileId === user.featured_profile.id
                                "
                                loading-text="Removing..."
                                @click="
                                    requestRemoveProfile(
                                        user.featured_profile.id,
                                    )
                                "
                            >
                                Remove
                            </LoadingButton>
                        </div>
                    </article>

                    <p
                        v-if="searchResults.length === 0"
                        class="text-sm text-muted-foreground"
                    >
                        No matching users found.
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-card p-4">
                <p
                    v-if="featuredProfiles.data.length > 20"
                    class="mb-3 text-xs text-amber-600"
                >
                    Warning: more than 20 featured profiles may reduce homepage
                    quality.
                </p>
                <div
                    v-if="featuredProfiles.data.length > 0"
                    class="overflow-x-auto rounded-lg border border-border"
                >
                    <table class="min-w-full text-left text-sm">
                        <thead
                            class="bg-muted/40 text-xs tracking-wide text-muted-foreground uppercase"
                        >
                            <tr>
                                <th class="px-4 py-3">Order</th>
                                <th class="px-4 py-3">Photo</th>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Username</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="profile in featuredProfiles.data"
                                :key="profile.id"
                                class="border-t border-border align-middle"
                            >
                                <td class="px-4 py-3">{{ profile.sort_order }}</td>
                                <td class="px-4 py-3">
                                    <img
                                        v-if="mediaPhotoUrl(profile.user?.media)"
                                        :src="mediaPhotoUrl(profile.user?.media) ?? ''"
                                        :alt="profile.user?.name ?? 'Profile photo'"
                                        class="h-10 w-10 rounded object-cover"
                                        loading="lazy"
                                    />
                                    <div
                                        v-else
                                        class="flex h-10 w-10 items-center justify-center rounded bg-muted text-xs text-muted-foreground"
                                    >
                                        N/A
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-medium">
                                    {{ profile.user?.name ?? 'Unknown user' }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    @{{ profile.user?.username }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ profile.user?.email }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <a
                                            v-if="profile.user?.username"
                                            :href="`/@${profile.user.username}`"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex h-8 items-center rounded-md border border-border px-3 text-xs hover:bg-accent"
                                        >
                                            Show
                                        </a>
                                        <LoadingButton
                                            type="button"
                                            size="sm"
                                            variant="destructive"
                                            :loading="deletingProfileId === profile.id"
                                            loading-text="Removing..."
                                            @click="requestRemoveProfile(profile.id)"
                                        >
                                            Remove
                                        </LoadingButton>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p v-else class="text-sm text-muted-foreground">
                    No featured profiles yet.
                </p>
            </div>
        </div>

        <Dialog :open="imageViewerOpen" @update:open="imageViewerOpen = $event">
            <DialogContent class="max-w-4xl">
                <DialogHeader>
                    <DialogTitle>Featured Image</DialogTitle>
                </DialogHeader>

                <div class="space-y-2">
                    <div
                        class="overflow-hidden rounded-lg border border-border bg-muted/20"
                    >
                        <img
                            v-if="imageViewerUrl"
                            :src="imageViewerUrl"
                            alt="Featured image full view"
                            class="max-h-[70vh] w-full object-contain"
                        />
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Uploaded {{ imageViewerLabel }}
                    </p>
                </div>
            </DialogContent>
        </Dialog>

        <ConfirmActionModal
            :open="confirmOpen"
            :title="confirmTitle"
            :description="confirmDescription"
            :confirm-text="confirmText"
            :confirm-variant="confirmVariant"
            :processing="confirmProcessing"
            @update:open="confirmOpen = $event"
            @confirm="executeConfirmAction"
        />
    </AppLayout>
</template>
