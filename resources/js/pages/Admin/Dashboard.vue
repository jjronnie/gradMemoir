<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    stats: {
        universities: number;
        courses: number;
        users: number;
        posts: number;
        photos: number;
        featuredProfiles: number;
        applicationsPending: number;
        applicationsReviewed: number;
        flagsPending: number;
        flagsReviewed: number;
        statusBreakdown: {
            active: number;
            banned: number;
            suspended: number;
        };
        postBreakdown: {
            published: number;
            processing: number;
        };
    };
    recentUsers: Array<{
        id: number;
        name: string;
        email: string;
        username: string;
        role: string;
        status: string;
        created_at: string;
        university?: {
            name: string;
        } | null;
        course?: {
            name: string;
        } | null;
    }>;
}>();
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout>
        <div class="space-y-6 p-4">
            <h1 class="text-2xl font-semibold">Superadmin Dashboard</h1>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-border bg-card p-4">
                    <p class="text-xs text-muted-foreground">Universities</p>
                    <p class="text-3xl font-semibold">{{ stats.universities }}</p>
                </div>
                <div class="rounded-xl border border-border bg-card p-4">
                    <p class="text-xs text-muted-foreground">Courses</p>
                    <p class="text-3xl font-semibold">{{ stats.courses }}</p>
                </div>
                <div class="rounded-xl border border-border bg-card p-4">
                    <p class="text-xs text-muted-foreground">Users</p>
                    <p class="text-3xl font-semibold">{{ stats.users }}</p>
                    <p class="mt-2 text-xs text-muted-foreground">
                        Active {{ stats.statusBreakdown.active }} · Banned
                        {{ stats.statusBreakdown.banned }} · Suspended
                        {{ stats.statusBreakdown.suspended }}
                    </p>
                </div>
                <div class="rounded-xl border border-border bg-card p-4">
                    <p class="text-xs text-muted-foreground">Posts</p>
                    <p class="text-3xl font-semibold">{{ stats.posts }}</p>
                    <p class="mt-2 text-xs text-muted-foreground">
                        Published {{ stats.postBreakdown.published }} · Processing
                        {{ stats.postBreakdown.processing }}
                    </p>
                </div>
                <div class="rounded-xl border border-border bg-card p-4">
                    <p class="text-xs text-muted-foreground">Photos</p>
                    <p class="text-3xl font-semibold">{{ stats.photos }}</p>
                </div>
                <div class="rounded-xl border border-border bg-card p-4">
                    <p class="text-xs text-muted-foreground">Featured Profiles</p>
                    <p class="text-3xl font-semibold">{{ stats.featuredProfiles }}</p>
                </div>
                <div class="rounded-xl border border-border bg-card p-4">
                    <p class="text-xs text-muted-foreground">Applications</p>
                    <p class="text-3xl font-semibold">{{ stats.applicationsPending }}</p>
                    <p class="mt-2 text-xs text-muted-foreground">
                        Reviewed {{ stats.applicationsReviewed }}
                    </p>
                </div>
                <div class="rounded-xl border border-border bg-card p-4">
                    <p class="text-xs text-muted-foreground">Flags</p>
                    <p class="text-3xl font-semibold">{{ stats.flagsPending }}</p>
                    <p class="mt-2 text-xs text-muted-foreground">
                        Reviewed {{ stats.flagsReviewed }}
                    </p>
                </div>
            </div>

            <section class="rounded-xl border border-border bg-card p-4">
                <h2 class="text-lg font-semibold">Recent Users</h2>
                <table class="mt-3 w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left">
                            <th class="px-2 py-2">Name</th>
                            <th class="px-2 py-2">Email</th>
                            <th class="px-2 py-2">Username</th>
                            <th class="px-2 py-2">Role</th>
                            <th class="px-2 py-2">Status</th>
                            <th class="px-2 py-2">Course</th>
                            <th class="px-2 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in recentUsers"
                            :key="user.id"
                            class="border-b border-border/50"
                        >
                            <td class="px-2 py-2">{{ user.name }}</td>
                            <td class="px-2 py-2">{{ user.email }}</td>
                            <td class="px-2 py-2">@{{ user.username }}</td>
                            <td class="px-2 py-2">{{ user.role }}</td>
                            <td class="px-2 py-2">{{ user.status }}</td>
                            <td class="px-2 py-2">
                                {{ user.course?.name ?? 'Not assigned' }}
                            </td>
                            <td class="px-2 py-2">
                                <a
                                    :href="`/@${user.username}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-xs underline underline-offset-4"
                                >
                                    View Public
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </AppLayout>
</template>
