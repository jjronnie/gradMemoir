<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { Course, CourseYear, University } from '@/types';

defineProps<{
    course: Course & { university: University };
    courseYears: Array<CourseYear>;
}>();
</script>

<template>
    <Head :title="course.name" />

    <PublicLayout>
        <section class="py-10">
            <div class="mx-auto w-[90%] space-y-2">
                <div class="h-px bg-border" />
                <div class="h-px bg-border/70" />
            </div>

            <h1
                class="mx-auto mt-6 max-w-4xl text-center text-4xl font-semibold tracking-wide text-foreground sm:text-5xl"
            >
                {{ course.name }}
            </h1>

            <div class="mx-auto mt-6 h-px w-[90%] bg-border" />

            <div
                class="mx-auto mt-6 grid w-[90%] grid-cols-1 gap-2 text-center text-sm text-muted-foreground md:grid-cols-3"
            >
                <p class="font-medium">{{ course.university?.name }}</p>
                <p class="font-medium">{{ course.short_name }}</p>
                <p class="font-medium">
                    {{ courseYears.length }} cohort{{ courseYears.length === 1 ? '' : 's' }}
                </p>
            </div>

            <p
                v-if="course.nickname"
                class="mt-4 text-center text-sm text-muted-foreground"
            >
                AKA: {{ course.nickname }}
            </p>
        </section>

        <section class="grid gap-4 pb-10 md:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="courseYear in courseYears"
                :key="courseYear.id"
                :href="`/${courseYear.slug}`"
                class="rounded-xl border border-border bg-card p-4 hover:border-primary"
            >
                <p class="font-semibold">Class of {{ courseYear.year }}</p>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ course.short_name.toUpperCase() }} Cohort
                </p>
                <p class="mt-2 text-xs text-muted-foreground">
                    {{ courseYear.active_students_count ?? 0 }} active students
                </p>
            </Link>

            <p
                v-if="courseYears.length === 0"
                class="rounded-xl border border-dashed border-border p-4 text-sm text-muted-foreground"
            >
                No cohorts have been added yet.
            </p>
        </section>
    </PublicLayout>
</template>
