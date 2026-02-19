<script setup lang="ts">
import ConfirmActionModal from '@/components/ConfirmActionModal.vue';
import PhotoCarousel from '@/components/PhotoCarousel.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type PostPhoto = {
    id: number;
    thumb: string;
    full: string;
    url: string;
    file_name: string;
};

type PostCard = {
    id: number;
    body: string | null;
    published: boolean;
    published_at: string | null;
    created_at: string | null;
    updated_at: string | null;
    photos_count: number;
    photos: PostPhoto[];
};

const props = defineProps<{
    posts: {
        data: PostCard[];
        next_page_url: string | null;
        prev_page_url: string | null;
    };
}>();

const deleteModalOpen = ref(false);
const deleting = ref(false);
const pendingPostId = ref<number | null>(null);
const carouselOpen = ref(false);
const selectedPhotos = ref<
    Array<{
        url: string;
        full: string;
        thumb: string;
        description: string | null;
        postedDiff: string | null;
        postedAt: string | null;
        downloadName: string | null;
    }>
>([]);
const selectedPhotoIndex = ref(0);

const posts = computed(() => props.posts.data);

const toRelative = (value: string | null): string | null => {
    if (value === null || value === '') {
        return null;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    const now = Date.now();
    const seconds = Math.round((date.getTime() - now) / 1000);
    const formatter = new Intl.RelativeTimeFormat(undefined, {
        numeric: 'auto',
    });

    const units: Array<{ unit: Intl.RelativeTimeFormatUnit; size: number }> = [
        { unit: 'year', size: 31536000 },
        { unit: 'month', size: 2592000 },
        { unit: 'week', size: 604800 },
        { unit: 'day', size: 86400 },
        { unit: 'hour', size: 3600 },
        { unit: 'minute', size: 60 },
    ];

    for (const entry of units) {
        if (Math.abs(seconds) >= entry.size) {
            return formatter.format(Math.round(seconds / entry.size), entry.unit);
        }
    }

    return formatter.format(seconds, 'second');
};

const formatDate = (value: string | null): string => {
    if (value === null || value === '') {
        return 'Unknown date';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return 'Unknown date';
    }

    return date.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const requestDelete = (postId: number): void => {
    pendingPostId.value = postId;
    deleteModalOpen.value = true;
};

const deletePost = (): void => {
    if (pendingPostId.value === null) {
        return;
    }

    deleting.value = true;

    router.delete(`/posts/${pendingPostId.value}`, {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            deleteModalOpen.value = false;
            pendingPostId.value = null;
        },
    });
};

const openCarousel = (post: PostCard, initialIndex: number): void => {
    selectedPhotos.value = post.photos.map((photo) => ({
        url: photo.full || photo.url || photo.thumb,
        full: photo.full,
        thumb: photo.thumb,
        description: post.body,
        postedDiff: toRelative(post.published_at ?? post.created_at),
        postedAt: post.published_at ?? post.created_at,
        downloadName: photo.file_name,
    }));
    selectedPhotoIndex.value = initialIndex;
    carouselOpen.value = true;
};
</script>

<template>
    <Head title="My Posts" />

    <AppLayout>
        <div class="mx-auto w-full max-w-6xl space-y-6 p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h1 class="text-2xl font-semibold">My Posts</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage your uploaded memories.
                    </p>
                </div>

                <Link href="/posts/create">
                    <Button>Add Photo</Button>
                </Link>
            </div>

            <div
                v-if="posts.length === 0"
                class="rounded-xl border border-dashed border-border p-8 text-center text-sm text-muted-foreground"
            >
                You have not posted anything yet.
            </div>

            <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <article
                    v-for="post in posts"
                    :key="post.id"
                    class="space-y-4 rounded-xl border border-border bg-card p-4"
                >
                    <div
                        v-if="post.photos.length > 0"
                        class="grid grid-cols-2 gap-2"
                    >
                        <button
                            v-for="(photo, index) in post.photos.slice(0, 4)"
                            :key="photo.id"
                            type="button"
                            class="overflow-hidden rounded-md border border-border"
                            @click="openCarousel(post, index)"
                        >
                            <img
                                :src="photo.thumb || photo.full || photo.url"
                                alt="Post photo"
                                class="aspect-[3/4] w-full object-cover transition-transform duration-200 hover:scale-[1.03]"
                                loading="lazy"
                            />
                        </button>
                    </div>

                    <p class="text-sm leading-relaxed">
                        {{ post.body || 'Photo post' }}
                    </p>

                    <div class="space-y-1 text-xs text-muted-foreground">
                        <p>
                            {{ post.published ? 'Published' : 'Processing' }}
                        </p>
                        <p>{{ post.photos_count }} photo(s)</p>
                        <p>Created {{ formatDate(post.created_at) }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <Link :href="`/posts/${post.id}/edit`">
                            <Button variant="outline" size="sm">Edit</Button>
                        </Link>
                        <Button
                            variant="destructive"
                            size="sm"
                            @click="requestDelete(post.id)"
                        >
                            Delete
                        </Button>
                    </div>
                </article>
            </div>

            <section class="flex items-center justify-between">
                <Link
                    v-if="props.posts.prev_page_url"
                    :href="props.posts.prev_page_url"
                    class="rounded-md border border-border px-3 py-1.5 text-sm hover:bg-accent"
                >
                    Previous
                </Link>
                <div v-else />

                <Link
                    v-if="props.posts.next_page_url"
                    :href="props.posts.next_page_url"
                    class="rounded-md border border-border px-3 py-1.5 text-sm hover:bg-accent"
                >
                    Next
                </Link>
            </section>
        </div>

        <ConfirmActionModal
            :open="deleteModalOpen"
            title="Delete Post"
            description="This will permanently delete the post and remove all its photos from storage."
            confirm-text="Delete Post"
            :processing="deleting"
            @update:open="deleteModalOpen = $event"
            @confirm="deletePost"
        />

        <PhotoCarousel
            v-model="carouselOpen"
            :photos="selectedPhotos"
            :initial-index="selectedPhotoIndex"
            title="Post Photos"
        />
    </AppLayout>
</template>
