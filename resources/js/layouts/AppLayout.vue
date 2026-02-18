<script setup lang="ts">
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const isSuperadmin = computed(() =>
    Boolean(page.props.auth.user?.roles?.includes('superadmin')),
);
</script>

<template>
    <AppSidebarLayout v-if="isSuperadmin" :breadcrumbs="breadcrumbs">
        <slot />
    </AppSidebarLayout>
    <AppHeaderLayout v-else :breadcrumbs="breadcrumbs">
        <slot />
    </AppHeaderLayout>
</template>
