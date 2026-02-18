<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { Course, University } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    university: University;
    courses: Course[];
}>();
</script>

<template>
    <Head :title="university.name" />

    <PublicLayout>
        <section class="py-10">
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
                class="mb-4 h-16 w-16 border border-border object-cover"
                loading="lazy"
            />
            <h1 class="text-3xl font-semibold">{{ university.name }}</h1>
            <p class="mt-2 text-sm text-muted-foreground">
                {{ university.location }}
            </p>
        </section>

        <section class="grid gap-4 pb-10 md:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="course in courses"
                :key="course.id"
                :href="`/courses/${course.slug}`"
                class="rounded-xl border border-border bg-card p-4 hover:border-primary"
            >
                <p class="font-semibold">{{ course.name }}</p>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ course.short_name }} · Class of {{ course.year }}
                </p>
                <p
                    v-if="course.nickname"
                    class="mt-1 text-xs text-muted-foreground"
                >
                    AKA: {{ course.nickname }}
                </p>
                <p class="mt-2 text-xs text-muted-foreground">
                    {{ course.active_students_count ?? 0 }} students
                </p>
            </Link>
        </section>
    </PublicLayout>
</template>
