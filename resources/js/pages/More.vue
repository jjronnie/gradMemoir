<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/composables/useAppearance';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { AppPageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage<AppPageProps>();
const { resolvedAppearance, updateAppearance } = useAppearance();

const toggleTheme = (): void => {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
};
</script>

<template>
    <Head title="More" />

    <PublicLayout>
        <section class="py-10">
            <h1 class="text-3xl font-semibold">More</h1>
            <div class="mt-6 space-y-3">
                <template v-if="page.props.auth.user">
                    <Link class="block rounded-lg border border-border p-3" :href="`/@${page.props.auth.user.username}`">My Profile</Link>
                    <Link
                        v-if="page.props.auth.user.roles?.includes('admin')"
                        class="block rounded-lg border border-border p-3"
                        href="/course-admin"
                    >
                        Manage Course
                    </Link>
                    <Link class="block rounded-lg border border-border p-3" href="/settings/profile">Settings</Link>
                </template>

                <Link class="block rounded-lg border border-border p-3" href="/how-it-works">How It Works</Link>
                <Link class="block rounded-lg border border-border p-3" href="/terms">Terms & Conditions</Link>
                <Link class="block rounded-lg border border-border p-3" href="/apply">Apply — Add My University/Course</Link>
                <a
                    href="https://techtowerinc.com"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="block rounded-lg border border-border p-3"
                    >Developer</a
                >

                <Button variant="outline" class="w-full" @click="toggleTheme">
                    Toggle {{ resolvedAppearance === 'dark' ? 'Light' : 'Dark' }} Mode
                </Button>

                <template v-if="page.props.auth.user">
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="w-full rounded-lg border border-border bg-card p-3 text-left"
                    >
                        Logout
                    </Link>
                </template>
                <template v-else>
                    <Link class="block rounded-lg border border-border p-3" href="/login">Login</Link>
                    <Link class="block rounded-lg border border-border p-3" href="/register">Register</Link>
                </template>
            </div>
        </section>
    </PublicLayout>
</template>
