<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import FlashMessages from '@/components/FlashMessages.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="header" class="flex-col">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent>
            <FlashMessages class="px-4 pt-4" />
            <slot />
        </AppContent>
    </AppShell>
</template>
