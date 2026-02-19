<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const page = usePage();
const appName = computed(
    () =>
        (page.props.appName as string | undefined) ??
        (page.props.name as string | undefined) ??
        'App',
);
</script>

<template>
    <div
        class="flex aspect-square size-10 items-center justify-center rounded-md border border-border bg-white text-black"
    >
        <AppLogoIcon class="size-7" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold"
            >{{ appName }}</span
        >
    </div>
</template>
