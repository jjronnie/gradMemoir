<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import BottomNav from '@/components/BottomNav.vue';
import { SidebarProvider } from '@/components/ui/sidebar';
import { useProgress } from '@/composables/useProgress';
import type { AppShellVariant } from '@/types';

type Props = {
    variant?: AppShellVariant;
};

defineProps<Props>();

const isOpen = usePage().props.sidebarOpen;
const progress = useProgress();
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col pb-24 lg:pb-0">
        <div
            v-if="progress.isActive"
            class="fixed top-0 left-0 z-50 h-1 bg-primary transition-all duration-200"
            :style="{ width: `${progress.percent}%` }"
        />
        <slot />
        <BottomNav />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <div class="flex min-h-screen w-full pb-24 lg:pb-0">
            <div
                v-if="progress.isActive"
                class="fixed top-0 left-0 z-50 h-1 bg-primary transition-all duration-200"
                :style="{ width: `${progress.percent}%` }"
            />
            <slot />
            <BottomNav />
        </div>
    </SidebarProvider>
</template>
