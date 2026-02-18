<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Terms & Conditions" />

    <PublicLayout>
        <article class="mx-auto max-w-3xl py-10">
            <h1 class="text-3xl font-semibold">Terms & Conditions</h1>
            <div class="mt-6 space-y-5 text-sm text-muted-foreground">
                <section class="rounded-xl border border-border bg-card p-4">
                    <h2 class="text-base font-semibold text-foreground">
                        1. Acceptance
                    </h2>
                    <p class="mt-2">
                        By using this platform, you agree to these terms and all
                        applicable policies.
                    </p>
                </section>
                <section class="rounded-xl border border-border bg-card p-4">
                    <h2 class="text-base font-semibold text-foreground">
                        2. User Content
                    </h2>
                    <p class="mt-2">
                        You are responsible for the content you publish and must
                        own or have rights to all uploaded media.
                    </p>
                </section>
                <section class="rounded-xl border border-border bg-card p-4">
                    <h2 class="text-base font-semibold text-foreground">
                        3. Privacy
                    </h2>
                    <p class="mt-2">
                        Profile visibility settings and public fields are managed
                        in your account settings.
                    </p>
                </section>
                <section class="rounded-xl border border-border bg-card p-4">
                    <h2 class="text-base font-semibold text-foreground">
                        4. Moderation
                    </h2>
                    <p class="mt-2">
                        Accounts that violate policies may be suspended or
                        banned.
                    </p>
                </section>
            </div>
        </article>
    </PublicLayout>
</template>
