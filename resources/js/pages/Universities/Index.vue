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
            <div class="mx-auto w-[90%] space-y-2">
                <div class="h-px bg-border" />
                <div class="h-px bg-border/70" />
            </div>
            <h1 class="mt-6 text-center text-3xl font-semibold sm:text-4xl">
                University Archives
            </h1>
            <div class="mx-auto mt-6 h-px w-[90%] bg-border" />
            <p class="mt-4 text-center text-sm text-muted-foreground">
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
                <div class="flex items-center gap-3">
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
                        class="h-12 w-12 rounded-full border border-border object-cover"
                        loading="lazy"
                    />
                    <p class="font-semibold">{{ university.name }}</p>
                </div>
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
