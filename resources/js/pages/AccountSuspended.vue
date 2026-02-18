<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Account Suspended" />

    <PublicLayout>
        <section class="mx-auto max-w-xl py-20 text-center">
            <h1 class="text-3xl font-semibold">Account suspended</h1>
            <p class="mt-3 text-sm text-muted-foreground">
                Your account is currently unavailable. Contact support for help.
            </p>
            <Link
                href="/"
                class="mt-6 inline-block rounded-lg bg-primary px-5 py-2.5 text-sm text-primary-foreground"
            >
                Back to Home
            </Link>
        </section>
    </PublicLayout>
</template>
