<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { AppPageProps, Post } from '@/types';

const props = defineProps<{
    recentPosts: Post[];
    managedCourseYear: {
        id: number;
        year: string;
        slug: string;
        course_id: number;
    } | null;
    myUniversity: {
        id: number;
        name: string;
        slug: string;
    } | null;
    photoUsage: {
        used: number;
        limit: number;
        remaining: number;
    };
}>();

const page = usePage<AppPageProps>();
const user = computed(() => page.props.auth.user);

const greeting = computed(() => {
    const hour = new Date().getHours();

    if (hour < 12) {
        return 'Good morning';
    }

    if (hour < 18) {
        return 'Good afternoon';
    }

    return 'Good evening';
});

const roleLabel = computed(() => {
    const role = user.value?.roles?.[0] ?? 'student';

    return role.charAt(0).toUpperCase() + role.slice(1);
});

const isAdmin = computed(() => user.value?.roles?.includes('admin') ?? false);
const courseYearUrl = computed(() => user.value?.course_year_url ?? '/dashboard');
const profileUrl = computed(() => user.value?.profile_url ?? '/dashboard');

const photoProgressPercent = computed(() => {
    if (props.photoUsage.limit <= 0) {
        return 0;
    }

    return Math.min(Math.round((props.photoUsage.used / props.photoUsage.limit) * 100), 100);
});

const usageTone = computed(() => {
    if (props.photoUsage.remaining <= 1) {
        return 'text-destructive';
    }

    if (props.photoUsage.remaining <= 3) {
        return 'text-amber-600';
    }

    return 'text-emerald-600';
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="mx-auto w-full max-w-6xl space-y-6 p-4">
            <section class="rounded-2xl border border-border bg-card p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div class="space-y-1">
                        <p class="text-sm text-muted-foreground">{{ greeting }},</p>
                        <h1 class="text-3xl font-semibold">{{ user?.name }}</h1>
                        <p class="text-sm text-muted-foreground">
                            @{{ user?.username }} · {{ user?.email }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full border border-border px-3 py-1 text-xs font-medium text-muted-foreground">
                            {{ roleLabel }}
                        </span>
                        <span
                            class="rounded-full border border-border px-3 py-1 text-xs font-medium"
                            :class="user?.is_verified ? 'text-emerald-600' : 'text-muted-foreground'"
                        >
                            {{ user?.is_verified ? 'Verified' : 'Unverified' }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-xl border border-border bg-card p-4">
                    <p class="text-xs uppercase tracking-wider text-muted-foreground">Photos Used</p>
                    <p class="mt-2 text-2xl font-semibold">{{ photoUsage.used }} / {{ photoUsage.limit }}</p>
                </article>

                <article class="rounded-xl border border-border bg-card p-4">
                    <p class="text-xs uppercase tracking-wider text-muted-foreground">Slots Remaining</p>
                    <p class="mt-2 text-2xl font-semibold" :class="usageTone">{{ photoUsage.remaining }}</p>
                </article>

                <article class="rounded-xl border border-border bg-card p-4">
                    <p class="text-xs uppercase tracking-wider text-muted-foreground">Recent Posts</p>
                    <p class="mt-2 text-2xl font-semibold">{{ recentPosts.length }}</p>
                </article>

                <article class="rounded-xl border border-border bg-card p-4">
                    <p class="text-xs uppercase tracking-wider text-muted-foreground">University</p>
                    <p class="mt-2 text-sm font-semibold">{{ myUniversity?.name ?? 'Not set' }}</p>
                </article>
            </section>

            <section class="grid gap-4 lg:grid-cols-5">
                <article class="rounded-xl border border-border bg-card p-4 lg:col-span-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold">Photo Capacity</h2>
                        <span class="text-xs text-muted-foreground">{{ photoProgressPercent }}%</span>
                    </div>
                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full bg-primary transition-all"
                            :style="{ width: `${photoProgressPercent}%` }"
                        />
                    </div>
                    <p class="mt-3 text-sm text-muted-foreground">
                        You can still upload
                        <span class="font-medium text-foreground">{{ photoUsage.remaining }}</span>
                        photo<span v-if="photoUsage.remaining !== 1">s</span>.
                    </p>
                </article>

                <article class="rounded-xl border border-border bg-card p-4 lg:col-span-2">
                    <h2 class="text-base font-semibold">Quick Actions</h2>
                    <div class="mt-3 grid gap-2">
                        <Link
                            href="/posts/create"
                            class="rounded-md border border-border px-3 py-2 text-sm font-medium hover:bg-accent"
                        >
                            Add Photo
                        </Link>
                        <Link
                            href="/posts"
                            class="rounded-md border border-border px-3 py-2 text-sm font-medium hover:bg-accent"
                        >
                            Manage My Posts
                        </Link>
                        <Link
                            :href="courseYearUrl"
                            class="rounded-md border border-border px-3 py-2 text-sm font-medium hover:bg-accent"
                        >
                            Open My Cohort
                        </Link>
                        <Link
                            href="/settings/profile"
                            class="rounded-md border border-border px-3 py-2 text-sm font-medium hover:bg-accent"
                        >
                            Profile Settings
                        </Link>
                        <Link
                            v-if="isAdmin"
                            href="/course-admin"
                            class="rounded-md border border-border px-3 py-2 text-sm font-medium hover:bg-accent"
                        >
                            Manage Cohort Members
                        </Link>
                    </div>
                </article>
            </section>

            <section class="grid gap-4 lg:grid-cols-5">
                <article class="rounded-xl border border-border bg-card p-4 lg:col-span-2">
                    <h2 class="text-base font-semibold">Academic Profile</h2>
                    <div class="mt-3 space-y-3 text-sm">
                        <p class="text-muted-foreground">
                            University:
                            <Link
                                v-if="myUniversity"
                                :href="`/universities/${myUniversity.slug}`"
                                class="font-medium text-foreground underline underline-offset-4"
                            >
                                {{ myUniversity.name }}
                            </Link>
                            <span v-else class="font-medium text-foreground">Not set</span>
                        </p>
                        <p class="text-muted-foreground">
                            My profile:
                            <Link :href="profileUrl" class="font-medium text-foreground underline underline-offset-4">
                                View public profile
                            </Link>
                        </p>
                        <p class="text-muted-foreground">
                            Cohort:
                            <Link :href="courseYearUrl" class="font-medium text-foreground underline underline-offset-4">
                                Open class archive
                            </Link>
                        </p>
                    </div>
                </article>

                <article class="rounded-xl border border-border bg-card p-4 lg:col-span-3">
                    <h2 class="text-base font-semibold">Recent Activity</h2>
                    <div class="mt-3 space-y-3">
                        <article
                            v-for="post in recentPosts"
                            :key="post.id"
                            class="rounded-lg border border-border p-3"
                        >
                            <p class="text-sm font-medium">{{ post.body || 'Photo post' }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ post.published ? 'Published' : 'Processing' }}
                            </p>
                        </article>
                        <p v-if="recentPosts.length === 0" class="text-sm text-muted-foreground">
                            No recent posts yet. Add your first photo to get started.
                        </p>
                    </div>
                </article>
            </section>
        </div>
    </AppLayout>
</template>
