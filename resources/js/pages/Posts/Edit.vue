<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';

type PostPhoto = {
    id: number;
    thumb: string;
    full: string;
    url: string;
    file_name: string;
};

const props = defineProps<{
    post: {
        id: number;
        body: string | null;
        photos: PostPhoto[];
    };
}>();

const form = useForm({
    body: props.post.body ?? '',
});

const submit = (): void => {
    form.put(`/posts/${props.post.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Post" />

    <AppLayout>
        <div class="mx-auto w-full max-w-3xl space-y-5 p-4">
            <div class="flex items-center justify-between gap-2">
                <h1 class="text-2xl font-semibold">Edit Post</h1>
                <Link
                    href="/posts"
                    class="rounded-md border border-border px-3 py-1.5 text-sm hover:bg-accent"
                >
                    Back to Posts
                </Link>
            </div>

            <p class="text-sm text-muted-foreground">
                Only the post body can be changed. Uploaded photos stay the
                same.
            </p>

            <div class="grid gap-2">
                <label for="post-body" class="text-sm font-medium">Caption</label>
                <textarea
                    id="post-body"
                    v-model="form.body"
                    rows="6"
                    class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                    placeholder="Write something about this memory..."
                />
                <InputError :message="form.errors.body" />
            </div>

            <div v-if="post.photos.length > 0" class="grid grid-cols-2 gap-3 md:grid-cols-4">
                <div
                    v-for="photo in post.photos"
                    :key="photo.id"
                    class="overflow-hidden rounded-md border border-border"
                >
                    <img
                        :src="photo.thumb || photo.full || photo.url"
                        alt="Post photo"
                        class="aspect-[3/4] w-full object-cover"
                        loading="lazy"
                    />
                </div>
            </div>

            <Button :disabled="form.processing" @click="submit">
                {{ form.processing ? 'Saving...' : 'Save Changes' }}
            </Button>
        </div>
    </AppLayout>
</template>
