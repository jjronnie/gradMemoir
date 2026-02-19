<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

const props = defineProps<{
    status: number;
}>();

const title = computed(() => {
    return {
        503: 'Service Unavailable',
        500: 'Server Error',
        404: 'Page Not Found',
        403: 'Forbidden',
    }[props.status] ?? 'Error';
});

const description = computed(() => {
    return {
        503: 'The service is temporarily unavailable. Please try again shortly.',
        500: 'Something went wrong on our side. Please try again.',
        404: 'The page you are looking for could not be found.',
        403: 'You are not authorized to access this page.',
    }[props.status] ?? 'An unexpected error occurred.';
});
</script>

<template>
    <Head :title="title" />

    <PublicLayout>
        <section class="mx-auto flex min-h-[55vh] w-full max-w-3xl flex-col items-center justify-center px-4 text-center">
            <p class="text-xs tracking-[0.22em] text-muted-foreground uppercase">
                Error {{ status }}
            </p>
            <h1 class="mt-3 text-3xl font-semibold sm:text-4xl">
                {{ title }}
            </h1>
            <p class="mt-3 max-w-xl text-sm text-muted-foreground sm:text-base">
                {{ description }}
            </p>
        </section>
    </PublicLayout>
</template>
