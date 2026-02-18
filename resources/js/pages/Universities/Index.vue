<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { University } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    universities: {
        data: University[];
        links: Array<Record<string, unknown>>;
    };
}>();
</script>

<template>
    <Head title="Universities" />

    <PublicLayout>
        <section class="py-10">
            <h1 class="text-3xl font-semibold">Universities</h1>
            <p class="mt-2 text-sm text-muted-foreground">
                Explore available university archives.
            </p>
        </section>

        <section class="grid gap-4 pb-10 md:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="university in universities.data"
                :key="university.id"
                :href="`/universities/${university.slug}`"
                class="rounded-xl border border-border bg-card p-4 hover:border-primary"
            >
                <img
                    v-if="
                        university.media?.[0]?.conversions?.thumb ||
                        university.media?.[0]?.original_url
                    "
                    :src="
                        university.media?.[0]?.conversions?.full ??
                        university.media?.[0]?.conversions?.thumb ??
                        university.media?.[0]?.original_url ??
                        ''
                    "
                    :alt="`${university.name} logo`"
                    class="mb-3 h-12 w-12 border border-border object-cover"
                    loading="lazy"
                />
                <p class="font-semibold">{{ university.name }}</p>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ university.location }}
                </p>
                <p class="mt-2 text-xs text-muted-foreground">
                    {{ university.courses_count ?? 0 }} courses
                </p>
            </Link>
        </section>
    </PublicLayout>
</template>
