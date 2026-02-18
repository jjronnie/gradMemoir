<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { AppPageProps, Post } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    recentPosts: Post[];
    managedCourse: {
        id: number;
        name: string;
        slug: string;
        shortcode: string;
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
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="mx-auto w-full max-w-5xl space-y-6 p-4">
            <section class="rounded-2xl border border-border bg-card p-5">
                <p class="text-sm text-muted-foreground">
                    {{ greeting }},
                </p>
                <h1 class="mt-1 text-3xl font-semibold">
                    {{ user?.name }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    @{{ user?.username }} · {{ user?.email }}
                </p>
                <p class="mt-2 text-xs uppercase tracking-widest text-muted-foreground">
                    {{ user?.roles?.[0] }}
                </p>
            </section>

            <section class="rounded-xl border border-border bg-card p-4">
                <h2 class="text-sm font-semibold">You are ready</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Complete your full profile details and start adding your 12
                    class memories.
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <Link
                        href="/settings/profile"
                        class="rounded-md border border-border px-3 py-1.5 text-xs hover:bg-accent"
                    >
                        Complete Profile
                    </Link>
                    <Link
                        href="/posts/create"
                        class="rounded-md border border-border px-3 py-1.5 text-xs hover:bg-accent"
                    >
                        Add Memories
                    </Link>
                </div>
            </section>

            <section class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                <Link
                    :href="
                        user?.course_slug ? `/courses/${user.course_slug}` : '/dashboard'
                    "
                    class="rounded-xl border border-border bg-card p-4 hover:border-primary"
                >
                    My Course Archive
                </Link>
                <Link
                    :href="user?.profile_url ?? '#'"
                    class="rounded-xl border border-border bg-card p-4 hover:border-primary"
                >
                    My Profile
                </Link>
                <Link
                    href="/settings/profile"
                    class="rounded-xl border border-border bg-card p-4 hover:border-primary"
                >
                    Settings
                </Link>
                <Link
                    href="/posts/create"
                    class="rounded-xl border border-border bg-card p-4 hover:border-primary"
                    v-if="(user?.photo_slots_remaining as number | undefined) ?? 0 > 0"
                >
                    Add Photo
                </Link>
            </section>

            <section class="rounded-xl border border-border bg-card p-4">
                <h2 class="text-sm font-semibold">Photo Capacity</h2>
                <p class="mt-1 text-xs text-muted-foreground">
                    {{ photoUsage.used }} / {{ photoUsage.limit }} used
                </p>
                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-muted">
                    <div
                        class="h-full bg-primary transition-all"
                        :style="{
                            width: `${Math.min((photoUsage.used / photoUsage.limit) * 100, 100)}%`,
                        }"
                    />
                </div>
                <p class="mt-2 text-xs text-muted-foreground">
                    {{ photoUsage.remaining }} photo slots left
                </p>
            </section>

            <section
                v-if="myUniversity"
                class="rounded-xl border border-border bg-card p-4 text-sm"
            >
                University:
                <Link
                    class="underline underline-offset-4"
                    :href="`/universities/${myUniversity.slug}`"
                >
                    {{ myUniversity.name }}
                </Link>
            </section>

            <section class="rounded-xl border border-border bg-card p-4">
                <h2 class="text-lg font-semibold">Recent Activity</h2>
                <div class="mt-3 space-y-3">
                    <article
                        v-for="post in recentPosts"
                        :key="post.id"
                        class="rounded-lg border border-border p-3"
                    >
                        <p class="text-sm">
                            {{ post.body || 'Photo post' }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ post.published ? 'Published' : 'Processing' }}
                        </p>
                    </article>
                    <p
                        v-if="recentPosts.length === 0"
                        class="text-sm text-muted-foreground"
                    >
                        No recent posts yet.
                    </p>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
