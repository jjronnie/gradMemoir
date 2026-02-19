<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
</script>

<template>
    <Head title="How It Works" />

    <PublicLayout>
        <article class="mx-auto max-w-3xl py-10">
            <h1 class="text-3xl font-semibold">How It Works</h1>
            <ol class="mt-6 space-y-5 text-sm">
                <li class="rounded-xl border border-border bg-card p-4">
                    <h2 class="font-semibold">1. Universities are added</h2>
                    <p class="mt-2 text-muted-foreground">
                        Platform admins add universities and courses to build
                        official class archives.
                    </p>
                </li>
                <li class="rounded-xl border border-border bg-card p-4">
                    <h2 class="font-semibold">2. Students join their course</h2>
                    <p class="mt-2 text-muted-foreground">
                        Students create profiles, choose their university and
                        class year, then complete onboarding.
                    </p>
                </li>
                <li class="rounded-xl border border-border bg-card p-4">
                    <h2 class="font-semibold">3. Archives stay searchable</h2>
                    <p class="mt-2 text-muted-foreground">
                        Profiles and memories remain discoverable as a permanent
                        digital yearbook.
                    </p>
                </li>
                <li class="rounded-xl border border-border bg-card p-4">
                    <h2 class="font-semibold">4. Share your legacy</h2>
                    <p class="mt-2 text-muted-foreground">
                        Share course archives and personal profiles with your
                        classmates and friends.
                    </p>
                </li>
            </ol>
        </article>
    </PublicLayout>
</template>
